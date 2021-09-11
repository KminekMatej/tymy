<?php

namespace Tymy\Module\Autotest\Right;

use Tymy\Bootstrap;
use Tymy\Module\Right\Model\Right;
use Tymy\Module\Autotest\RequestCase;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of RightTest
 *
 * @author kminekmatej, 19.10.2020 21:47:07
 *
 */
class RightTest extends RequestCase
{
    public function getBasePath()
    {
        return "/" . basename(__DIR__);
    }

    public function getModule(): string
    {
        return Right::MODULE;
    }

    public function testGetSingular()
    {
        $this->authorizeAdmin();
        $listResponse = $this->request($this->getBasePath())->expect(200, "array");
    }

    public function testGetPlural()
    {
        $this->authorizeAdmin();
        $listResponse = $this->request($this->getBasePath() . "s")->expect(200, "array");
    }

    public function testRightUserSingular()
    {
        $this->authorizeAdmin();
        $listResponse = $this->request($this->getBasePath() . "/user")->expect(200, "array");
    }

    public function testRightUserPlural()
    {
        $this->authorizeAdmin();
        $listResponse = $this->request($this->getBasePath() . "s/user")->expect(200, "array");
    }

    public function createRecord()
    {
        //cannot create
    }

    public function mockRecord()
    {
        //cannot mock
    }


    protected function mockChanges(): array
    {
        return [];
    }
}

(new RightTest($container))->run();
