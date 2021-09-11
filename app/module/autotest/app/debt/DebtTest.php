<?php

namespace Tymy\Module\Autotest\Debt;

use Tymy\Bootstrap;
use Nette\Utils\DateTime;
use Tymy\Module\Debt\Model\Debt;
use Tymy\Module\Autotest\Entity\Assert;
use Tymy\Module\Autotest\RequestCase;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of DebtTest
 *
 * @author kminekmatej, 07.10.2020 21:47:07
 *
 */
class DebtTest extends RequestCase
{
    public function getBasePath()
    {
        return "/" . basename(__DIR__);
    }

    public function getModule(): string
    {
        return Debt::MODULE;
    }

    public function testGet()
    {
        $this->authorizeAdmin();
        $listResponse = $this->request($this->getBasePath())->expect(200, "array");
        if (count($listResponse->getData()) == 0) {
            return;
        }
        $data = $listResponse->getData();
        shuffle($data);
        $iterations = min(5, count($data));
        if ($iterations == 0) {
            return;
        }
        for ($index = 0; $index < $iterations; $index++) {
            $d = array_shift($data);
            $idRecord = $d["id"];
            $this->request($this->getBasePath() . "/$idRecord")->expect(200, "array");
        }
    }

    public function testCRUDSingular()
    {
        $this->authorizeAdmin();
        $recordId = $this->createRecord();

        $data = $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");

        $this->change($recordId);

        $origData = $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");

        //test any other user doesnt see it
        $this->authorizeAdmin($this->config["user_mainadmin_login"], $this->config["user_mainadmin_pwd"]);
        $this->request($this->getBasePath() . "/" . $recordId)->expect(403);

        //mainadmin cannot edit his debt, nor can set send date, nor delete
        $this->request($this->getBasePath() . "/" . $recordId, "PUT", $this->mockChanges())->expect(403);
        $this->request($this->getBasePath() . "/" . $recordId, "PUT", ["paymentSent" => new DateTime()])->expect(403);
        $this->request($this->getBasePath() . "/" . $recordId, "DELETE")->expect(403);

        //user, which is the actual debtor, can set only sent date, nothing else
        $this->authorizeUser();
        $chResponse = $this->request($this->getBasePath() . "/" . $recordId, "PUT", $this->mockChanges())->expect(200, "array");//debtor can edit, but the only field that gets edited is paymentSent
        Assert::equal($origData->getData()["amount"], $chResponse->getData()["amount"]);//amount didnt change
        $this->request($this->getBasePath() . "/" . $recordId, "PUT", ["paymentSent" => new DateTime()])->expect(200, "array");
        $debtData = $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");
        Assert::equal($debtData->getData()["paymentSent"], $this->toJsonDate(new DateTime()));//amount didnt change
        $this->request($this->getBasePath() . "/" . $recordId, "DELETE")->expect(403);

        //back to admin, which can mark the debt as paymentReceived and then delete it
        $this->authorizeAdmin();
        $this->request($this->getBasePath() . "/" . $recordId, "PUT", ["paymentReceived" => new DateTime()])->expect(200, "array");
        $this->request($this->getBasePath() . "/" . $recordId, "DELETE")->expect(200);
    }

