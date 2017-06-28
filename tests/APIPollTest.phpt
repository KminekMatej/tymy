<?php
/**
 * TEST: Test Poll detail on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class APIPollTest extends TapiTestCase {

    private $authenticator;
    private $container;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    function setUp() {
        parent::setUp();
        $this->initTapiConfiguration($this->container);
        $this->authenticator = new \App\Model\TestAuthenticator();
    }
    
    function tearDown() {
        parent::tearDown();
    }

    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchNotLoggedInFailsRecIdNotSet() {
        $pollObj = new \Tymy\Poll();
        $pollObj->setSupplier($this->supplier)->fetch();
    }
    
    /**
     * @throws Nette\Application\AbortException
     */
    function testFetchNotLoggedInFails404() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Poll');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "testteam", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");

        $pollObj = new \Tymy\Poll();
        $pollObj->setPresenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }
    
    /**
     * @throws Nette\Application\AbortException
     */
    function testFetchNotLoggedInRedirects() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Poll');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");

        $pollObj = new \Tymy\Poll();
        $pollObj->setPresenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }
    
    function testFetchSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Poll');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $pollId = $GLOBALS["testedTeam"]["testPollId"];
        $pollObj = new \Tymy\Poll($mockPresenter->tapiAuthenticator, $mockPresenter);
        $pollObj->recId($pollId)
                ->fetch();
        Assert::true(is_object($pollObj));
        Assert::true(is_object($pollObj->result));
        Assert::type("string",$pollObj->result->status);
        Assert::same("OK",$pollObj->result->status);
        
        Assert::type("int",$pollObj->result->data->id);
        Assert::same($pollId,$pollObj->result->data->id);
        
        Assert::type("int",$pollObj->result->data->createdById);
        Assert::type("string",$pollObj->result->data->createdAt);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $pollObj->result->data->createdAt)); //timezone correction check
        Assert::type("int",$pollObj->result->data->updatedById);
        Assert::type("string",$pollObj->result->data->updatedAt);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $pollObj->result->data->updatedAt)); //timezone correction check
        Assert::type("string",$pollObj->result->data->caption);
        Assert::type("string",$pollObj->result->data->description);
        if(property_exists($pollObj->result->data, "minItems")){
            Assert::type("int",$pollObj->result->data->minItems);
            Assert::true($pollObj->result->data->minItems > 0 || $pollObj->result->data->minItems == -1);
        }
        if(property_exists($pollObj->result->data, "maxItems")){
            Assert::type("int",$pollObj->result->data->maxItems);
            Assert::true($pollObj->result->data->maxItems > 0 || $pollObj->result->data->maxItems == -1);
            Assert::true($pollObj->result->data->maxItems >= $pollObj->result->data->minItems);
        }
        Assert::type("bool",$pollObj->result->data->changeableVotes);
        Assert::type("bool",$pollObj->result->data->mainMenu);
        Assert::type("bool",$pollObj->result->data->anonymousResults);
        Assert::type("string",$pollObj->result->data->showResults);
        Assert::true(in_array($pollObj->result->data->showResults, ["NEVER", "ALWAYS", "AFTER_VOTE", "WHEN_CLOSED"]));
        Assert::type("string",$pollObj->result->data->status);
        Assert::true(in_array($pollObj->result->data->status, ["DESIGN", "OPENED", "CLOSED"]));
        Assert::type("string",$pollObj->result->data->resultRightName);
        Assert::type("string",$pollObj->result->data->voteRightName);
        Assert::type("int",$pollObj->result->data->orderFlag);
        
        Assert::type("array",$pollObj->result->data->options);
        foreach ($pollObj->result->data->options as $opt) {
            Assert::type("int",$opt->id);
            Assert::true($opt->id > 0);
            Assert::type("int",$opt->pollId);
            Assert::same($pollId,$opt->pollId);
            Assert::type("string",$opt->caption);
            Assert::type("string",$opt->type);
            Assert::true(in_array($opt->type, ["TEXT", "NUMBER", "BOOLEAN"]));
        }
        foreach ($pollObj->result->data->votes as $vote) {
            Assert::type("int",$vote->pollId);
            Assert::same($pollId,$vote->pollId);
            Assert::type("int",$vote->userId);
            Assert::true($vote->userId > 0);
            
            //check option
            Assert::type("int",$vote->optionId);
            Assert::true($vote->optionId > 0);
            
            $found = FALSE;
            foreach ($pollObj->result->data->options as $option) {
                if($option->id == $vote->optionId){
                    $found = TRUE;
                    switch ($option->type) {
                        case "TEXT":
                            Assert::true(property_exists($vote, "stringValue"));
                            Assert::true(!property_exists($vote, "numericValue"));
                            Assert::true(!property_exists($vote, "booleanValue"));
                            Assert::type("string",$vote->stringValue);
                            break;
                        case "NUMBER":
                            Assert::true(!property_exists($vote, "stringValue"));
                            Assert::true(property_exists($vote, "numericValue"));
                            Assert::true(!property_exists($vote, "booleanValue"));
                            Assert::type("int",$vote->numericValue);
                            break;
                        case "BOOLEAN":
                            Assert::true(!property_exists($vote, "stringValue"));
                            Assert::true(!property_exists($vote, "numericValue"));
                            Assert::true(property_exists($vote, "booleanValue"));
                            Assert::type("bool",$vote->booleanValue);
                            break;
                        default:
                            Assert::true(FALSE, "Vote is neither text, number or bool");
                    }
                    break;
                }
            }
            Assert::true($found);
            Assert::type("int",$vote->updatedById);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $vote->updatedAt)); //timezone correction check
        }
    }
}

$test = new APIPollTest($container);
$test->run();
