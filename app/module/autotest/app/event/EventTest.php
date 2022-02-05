<?php

namespace Tymy\Module\Autotest\Event;

use Tymy\Bootstrap;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Autotest\RequestCase;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of EventTest
 *
 * @author kminekmatej, 07.10.2020 21:47:07
 *
 */
class EventTest extends RequestCase
{
    public function getModule(): string
    {
        return Event::MODULE;
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

    public function testCreateMultiple()
    {
        $event1 = $this->mockRecord();
        $event2 = $this->mockRecord();
        $event3 = $this->mockRecord();

        $this->request($this->getBasePath(), "POST", [$event1, $event2, $event3])->expect(201, "array");
    }

    public function testCRUDSingular()
    {
        $recordId = $this->createRecord();

        $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");
        $this->request($this->getBasePath() . "/" . $recordId . "/history")->expect(200, "array");

        $this->change($recordId);

        $this->deleteRecord($recordId);
    }

    public function testCRUDPlural()
    {
        $this->authorizeAdmin();
        $response = $this->request("events", "POST", $this->mockRecord())->expect(201, "array");
        $eventId = $response->getData()["id"];

        $this->request("events/" . $eventId)->expect(200, "array");
        $this->request("events/" . $eventId . "/history")->expect(200, "array");

        $changes = $this->mockChanges();

        $changedData = $this->request("events/" . $eventId, "PUT", $changes)->expect(200, "array");

        foreach ($changes as $key => $changed) {
            Assert::hasKey($key, $changedData->getData());
            Assert::equal($changed, $changedData->getData()[$key]);
        }

        $this->request("events/" . $eventId, "DELETE")->expect(200);
    }

    public function testWithMyAttendance()
    {
        $this->request("events/withMyAttendance")->expect(200, "array");
    }

    public function testEventTypes()
    {
        $this->request("eventTypes")->expect(200, "array");
    }

    public function createRecord()
    {
        return $this->recordManager->createEvent();
    }

    public function mockRecord()
    {
        return $this->recordManager->mockEvent();
    }


    protected function mockChanges(): array
    {
        return [];
    }
}

(new EventTest($container))->run();
