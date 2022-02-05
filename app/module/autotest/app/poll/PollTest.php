<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Permission;

use Tymy\Bootstrap;
use Tymy\Module\Poll\Model\Poll;
use Tymy\Module\Autotest\Entity\Assert;
use Tymy\Module\Autotest\RequestCase;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of PollTest
 *
 * @author kminekmatej, 28.12.2020 22:41:07
 *
 */
class PollTest extends RequestCase
{
    public function getModule(): string
    {
        return Poll::MODULE;
    }

    public function testGetSingular()
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

    public function testGetPlural()
    {
        $this->authorizeAdmin();
        $listResponse = $this->request($this->getBasePath() . "s")->expect(200, "array");
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

        $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");

        $changes = $this->mockChanges();
        $changes["description"] = "Changed description";
        $changes["resultRightName"] = "ADMINONLY";
        $changes["voteRightName"] = "ADMINONLY";
        $changes["alienVoteRightName"] = "ADMINONLY";

        $this->change($recordId, $changes);

        $this->createOptionsFor($recordId, 5);

        $this->request($this->getBasePath() . "/" . $recordId . "/options", "POST", $this->recordManager->mockOption($recordId))->expect(201, "array");//can create just one option

        //now change some options
        $options = $this->request($this->getBasePath() . "/" . $recordId . "/options")->expect(200, "array")->getData();
        $optionData = $options[0];
        $optionData["caption"] = "Poll changed option to BOOLEAN";
        $optionData["type"] = "BOOLEAN";
        $changedData = $this->request($this->getBasePath() . "/" . $recordId . "/options", "PUT", $optionData)->expect(200, "array")->getData();

        Assert::equal($optionData["caption"], $changedData["caption"]);
        Assert::equal($optionData["type"], $changedData["type"]);

        $deleteOption = $this->request($this->getBasePath() . "/" . $recordId . "/options", "DELETE", $changedData)->expect(200, "array");

        $this->deleteRecord($recordId);
    }

    public function testCrudForbidden()
    {
        $this->authorizeAdmin();
        $pollId = $this->createRecord();
        $this->createOptionsFor($pollId, 1);
        $optionsData = $this->request($this->getBasePath() . "/$pollId/options")->expect(200, "array")->getData();//poll doesnt exist
        $option = $optionsData[0];

        $this->authorizeUser();
        $this->request($this->getBasePath(), "POST", $this->recordManager->mockPoll())->expect(403);//user cannot create
        $this->request($this->getBasePath() . "/$pollId", "PUT", $this->mockChanges())->expect(403);//user cannot change

        $this->request($this->getBasePath() . "/$pollId/options", "POST", [$this->recordManager->mockOption($pollId)])->expect(403);//user cannot create options
        $this->request($this->getBasePath() . "/$pollId/options", "POST", $this->recordManager->mockOption($pollId))->expect(403);//user cannot create just one option

        $option["caption"] = "Changed caption by user";
        $this->request($this->getBasePath() . "/$pollId/options", "PUT", $option)->expect(403);//user cannot change option
        $this->request($this->getBasePath() . "/$pollId", "DELETE")->expect(403);//user cannot change

        $this->authorizeAdmin();
        $this->request($this->getBasePath() . "/9999999")->expect(404);//poll doesnt exist
        $this->request($this->getBasePath() . "/9999999/options")->expect(404);//poll doesnt exist
        $this->request($this->getBasePath() . "/9999999/options", "HEAD")->expect(405);//method not supported
        $this->request($this->getBasePath() . "/9999999", "DELETE")->expect(404);//poll doesnt exist

        $this->request($this->getBasePath() . "/$pollId", "DELETE")->expect(200);//delete created poll
    }

    public function testUserVisibility()
    {
        $this->authorizeUser();
        $this->request($this->getBasePath())->expect(200);//get list of polls
    }

    public function testMenu()
    {
        $polls = $this->request($this->getBasePath() . "/menu")->expect(200, "array")->getData();//poll doesnt exist
        foreach ($polls as $poll) {
            Assert::true($poll["mainMenu"]);
        }

        $this->request($this->getBasePath() . "/menu", "POST")->expect(405);
        $this->request($this->getBasePath() . "/menu", "PUT")->expect(405);
        $this->request($this->getBasePath() . "/menu", "DELETE")->expect(405);
    }

    public function testBlank()
    {
        $this->authorizeAdmin();
        $createdData = $this->request($this->getBasePath(), "POST")->expect(201, "array")->getData();

        Assert::equal("New poll", $createdData["caption"]);
        Assert::equal(-1, $createdData["minItems"]);
        Assert::equal(-1, $createdData["maxItems"]);
        Assert::equal(false, $createdData["mainMenu"]);
        Assert::equal(false, $createdData["anonymousResults"]);
        Assert::equal(true, $createdData["changeableVotes"]);
        Assert::equal("NEVER", $createdData["showResults"]);
        Assert::equal("DESIGN", $createdData["status"]);
        Assert::equal(0, $createdData["orderFlag"]);

        $this->deleteRecord($createdData["id"]);
    }

    public function test($param)
    {
    }

    public function testVoting()
    {
        //create poll and add some votes into it, the get the poll again and check the votes exists
    }

    public function createRecord()
    {
        return $this->recordManager->createPoll();
    }

    public function createOptionsFor(int $pollId, int $numberOfOptions): void
    {
        $this->recordManager->createOptions($pollId, $numberOfOptions);
    }

    public function mockRecord()
    {
        return $this->recordManager->mockPoll();
    }

    public function mockOptionFor(int $pollId)
    {
        return $this->recordManager->mockOption($pollId);
    }


    protected function mockChanges(): array
    {
        return [
            "caption" => "Autotest changed poll " . rand(0, 1000),
            "mainMenu" => false,
            "status" => "OPENED",
        ];
    }
}

(new PollTest($container))->run();
