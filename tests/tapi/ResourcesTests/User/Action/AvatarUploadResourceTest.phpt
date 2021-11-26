<?php

namespace Test\Tapi;

use Nette;
use Nette\Application\Request;
use Tester\Assert;
use Tester\Environment;

$container = require substr(__DIR__, 0, strpos(__DIR__, "tests/tapi")) . "tests/bootstrap.php";

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class AvatarUploadResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return FALSE;
    }

    public function getMethod() {
        return \Tymy\Module\Core\Model\RequestMethod::POST;
    }
    
    public function setCorrectInputParams() {
        $this->tapiObject->setId($this->user->getId())->setAvatar($GLOBALS["testedTeam"]["avatarB64"]);
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "User ID is missing");
        Assert::exception(function() {
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testPollId"])->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Avatar is missing");
    }

    public function testPerformSuccess() {
        parent::getPerformSuccessData();
    }

}

$test = new AvatarUploadResourceTest($container);
$test->run();
