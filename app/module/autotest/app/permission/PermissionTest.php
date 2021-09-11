<?php

namespace Tymy\Module\Autotest\Permission;

use Tymy\Bootstrap;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Autotest\Entity\Assert;
use Tymy\Module\Autotest\RequestCase;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of PermissionTest
 *
 * @author kminekmatej, 19.10.2020 21:47:07
 *
 */
class PermissionTest extends RequestCase
{
    public function getModule(): string
    {
        return Permission::MODULE;
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
        $recordId = $this->createRecord();

        $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");

        $this->change($recordId);

        $this->deleteRecord($recordId);
    }

    public function testCRUDPlural()
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

    public function testPermissionName()
    {
        $this->request("permissionName/EVE_CREATE")->expect(200, "array");
        $this->request("permissionName/ADMINONLY")->expect(200, "array");
    }

    public function createRecord()
    {
        return $this->recordManager->createPermission();
    }

    public function mockRecord()
    {
        return $this->recordManager->mockPermission();
    }


    protected function mockChanges(): array
    {
        return [];
    }
}

(new PermissionTest($container))->run();
