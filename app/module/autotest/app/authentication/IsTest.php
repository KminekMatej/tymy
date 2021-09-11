<?php

namespace Tymy\Module\Autotest\Authentication;

use Tymy\Bootstrap;
use Tymy\Module\Autotest\RequestCase;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of IsTest
 *
 * @author kminekmatej, 01.10.2020 22:00:34
 *
 */
class IsTest extends RequestCase
{
    public function testUnauthorized()
    {
        //do nothing, is is always returned
    }

    public function testIs()
    {
        $this->request($this->getBasePath())->expect(200, "array");
    }

    protected function mockChanges(): array
    {
        return [];
    }

    public function createRecord()
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

    public function mockRecord()
    {
        //not used in this test
    }
}

(new IsTest($container))->run();