    public function testTeamDebts()
    {
        //admin can create team debt
        $this->authorizeAdmin();
        $recordId = $this->recordManager->createDebt(null, ["payeeId" => null, "caption" => "Poplatky 2020", "note" => null]);   //create debt for team
        $origData = $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");

        //another admin of team debts can see that debt
        $this->authorizeAdmin($this->config["user_mainadmin_login"], $this->config["user_mainadmin_pwd"]);
        $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");
        $list = $this->request($this->getBasePath())->expect(200, "array")->getData();

        $found = false;
        foreach ($list as $debt) {
            if ($debt["id"] == $recordId) {
                Assert::equal($debt["id"], $recordId);
                $found = true;
                break;
            }
            $found = false;
        }
        if (!$found) {
            Assert::equal($debt["id"], $recordId, "Debt id $recordId not found in list");
        }


        //user for which that debt is created can see it and set paymentSent, cannot delete or change anything else
        $this->authorizeUser();
        $list = $this->request($this->getBasePath())->expect(200, "array")->getData();

        $found = false;
        foreach ($list as $debt) {
            if ($debt["id"] == $recordId) {
                Assert::equal($debt["id"], $recordId);
                $found = true;
                break;
            }
            $found = false;
        }
        if (!$found) {
            Assert::equal($debt["id"], $recordId, "Debt id $recordId not found in users list");
        }

        $chResponse = $this->request($this->getBasePath() . "/" . $recordId, "PUT", $this->mockChanges())->expect(200, "array");//debtor can edit, but the only field that gets edited is paymentSent
        Assert::equal($origData->getData()["amount"], $chResponse->getData()["amount"]);//amount didnt change
        $now = new DateTime();
        $this->request($this->getBasePath() . "/" . $recordId, "PUT", ["paymentSent" => $now])->expect(200, "array");
        sleep(1);//sleep for one second, to make sure that current datetime is now different than $now variable. So we can check that the paymentSent would be actually changed if something changes it
        $debtData = $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");
        Assert::equal($debtData->getData()["paymentSent"], $this->toJsonDate($now));//amount didnt change
        $this->request($this->getBasePath() . "/" . $recordId, "DELETE")->expect(403);

        //another admin can mark it as paymentReceived
        $this->authorizeAdmin($this->config["user_mainadmin_login"], $this->config["user_mainadmin_pwd"]);
        $this->request($this->getBasePath() . "/" . $recordId, "PUT", ["paymentReceived" => new DateTime()])->expect(200, "array");

        //admin can delete that debt
        $this->authorizeAdmin();
        $this->request($this->getBasePath() . "/" . $recordId, "DELETE")->expect(200);
    }

    public function testBlankCaption()
    {
        $data = $this->mockRecord();
        $data["caption"] = "";
        $this->request($this->getBasePath(), "POST", $data)->expect(400);

        $recordId = $this->createRecord();
        $this->request($this->getBasePath() . "/" . $recordId, "PUT", ["caption" => ""])->expect(400);
    }

    public function testDifferentUsersDebt()
    {
        $this->authorizeUser();
        $data = $this->mockRecord();
        $data["payeeId"] = $this->config["user_admin_id"];
        $this->request($this->getBasePath(), "POST", $data)->expect(403);
    }

    public function testDebtNegative()
    {
        $this->authorizeAdmin();
        $data = $this->mockRecord();
        $data["amount"] = -13;
        $this->request($this->getBasePath(), "POST", $data)->expect(400);

        $recordId = $this->createRecord();
        $this->request($this->getBasePath() . "/" . $recordId, "PUT", ["amount" => "-14"])->expect(400);
    }

    public function testTeamOwesMe()
    {
        $this->authorizeUser();

        $recordId = $this->recordManager->createDebt(null, ["payeeId" => $this->config["user_test_id"], "debtorId" => null, "caption" => "Tým mi dluží přeplatek za finále MČR, id: " . $this->user->getId()]);

        //admin can mark it as paymentSent
        $this->authorizeAdmin($this->config["user_mainadmin_login"], $this->config["user_mainadmin_pwd"]);
        $this->request($this->getBasePath() . "/" . $recordId, "PUT", ["paymentSent" => new DateTime()])->expect(200, "array");
        //admin cannot mark it as payment received
        $this->request($this->getBasePath() . "/" . $recordId, "PUT", ["paymentReceived" => new DateTime()])->expect(200, "array");

        $this->authorizeUser();
        $this->deleteRecord($recordId);
    }

    public function testCRUDPlural()
    {
        $this->authorizeAdmin();
        $recordId = $this->createRecord();

        $data = $this->request($this->getBasePath() . "s/" . $recordId)->expect(200, "array");

        $this->change($recordId);

        $this->deleteRecord($recordId);
    }

    public function createRecord()
    {
        return $this->recordManager->createDebt();
    }

    public function mockRecord()
    {
        return $this->recordManager->mockDebt();
    }


    protected function mockChanges(): array
    {
        return [
            "amount" => (float)rand(1000, 10000),
            "payeeAccountNumber" => "214700539/0800",
            "varcode" => "654321",
            "debtDate" => $this->toJsonDate(new DateTime("- 14 days")),
            "caption" => "Odvoz do Chrasti",
            "note" => "Ne jako vzdycky, rupperte",
        ];
    }
}

(new DebtTest($container))->run();
