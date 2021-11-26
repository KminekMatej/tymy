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

class UserListResourceTest extends TapiTest {
    
    public function getCacheable() {
        return TRUE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tymy\Module\Core\Model\RequestMethod::GET;
    }

    public function setCorrectInputParams() {
        // nothing to set
    }

    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();

        Assert::type("array", $data); //returned event object

        foreach ($data as $u) {
            Assert::true(is_object($u));
            Assert::type("int", $u->id);
            Assert::type("string", $u->login);
            Assert::type("bool", $u->canLogin);
            Assert::type("bool", $u->canEditCallName);
            if (property_exists($u, "lastLogin")) { // last login not returned for users that never logged before
                Assert::type("string", $u->lastLogin);
                Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $u->lastLogin)); //timezone correction check
            }

            Assert::type("string", $u->status);
            Assert::true(in_array($u->status, ["PLAYER", "MEMBER", "SICK", "DELETED", "INIT"]));

            if (property_exists($u, "firstName"))
                Assert::type("string", $u->firstName);
            if (property_exists($u, "lastName"))
                Assert::type("string", $u->lastName);
            Assert::type("string", $u->callName);
            if (property_exists($u, "language"))
                Assert::type("string", $u->language);
            if (property_exists($u, "email"))
                Assert::type("string", $u->email);
            Assert::type("string", $u->jerseyNumber);
            if (property_exists($u, "gender"))
                Assert::type("string", $u->gender);
            if (property_exists($u, "street"))
                Assert::type("string", $u->street);
            if (property_exists($u, "city"))
                Assert::type("string", $u->city);
            if (property_exists($u, "zipCode"))
                Assert::type("string", $u->zipCode);
            if (property_exists($u, "phone"))
                Assert::type("string", $u->phone);
            if (property_exists($u, "phone2"))
                Assert::type("string", $u->phone2);
            if (property_exists($u, "birthDate"))
                Assert::type("string", $u->birthDate);

            Assert::type("int", $u->nameDayMonth);
            Assert::type("int", $u->nameDayDay);
            Assert::type("string", $u->pictureUrl);
            if (property_exists($u, "fullName"))
                Assert::type("string", $u->fullName);
            Assert::type("string", $u->displayName);
            Assert::type("string", $u->webName);
            Assert::type("int", $u->errCnt);
            Assert::type("array", $u->errFls);
            foreach ($u->errFls as $errF) {
                Assert::type("string", $errF);
            }
        }
        
        Assert::type("array", $this->tapiObject->getCounts());
        Assert::contains("ALL", array_keys($this->tapiObject->getCounts()));
        Assert::contains("NEW", array_keys($this->tapiObject->getCounts()));
        Assert::contains("PLAYER", array_keys($this->tapiObject->getCounts()));
        Assert::contains("NEW:PLAYER", array_keys($this->tapiObject->getCounts()));
        Assert::contains("MEMBER", array_keys($this->tapiObject->getCounts()));
        Assert::contains("SICK", array_keys($this->tapiObject->getCounts()));
        Assert::contains("DELETED", array_keys($this->tapiObject->getCounts()));
        Assert::contains("INIT", array_keys($this->tapiObject->getCounts()));
        Assert::type("array", $this->tapiObject->getById());
        Assert::type("array", $this->tapiObject->getByTypesAndId());
        Assert::true(is_object($this->tapiObject->getMe()));
        Assert::truthy($this->tapiObject->getMe());
    }
}

$test = new UserListResourceTest($container);
$test->run();
