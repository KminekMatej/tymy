<?php

// phpcs:disable PSR1.Files.SideEffects

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
    public function getModule(): string
    {
        return Right::MODULE;
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

    public function testRightUserSingular(): void
    {
        $this->authorizeAdmin();
        $this->request($this->getBasePath() . "/user")->expect(200, "array");
    }

    public function testRightUserPlural(): void
    {
        $this->authorizeAdmin();
        $this->request($this->getBasePath() . "s/user")->expect(200, "array");
    }

    public function createRecord(): void
    {
        //cannot create
    }

    public function mockRecord(): void
    {
        //cannot mock
    }


    /**
     * @return mixed[]
     */
    protected function mockChanges(): array
    {
        return [];
    }
}

(new RightTest($container))->run();
