<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Permission;

use Tymy\Bootstrap;
use Tymy\Module\Autotest\ApiTest;
use Tymy\Module\Autotest\Entity\Assert;
use Tymy\Module\Permission\Model\Permission;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of PermissionTest
 */
class PermissionTest extends ApiTest
{
    public function getModule(): string
    {
        return Permission::MODULE;
    }

    public function testGetSingular(): void
    {
        $this->authorizeAdmin();
        $this->request($this->getBasePath())->expect(200, "array");
    }

    public function testGetPlural(): void
    {
        $this->authorizeAdmin();
        $this->request($this->getBasePath() . "s")->expect(200, "array");
    }

    public function testCRUDSingular(): void
    {
        $recordId = $this->createRecord();

        $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");

        $this->change($recordId);

        $this->deleteRecord($recordId);
    }

    public function testCRUDPlural(): void
    {
        $this->authorizeAdmin();
        $response = $this->request("permissions", "POST", $this->mockRecord())->expect(201, "array");
        $permissionId = $response->getData()["id"];

        $this->request("permissions/" . $permissionId)->expect(200, "array");

        $changes = $this->mockChanges();

        $changedData = $this->request("permissions/" . $permissionId, "PUT", $changes)->expect(200, "array");

        foreach ($changes as $key => $changed) {
            Assert::hasKey($key, $changedData->getData());
            Assert::equal($changed, $changedData->getData()[$key]);
        }

        $this->request("permissions/" . $permissionId, "DELETE")->expect(200);
    }

    public function testPermissionName(): void
    {
        $this->request("permissionName/EVE_CREATE")->expect(200, "array");
        $this->request("permissionName/ADMINONLY")->expect(200, "array");
    }

    public function createRecord(): array
    {
        return $this->recordManager->createPermission();
    }

    public function mockRecord(): array
    {
        return $this->recordManager->mockPermission();
    }


    /**
     * @return mixed[]
     */
    protected function mockChanges(): array
    {
        return [];
    }
}

(new PermissionTest($container))->run();
