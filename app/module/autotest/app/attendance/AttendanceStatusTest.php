<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Event;

use Tymy\Bootstrap;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Autotest\Entity\Assert;
use Tymy\Module\Autotest\RequestCase;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of AttendanceStatusTest
 *
 * @RequestMapping(value = "/attendanceStatusSet", method = RequestMethod.POST)
 * @RequestMapping(value = "/attendanceStatusSet/{id}", method = RequestMethod.PUT)
 * @RequestMapping(value = "/attendanceStatusSet/{id}", method = RequestMethod.DELETE)
 */
class AttendanceStatusTest extends RequestCase
{
    protected function getBasePath(): string
    {
        return parent::getBasePath() . "Status";
    }

    public function getStatusSetPath(): string
    {
        return parent::getBasePath() . "StatusSet";
    }

    public function getModule(): string
    {
        return Attendance::MODULE;
    }

    public function testGet(): void
    {
        $this->authorizeAdmin();

        $list = $this->request($this->getBasePath())->expect(200, "array")->getData();

        foreach ($list as $record) {
            Assert::type("array", $record);
        }

        $last = array_pop($list);
        $this->request($this->getBasePath() . "/" . $last["id"])->expect(200, "array");
    }

    public function testCRUDStatus(): void
    {
        //createStatus under some status set
        $statusSetId = $this->getStatusSetId();

        $statusData = $this->recordManager->mockStatus($statusSetId);
        $statusResponseData = $this->request($this->getBasePath(), "POST", $statusData)->expect(201, "array")->getData();

        $this->assertObjectEquality($statusData, $statusResponseData, ["image"]);
        Assert::hasKey("updatedById", $statusResponseData);
        Assert::hasKey("updatedAt", $statusResponseData);
        Assert::type("int", $statusResponseData["updatedById"]);
        Assert::truthy($statusResponseData["updatedById"]);

        $statusId = $statusResponseData["id"];

        //now get the status set and check created status is there

        $this->checkStatusOfStatusSet($statusSetId, $statusResponseData);

        //now try to update this status using PUT request

        $rand = random_int(0, 1000);
        $changeMock = [
            "code" => strtoupper(substr(md5($rand), 0, 3)),
            "caption" => "Changed name $rand",
        ];
        $this->request($this->getBasePath() . "/$statusId", "PUT", $changeMock)->expect(200, "array");

        $this->checkStatusOfStatusSet($statusSetId, $changeMock);

        //now delete this status

        $this->request($this->getBasePath() . "/$statusId", "DELETE")->expect(200);
    }

    public function testStatusSetForbidden(): void
    {
        $this->authorizeUser();

        $statusSetData = $this->recordManager->mockStatusSet();
        $this->request($this->getStatusSetPath(), "POST", $statusSetData)->expect(403);
    }

    public function testStatusSetNoName(): void
    {
        $this->authorizeAdmin();

        $statusSetData = $this->recordManager->mockStatusSet();
        unset($statusSetData["name"]);
        $this->request($this->getStatusSetPath(), "POST", $statusSetData)->expect(400);
    }

    public function testCRUDStatusSet(): void
    {
        $this->authorizeAdmin();

        $statusSetData = $this->recordManager->mockStatusSet();
        $statusSetResponseData = $this->request($this->getStatusSetPath(), "POST", $statusSetData)->expect(201, "array")->getData();
        $this->assertObjectEquality($statusSetData, $statusSetResponseData);
        $statusSetId = $statusSetResponseData["id"];

        //now get the status set and check created status is there

        $statusGetResponseData = $this->request($this->getBasePath() . "/$statusSetId")->expect(200, "array")->getData();
        $this->assertObjectEquality($statusSetData, $statusGetResponseData);
        Assert::equal($statusSetId, $statusGetResponseData["id"]);
        Assert::hasKey("statuses", $statusGetResponseData);
        Assert::type("array", $statusGetResponseData["statuses"]);
        Assert::count(0, $statusGetResponseData["statuses"]);

        //now try to update this status using PUT request

        $rand = random_int(0, 1000);
        $changeMock = [
            "name" => "Changed SS name$rand",
        ];
        $this->request($this->getStatusSetPath() . "/$statusSetId", "PUT", $changeMock)->expect(200, "array");

        $statusGetResponseData = $this->request($this->getBasePath() . "/$statusSetId")->expect(200, "array")->getData();
        $this->assertObjectEquality($changeMock, $statusGetResponseData);

        //now delete this status
        $this->request($this->getStatusSetPath() . "/$statusSetId", "DELETE")->expect(200);
    }

    private function checkStatusOfStatusSet(int $statusSetId, array $mockedStatusData): void
    {
        $statusData = $this->request($this->getBasePath() . "/$statusSetId")->expect(200, "array")->getData();
        Assert::hasKey("id", $statusData);
        Assert::hasKey("name", $statusData);
        Assert::hasKey("statuses", $statusData);
        Assert::equal($statusSetId, $statusData["id"]);
        Assert::type("string", $statusData["name"]);
        Assert::type("array", $statusData["statuses"]);

        $correctStatus = null;
        foreach ($statusData["statuses"] as $status) {
            Assert::hasKey("id", $status);
            Assert::hasKey("code", $status);
            Assert::hasKey("caption", $status);
            Assert::hasKey("updatedById", $status);
            Assert::hasKey("updatedAt", $status);
            Assert::equal($statusSetId, $status["statusSetId"]);
            if ($status["code"] == $mockedStatusData["code"]) {
                $correctStatus = $status;
            }
        }

        Assert::truthy($correctStatus);
        Assert::equal($correctStatus["code"], $mockedStatusData["code"]);
        Assert::equal($correctStatus["caption"], $mockedStatusData["caption"]);
    }
    /**
     * Return id of random, already existing status set
     *
     * @return int
     */
    private function getStatusSetId(): int
    {
        $allStatusSets = $this->request("attendanceStatus")->expect(200, "array")->getData();
        return $allStatusSets[random_int(0, (is_countable($allStatusSets) ? count($allStatusSets) : 0) - 1)]["id"];
    }

    public function createRecord(): void
    {
        //use creator from recorManager - can create status or statusSet
    }

    public function mockRecord(): void
    {
        //use mocker from recordManager - can mock status or statusSet
    }


    /**
     * @return mixed[]
     */
    protected function mockChanges(): array
    {
        return [];
    }
}

(new AttendanceStatusTest($container))->run();
