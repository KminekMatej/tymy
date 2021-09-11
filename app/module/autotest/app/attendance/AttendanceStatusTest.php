<?php

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
 * @author kminekmatej, 07.11.2020 21:47:07
 *
    @RequestMapping(value = "/attendanceStatusSet", method = RequestMethod.POST)
    @RequestMapping(value = "/attendanceStatusSet/{id}", method = RequestMethod.PUT)
    @RequestMapping(value = "/attendanceStatusSet/{id}", method = RequestMethod.DELETE)
 *
 *
 */
class AttendanceStatusTest extends RequestCase
{
    public function getBasePath()
    {
        return "/" . basename(__DIR__) . "Status";
    }

    public function getStatusSetPath()
    {
        return "/" . basename(__DIR__) . "StatusSet";
    }

    public function getModule(): string
    {
        return Attendance::MODULE;
    }

    public function testGet()
    {
        $this->authorizeAdmin();

        $list = $this->request($this->getBasePath())->expect(200, "array")->getData();

        foreach ($list as $record) {
            Assert::type("array", $record);
        }

        $last = array_pop($list);
        $this->request($this->getBasePath() . "/" . $last["id"])->expect(200, "array");
    }

    public function testCRUDStatus()
    {
        //createStatus under some status set
        $statusSetId = $this->getStatusSetId();

        $statusData = $this->recordManager->mockStatus($statusSetId);
        $statusResponseData = $this->request($this->getBasePath(), "POST", $statusData)->expect(201, "array")->getData();

        $this->_testObjectEquality($statusData, $statusResponseData, ["image"]);
        Assert::hasKey("updatedById", $statusResponseData);
        Assert::hasKey("updatedAt", $statusResponseData);
        Assert::type("int", $statusResponseData["updatedById"]);
        Assert::truthy($statusResponseData["updatedById"]);

        $statusId = $statusResponseData["id"];

        //now get the status set and check created status is there

        $this->checkStatusOfStatusSet($statusSetId, $statusResponseData);

        //now try to update this status using PUT request

        $rand = rand(0, 1000);
        $changeMock = [
            "code" => strtoupper(substr(md5($rand), 0, 3)),
            "caption" => "Changed name $rand",
        ];
        $this->request($this->getBasePath() . "/$statusId", "PUT", $changeMock)->expect(200, "array");

        $this->checkStatusOfStatusSet($statusSetId, $changeMock);

        //now delete this status

        $this->request($this->getBasePath() . "/$statusId", "DELETE")->expect(200);
    }

    public function testStatusSetForbidden()
    {
        $this->authorizeUser();

        $statusSetData = $this->recordManager->mockStatusSet();
        $this->request($this->getStatusSetPath(), "POST", $statusSetData)->expect(403);
    }

    public function testStatusSetNoName()
    {
        $this->authorizeAdmin();

        $statusSetData = $this->recordManager->mockStatusSet();
        unset($statusSetData["name"]);
        $this->request($this->getStatusSetPath(), "POST", $statusSetData)->expect(400);
    }

    public function testCRUDStatusSet()
    {
        $this->authorizeAdmin();

        $statusSetData = $this->recordManager->mockStatusSet();
        $statusSetResponseData = $this->request($this->getStatusSetPath(), "POST", $statusSetData)->expect(201, "array")->getData();
        $this->_testObjectEquality($statusSetData, $statusSetResponseData);
        $statusSetId = $statusSetResponseData["id"];

        //now get the status set and check created status is there

        $statusGetResponseData = $this->request($this->getBasePath() . "/$statusSetId")->expect(200, "array")->getData();
        $this->_testObjectEquality($statusSetData, $statusGetResponseData);
        Assert::equal($statusSetId, $statusGetResponseData["id"]);
        Assert::hasKey("statuses", $statusGetResponseData);
        Assert::type("array", $statusGetResponseData["statuses"]);
        Assert::count(0, $statusGetResponseData["statuses"]);

        //now try to update this status using PUT request

        $rand = rand(0, 1000);
        $changeMock = [
            "name" => "Changed SS name$rand",
        ];
        $this->request($this->getStatusSetPath() . "/$statusSetId", "PUT", $changeMock)->expect(200, "array");

        $statusGetResponseData = $this->request($this->getBasePath() . "/$statusSetId")->expect(200, "array")->getData();
        $this->_testObjectEquality($changeMock, $statusGetResponseData);

        //now delete this status
        $this->request($this->getStatusSetPath() . "/$statusSetId", "DELETE")->expect(200);
    }

    private function checkStatusOfStatusSet(int $statusSetId, array $mockedStatusData)
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
            Assert::type("int", $status["updatedById"]);
            Assert::truthy($status["updatedById"]);
            Assert::equal($statusSetId, $status["statusSetId"]);
            if ($status["code"] == $mockedStatusData["code"]) {
                $correctStatus = $status;
            }
        }

        Assert::truthy($correctStatus);
        Assert::equal($correctStatus["code"], $mockedStatusData["code"]);
        Assert::equal($correctStatus["caption"], $mockedStatusData["caption"]);
    }

    private function createStatus(int $statusSetId, ?array $changes = null): int
    {
        return $this->recordManager->createStatus($statusSetId, null, $changes);
    }
    /**
     * Return id of random, already existing status set
     * @return type
     */
    private function getStatusSetId()
    {
        $allStatusSets = $this->request("attendanceStatus")->expect(200, "array")->getData();
        return $allStatusSets[rand(0, count($allStatusSets)-1)]["id"];
    }

    public function createRecord()
    {
        //use creator from recorManager - can create status or statusSet
    }

    public function mockRecord()
    {
        //use mocker from recordManager - can mock status or statusSet
    }


    protected function mockChanges(): array
    {
        return [];
    }
}

(new AttendanceStatusTest($container))->run();
