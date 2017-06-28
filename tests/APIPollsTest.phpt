<?php
/**
 * TEST: Test Polls on TYMY api
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

class APIPollsTest extends TapiTestCase {

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
     * @throws Nette\Application\AbortException
     */
    function testFetchNotLoggedInFails404() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "testteam", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $pollsObj = new \Tymy\Polls();
        $pollsObj->setPresenter($mockPresenter)
                ->fetch();
    }
    
    /**
     * @throws Nette\Application\AbortException
     */
    function testFetchNotLoggedInRedirects() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => $GLOBALS["testedTeam"]["team"], "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $pollsObj = new \Tymy\Polls();
        $pollsObj->setPresenter($mockPresenter)
                ->fetch();
    }
    
    function testFetchSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Poll');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => $GLOBALS["testedTeam"]["team"], "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $pollsObj = new \Tymy\Polls($mockPresenter->tapiAuthenticator, $mockPresenter);
        $pollsObj->fetch();
        
        Assert::same(1, count($pollsObj->getUriParams()));
        
        Assert::true(is_object($pollsObj));
        Assert::true(is_object($pollsObj->result));
        Assert::type("string",$pollsObj->result->status);
        Assert::same("OK",$pollsObj->result->status);
        
        Assert::type("array",$pollsObj->result->data);
        
        foreach ($pollsObj->result->data as $poll) {
            Assert::true(is_object($poll));
            Assert::type("int",$poll->id);
            Assert::true($poll->id > 0);
            Assert::type("int",$poll->createdById);
            Assert::true($poll->createdById > 0);
            Assert::type("string",$poll->createdAt);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $poll->createdAt)); //timezone correction check
            Assert::type("int",$poll->updatedById);
            Assert::true($poll->updatedById > 0);
            Assert::type("string",$poll->updatedAt);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $poll->updatedAt)); //timezone correction check
            Assert::type("string",$poll->caption);
            Assert::type("string",$poll->description);
            Assert::type("string",$poll->descriptionHtml);
            if(property_exists($poll, "minItems")){
                Assert::type("int",$poll->minItems);
                Assert::true($poll->minItems > 0 || $poll->minItems == -1);
            }
            if(property_exists($poll, "maxItems")){
                Assert::type("int",$poll->maxItems);
                Assert::true($poll->maxItems > 0 || $poll->maxItems == -1);
                Assert::true($poll->maxItems >= $poll->minItems);
            }
            Assert::type("bool",$poll->changeableVotes);
            Assert::type("bool",$poll->mainMenu);
            Assert::type("bool",$poll->anonymousResults);
            Assert::type("string",$poll->showResults);
            Assert::true(in_array($poll->showResults, ["NEVER", "ALWAYS", "AFTER_VOTE", "WHEN_CLOSED"]));
            Assert::type("string",$poll->status);
            Assert::true(in_array($poll->status, ["DESIGN", "OPENED", "CLOSED"]));
            Assert::type("string",$poll->resultRightName);
            Assert::type("string",$poll->voteRightName);
            //Assert::type("string",$poll->alienVoteRightName); // not always exists
            Assert::type("int",$poll->orderFlag);
            Assert::type("bool",$poll->canSeeResults);
            Assert::type("bool",$poll->canVote);
            Assert::type("bool",$poll->canAlienVote);
            Assert::type("bool",$poll->voted);
        }
    }
}

$test = new APIPollsTest($container);
$test->run();
