<?php

namespace Tymy\Module\Autotest;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Debt\Model\Debt;
use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Poll\Model\Poll;
use Tymy\Module\User\Model\User;
use Tymy\Module\Autotest\Entity\Assert;

/**
 * Description of RecordManager
 *
 * @author kminekmatej, 1.5.2019
 */
class RecordManager
{
    /** @var RequestCase */
    private $requestCase;
    private $config;

    public function __construct(RequestCase $requestCase, $config)
    {
        $this->requestCase = $requestCase;
        $this->config = $config;
    }

    private function createRecord(string $url, $data = null, $changes = null, string $identifier = "ID", ?array $checkSkips = null): int
    {
        $this->applyChanges($data, $changes);

        $response = $this->requestCase->request($url, "POST", $data)->expect(201, "array");

        $this->requestCase->assertObjectEquality($data, $response->getData(), $checkSkips);

        return $response->getData()[$identifier];
    }

    /** @return int $recordId */
    public function createUser($data = null, $changes = null): int
    {
        $postData = $data ?: $this->mockUser();
        $this->applyChanges($postData, $changes);

        $response = $this->requestCase->request(User::MODULE, "POST", $postData)->expect(201, "array");

        $this->requestCase->assertObjectEquality($postData, $response->getData(), ["login", "password"]);

        Assert::equal(false, array_key_exists("password", $response->getData())); //check password is not returned
        Assert::equal(strtoupper($postData["login"]), $response->getData()["login"]); //check login has been saved in uppercase

        return $response->getData()["id"];
    }

    /** @return int $recordId */
    public function createDebt(?array $data = null, ?array $changes = null): int
    {
        return $this->createRecord(Debt::MODULE, $data ?: $this->mockDebt(), $changes, "id");
    }

    /** @return int $recordId */
    public function createDiscussion(?array $data = null, ?array $changes = null): int
    {
        return $this->createRecord(Discussion::MODULE, $data ?: $this->mockDiscussion(), $changes, "id");
    }

    /** @return int $recordId */
    public function createEvent(?array $data = null, ?array $changes = null): int
    {
        return $this->createRecord(Event::MODULE, $data ?: $this->mockEvent(), $changes, "id");
    }

    /** @return int $recordId */
    public function createOptions(int $pollId, int $numberOfOptions, ?array $data = null, ?array $changes = null): void
    {
        if (empty($data)) {
            $data = [];
            for ($index = 0; $index < $numberOfOptions; $index++) {
                $optionData = $this->mockOption($pollId);
                $this->applyChanges($optionData, $changes);
                $data[] = $optionData;
            }
        }

        $this->requestCase->request(Poll::MODULE . "/$pollId/options", "POST", $data)->expect(201, "array");
        //here is record created directly, without checking the result, since we are creating array of objects and we cannot check that against returned array of objects (output contains ID, but input does not)
    }

    /** @return int $recordId */
    public function createPermission(?array $data = null, ?array $changes = null): int
    {
        return $this->createRecord(Permission::MODULE, $data ?: $this->mockPermission(), $changes, "id", ["allowedRoles", "allowedStatuses", "allowedUsers"]);//we are sending revocations, so there will not be any allowances
    }

    /** @return int $recordId */
    public function createPoll(?array $data = null, ?array $changes = null): int
    {
        return $this->createRecord(Poll::MODULE, $data ?: $this->mockPoll(), $changes, "id");
    }

    /** @return int $recordId */
    public function createStatus(int $statusSetId, ?array $data = null, ?array $changes = null): int
    {
        return $this->createRecord("attendanceStatus", $data ?: $this->mockStatus($statusSetId), $changes, "id", ["image"]);
    }

    /** @return int $recordId */
    public function createStatusSet(?array $data = null, ?array $changes = null): int
    {
        return $this->createRecord("attendanceStatusSet", $data ?: $this->mockStatusSet(), $changes, "id");
    }

    public function mockAttendance(int $eventId, $pre = true, $post = false)
    {
        $data = [
            "eventId" => $eventId
        ];

        if ($pre) {
            $data["preStatus"] = "YES";
            $data["preDescription"] = "Autotest will come";
        }

        if ($post) {
            $data["postStatus"] = "LAT";
            $data["postDescription"] = "Autotest came later";
        }

        return $data;
    }

    public function mockDebt()
    {
        return [
            "amount" => (float)rand(10, 1000),
            "currencyIso" => "CZK",
            "countryIso" => "cs",
            "debtorId" => $this->config["user_test_id"],
            "payeeId" => $this->config["user_admin_id"],
            "payeeAccountNumber" => "209378338/0300",
            "varcode" => "123456",
            "debtDate" => $this->toJsonDate(new DateTime("- 1 month")),
            "caption" => "Odvoz z Chrasti",
            "note" => "S prirazkou za poblity auto",
        ];
    }

