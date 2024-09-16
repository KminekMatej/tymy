<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Event;

use Tymy\Bootstrap;
use Tymy\Module\Autotest\Entity\Assert;
use Tymy\Module\Autotest\RequestCase;
use Tymy\Module\Event\Model\Event;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of EventTest
 */
class EventTest extends RequestCase
{
    public function getModule(): string
    {
        return Event::MODULE;
    }

    public function testGet(): void
    {
        $this->authorizeAdmin();
        $this->request($this->getBasePath())->expect(200, "array");
    }

    public function testCreateMultiple(): void
    {
        $event1 = $this->mockRecord();
        $event2 = $this->mockRecord();
        $event3 = $this->mockRecord();

        $this->request($this->getBasePath(), "POST", [$event1, $event2, $event3])->expect(201, "array");
    }

    public function testCRUDSingular(): void
    {
        $recordId = $this->createRecord();

        $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");
        $this->request($this->getBasePath() . "/" . $recordId . "/history")->expect(200, "array");

        $this->change($recordId);

        $this->deleteRecord($recordId);
    }

    public function testCRUDPlural(): void
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

    public function testWithMyAttendance(): void
    {
        $this->request("events/withMyAttendance")->expect(200, "array");
    }

    public function testEventTypes(): void
    {
        $this->request("eventTypes")->expect(200, "array");
    }

    public function createRecord(): int
    {
        return $this->recordManager->createEvent();
    }

    /**
     * @return array<string, mixed>
     */
    public function mockRecord(): array
    {
        return $this->recordManager->mockEvent();
    }


    /**
     * @return mixed[]
     */
    protected function mockChanges(): array
    {
        return [];
    }
}

(new EventTest($container))->run();
