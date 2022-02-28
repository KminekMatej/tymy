<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Multiaccount;

use Tymy\Bootstrap;
use Tymy\Module\Autotest\Entity\Assert;
use Tymy\Module\Autotest\RequestCase;
use Tymy\Module\Multiaccount\Model\TransferKey;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of MultiaccountTest
 *
 * @author kminekmatej, 07.10.2020 21:47:07
 *
 */
class MultiaccountTest extends RequestCase
{
    public function getModule(): string
    {
        return TransferKey::MODULE;
    }

    public function testGet()
    {
        $this->authorizeAdmin();
        $this->request($this->getBasePath())->expect(200, "array");
    }

    public function testPutForbidden()
    {
        $this->request($this->getBasePath(), "PUT")->expect(405);
    }

    public function testAddMultiAccount()
    {
        $this->request($this->getBasePath() . "/dev", "POST", [
            "login" => "autotest",
            "password" => "b5be656a7060dd3525027d6763c33ca0",
        ])->expect(201, "array");

        //add again should fail
        $this->request($this->getBasePath() . "/dev", "POST", [
            "login" => "autotest",
            "password" => "b5be656a7060dd3525027d6763c33ca0",
        ])->expect(400);

        $this->request($this->getBasePath() . "/asdfasdf")->expect(404);
        $response = $this->request($this->getBasePath() . "/dev")->expect(200, "array")->getData();
        Assert::count(2, $response);
        Assert::hasKey("transferKey", $response);
        Assert::hasKey("uid", $response);

        //get list of MA teams - should be two
        $list = $this->request($this->getBasePath())->expect(200, "array")->getData();
        Assert::count(2, $list);

        $this->request($this->getBasePath() . "/asdfasdf", "DELETE")->expect(404);  //test deleting non-existing team
        $this->request($this->getBasePath() . "/dev", "DELETE")->expect(200, "array");
    }

    public function testNonExistingTeam()
    {
        $this->request($this->getBasePath() . "/asdkfjbasldf", "POST")->expect(404);
    }

    public function testWrongCredentials()
    {
        $this->request($this->getBasePath() . "/dev", "POST", [
            "login" => "autotest",
            "password" => "nespravneheslo",
        ])->expect(401);

        $this->request($this->getBasePath() . "/dev", "POST", [
            "login" => "nespravnylogin",
            "password" => "b5be656a7060dd3525027d6763c33ca0",
        ])->expect(401);

        $this->request($this->getBasePath() . "/dev", "POST", ["login" => "autotest"])->expect(400);
        $this->request($this->getBasePath() . "/dev", "POST", ["password" => "b5be656a7060dd3525027d6763c33ca0"])->expect(400);
        $this->request($this->getBasePath() . "/dev", "POST", ["login" => ""])->expect(400);
        $this->request($this->getBasePath() . "/dev", "POST", ["password" => ""])->expect(400);
        $this->request($this->getBasePath() . "/dev", "POST")->expect(400);
    }

    public function testDeleteAgain()
    {
        $this->request($this->getBasePath() . "/dev", "DELETE")->expect(404);
    }

    public function testGetNonExistingTeam()
    {
        $this->request($this->getBasePath() . "/dev")->expect(404);
    }

    public function createRecord()
    {
        return []; //unused
    }

    public function mockRecord()
    {
        return []; //unused
    }

    protected function mockChanges(): array
    {
        return []; //unused
    }
}

(new MultiaccountTest($container))->run();
