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
 * Description of AttendanceTest
 *
 * @author kminekmatej, 04.11.2020 21:47:07
 *
 */
class AttendanceTest extends RequestCase
{
    private ?int $eventId = null;

    public function getModule(): string
    {
        return Attendance::MODULE;
    }

    public function testPost(): void
    {
        $this->authorizeAdmin();
        //pre
        $this->eventId = $this->recordManager->createEvent();
        $mocked = $this->mockRecord();

        $this->authorizeUser();

        $this->request($this->getBasePath(), "POST", $mocked)->expect(200, "array");

        //now change my plan to see if history record has been created
        $mocked["preStatus"] = "LAT";
        $mocked["preDescription"] = "Autotest will come later";
        $this->request($this->getBasePath(), "POST", $mocked)->expect(200, "array");

        $eventData = $this->request("event/{$this->eventId}")->expect(200, "array")->getData();

        Assert::hasKey("myAttendance", $eventData, print_r($eventData, true));
        Assert::equal($mocked["preStatus"], $eventData["myAttendance"]["preStatus"], print_r($eventData, true));
        Assert::equal($mocked["preDescription"], $eventData["myAttendance"]["preDescription"], print_r($eventData, true));

        $eventHistoryData = $this->request("event/{$this->eventId}/history")->expect(200, "array")->getData();
        $lastHistory = array_pop($eventHistoryData);
        Assert::equal($mocked["preStatus"], $lastHistory["preStatusTo"], "Last history preStatusTo is {$lastHistory["preStatusTo"]} but should be {$mocked["preStatus"]}");
        Assert::equal($mocked["preDescription"], $lastHistory["preDescTo"], print_r($lastHistory, true));

        $mocked["preStatus"] = "NO";
        $mocked["preDescription"] = "Autotest will not come";
        $this->request($this->getBasePath(), "POST", $mocked)->expect(200, "array");

        $eventData = $this->request("event/{$this->eventId}")->expect(200, "array")->getData();
        Assert::equal($mocked["preStatus"], $eventData["myAttendance"]["preStatus"], print_r($lastHistory, true));
        Assert::equal($mocked["preDescription"], $eventData["myAttendance"]["preDescription"], print_r($lastHistory, true));

        $eventHistoryData = $this->request("event/{$this->eventId}/history")->expect(200, "array")->getData();
        $lastHistory = array_pop($eventHistoryData);
        Assert::equal($mocked["preStatus"], $lastHistory["preStatusTo"], "Last history preStatusTo is {$lastHistory["preStatusTo"]} but should be {$mocked["preStatus"]}");
        Assert::equal($mocked["preDescription"], $lastHistory["preDescTo"]);

        //read attendance
        $eventData = $this->request("event/{$this->eventId}")->expect(200, "array")->getData();
        Assert::hasKey("myAttendance", $eventData);
        Assert::equal($mocked["preStatus"], $eventData["myAttendance"]["preStatus"]);
        Assert::equal($mocked["preDescription"], $eventData["myAttendance"]["preDescription"]);
        Assert::equal($this->config["user_test_id"], $eventData["myAttendance"]["preUserMod"]);

        //post - this user is forbidden to set attendance results
        $mocked = $this->recordManager->mockAttendance($this->eventId, false, true);
        $this->request($this->getBasePath(), "POST", $mocked)->expect(403);

        $this->authorizeAdmin();
        $mocked["userId"] = $this->config["user_test_id"];
        $this->request($this->getBasePath(), "POST", $mocked)->expect(200, "array");

        $this->authorizeUser();

        $eventData = $this->request("event/{$this->eventId}")->expect(200, "array")->getData();
        Assert::hasKey("myAttendance", $eventData);

        Assert::equal($mocked["postStatus"], $eventData["myAttendance"]["postStatus"]);
        Assert::equal($mocked["postDescription"], $eventData["myAttendance"]["postDescription"]);
        Assert::equal($this->config["user_admin_id"], $eventData["myAttendance"]["postUserMod"]);

        //fail
        $this->request($this->getBasePath(), "POST", [])->expect(400);
        $this->request($this->getBasePath(), "POST", [[],[]])->expect(400);

        //test sending invalid requests
        $mocked = $this->recordManager->mockAttendance($this->eventId, true, true);
        unset($mocked["preStatus"]);
        unset($mocked["postStatus"]);
        $this->request($this->getBasePath(), "POST", $mocked)->expect(400);
    }

    public function testViewPlanForbidden(): void
    {
        $this->authorizeAdmin();
        $eventId = $this->recordManager->createEvent(null, ["resultRightName" => null]);

        $this->authorizeUser();

        //test user cannot set result
        $mockedResult = $this->recordManager->mockAttendance($eventId, false, true);
        $this->request($this->getBasePath(), "POST", $mockedResult)->expect(403); //user not permitted to add result

        //test user not permitted to add plan as different user
        $mockedPlan = $this->recordManager->mockAttendance($eventId, true, false);
        $mockedPlan["userId"] = $this->config["user_admin_id"];
        $this->request($this->getBasePath(), "POST", $mockedPlan)->expect(403);

        $this->authorizeAdmin();
        //test admin permitted to add plan as different user
        $this->request($this->getBasePath(), "POST", $mockedPlan)->expect(200, "array");
    }

    public function testPermissivePost(): void
    {
        $this->authorizeAdmin();
        $this->eventId = $this->recordManager->createEvent(null, ["viewRightName" => "ADMINONLY", "planRightName" => "ADMINONLY", "resultRightName" => "ADMINMEMBER"]);
        $mocked = $this->mockRecord();

        $this->authorizeUser();

        $this->request("event/{$this->eventId}")->expect(403)->getData(); //user not permitted to view
        $allEvents = $this->request("event")->expect(200, "array")->getData(); //user not permitted to view event detail
        foreach ($allEvents as $event) { //user not permitted to view this event in list
            Assert::notEqual($this->eventId, $event["id"]);
        }

        $this->request($this->getBasePath(), "POST", $mocked)->expect(403); //user not permitted to attend

        $mockedResult = $this->recordManager->mockAttendance($this->eventId, false, true);
        $this->request($this->getBasePath(), "POST", $mockedResult)->expect(403); //user is forbidden to fill result

        $this->authorizeAdmin();
        $this->request($this->getBasePath(), "POST", $mocked)->expect(200); //admin permitted to attend

        $this->authorizeAdmin($this->config["user_member_login"], $this->config["user_member_pwd"]);
        $this->request($this->getBasePath(), "POST", $mockedResult)->expect(200, "array"); //member is allowed to fill result
    }

    public function testUnknownEvent(): void
    {
        $this->authorizeUser();
        $mocked = $this->recordManager->mockAttendance(99999, true, false);
        $this->request($this->getBasePath(), "POST", $mocked)->expect(404); //event doesnt exist
    }

    public function createRecord(): void
    {
        //attendance are never created solely, always just posted to some event
    }

    public function mockRecord(): array
    {
        return $this->recordManager->mockAttendance($this->eventId, true, false);
    }


    /**
     * @return mixed[]
     */
    protected function mockChanges(): array
    {
        return [];
    }
}

(new AttendanceTest($container))->run();
