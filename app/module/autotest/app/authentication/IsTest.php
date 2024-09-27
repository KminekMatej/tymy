<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Authentication;

use Tymy\Bootstrap;
use Tymy\Module\Autotest\ApiTest;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of IsTest
 */
class IsTest extends ApiTest
{
    public function testUnauthorized(): void
    {
        //do nothing, is is always returned
    }

    public function testIs(): void
    {
        $this->request($this->getBasePath())->expect(200, "array");
    }

    /**
     * @return mixed[]
     */
    protected function mockChanges(): array
    {
        return [];
    }

    public function createRecord(): array
    {
        //not used in this test
    }

    protected function getBasePath(): string
    {
        return "is";
    }

    public function getModule(): string
    {
        return "authentication";
    }

    public function mockRecord(): void
    {
        //not used in this test
    }
}

(new IsTest($container))->run();