    public function mockDiscussion()
    {
        return [
            "caption" => "Autotest diskuze " . rand(0, 1000),
            "description" => "Blablabla " . rand(0, 1000),
            "readRightName" => "",
            "writeRightName" => "",
            "deleteRightName" => "ADMINONLY",
            "stickyRightName" => "ADMINONLY",
            "publicRead" => false,
            "editablePosts" => true,
        ];
    }

    public function mockEvent()
    {
        return [
            "caption" => "Autotest event " . rand(0, 1000),
            "type" => "TRA",
            "description" => "Testovací trénink " . rand(0, 1000),
            "closeTime" => $this->toJsonDate(new DateTime("- 2 hours")),
            "startTime" => $this->toJsonDate(new DateTime("+ 2 hours")),
            "endTime" => $this->toJsonDate(new DateTime("+ 3 hours")),
            "link" => "https://mapy.cz/s/jamolefone",
            "place" => "TJ Meteor Palmovka, U Meteoru 29, 180 00 Praha 8, tram: Libeňský zámek, metro Palmovka 10 min pěšky",
            "resultRightName" => "ADMINONLY",
        ];
    }

    public function mockOption(int $pollId = null)
    {
        $type = ["NUMBER","TEXT","BOOLEAN"][rand(0, 2)];

        return [
            "caption" => "Poll $type " . rand(0, 1000),
            "type" => $type,
            "pollId" => $pollId,
        ];
    }

    public function mockPermission()
    {
        return [
            "type" => "USR",
            "caption" => "Autotest event " . rand(0, 1000),
            "name" => "AUTOPERM " . rand(0, 1000),
            "allowedRoles" => ["SUPER", "ATT"],
            "revokedRoles" => ["USR"],
            "allowedStatuses" => ["PLAYER"],
            "revokedStatuses" => ["MEMBER"],
            "allowedUsers" => [1],
            "revokedUsers" => [2],
        ];
    }

    public function mockPoll()
    {
        return [
            "caption" => "Autotest poll " . rand(0, 1000),
            "changeableVotes" => true,
            "anonymousResults" => false,
            "showResults" => "ALWAYS",
            "status" => "DESIGN",
        ];
    }

    public function mockUser()
    {
        $rand = rand(0, 30000);
        return [
            "login" => "MAL_GANIS_" . $rand,
            "password" => md5($rand),
            "email" => "mal-ganis-$rand@autotest.tymy.cz",
            "canLogin" => true,
            "canEditCallName" => true,
            "status" => "PLAYER",
            "firstName" => "Josef",
            "lastName" => "Svěcený",
            "callName" => "Jožo",
            "language" => "CZ",
            "jerseyNumber" => (string)rand(0, 100),
            "gender" => ["MALE", "FEMALE"][rand(0, 1)],
            "street" => "K Marastu 315",
            "city" => "Nový Krobuzon",
            "zipCode" => "91544",
            "birthDate" => "1985-04-22",
            "nameDayMonth" => 6,
            "nameDayDay" => 13,
            "accountNumber" => "123465789/0300",
        ];
    }

    public function mockStatus(int $statusSetId)
    {
        $rand = rand(0, 30000);
        return [
            "code" => strtoupper(substr(md5($rand), 0, 3)),//random code
            "caption" => "Why do this? ($rand)",
            "image" => $this->config["test_250_img"],
            "statusSetId" => $statusSetId,
        ];
    }

    public function mockStatusSet()
    {
        $rand = rand(0, 30000);
        return [
            "name" => "Autotest status set $rand"
        ];
    }

    public function deleteUser($id)
    {
        $this->deleteRecord(User::MODULE, $id);
    }

    public function deleteRecord($basePath, $id)
    {
        $url = "$basePath/$id";

        $responseDelete = $this->requestCase->request($url, 'DELETE')->expect(200);
        Assert::true(array_key_exists("id", $responseDelete->getData()));
        Assert::equal($responseDelete->getData()["id"], $id);
    }

    private function toJsonDate(DateTime $date = null)
    {
        return $date !== null ? $date->format(BaseModel::DATE_FORMAT) : null;
    }

    private function applyChanges(&$data, $changes)
    {
        if ($changes && is_array($changes)) {
            foreach ($changes as $key => $value) {
                $data[$key] = $value;
            }
        }
    }
}
