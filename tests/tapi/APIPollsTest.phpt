<?php
/**
 * TEST: Test Polls on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class APIPollsTest extends ITapiTest {

    /** @var \Tymy\Polls */
    private $polls;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->polls;
    }
    
    protected function setUp() {
        $this->polls = $this->container->getByType('Tymy\Polls');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    function testMenu(){
        $value = TRUE;
        $this->polls->setMenu($value);
        Assert::equal($this->polls->getMenu(), $value);
    }
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */


    function testSelectNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->polls->reset()->getResult(TRUE);} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
    }
        
    function testSelectSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->polls->reset()->getResult(TRUE);
        
        Assert::same(1, count($this->polls->getUriParams()));
        
        Assert::true(is_object($this->polls));
        Assert::true(is_object($this->polls->result));
        Assert::type("string",$this->polls->result->status);
        Assert::same("OK",$this->polls->result->status);
        
        Assert::type("array",$this->polls->result->data);
        var_dump($this->polls->result->data);
        foreach ($this->polls->result->data as $poll) {
            Assert::true(is_object($poll));
            Assert::type("int",$poll->id);
            Assert::true($poll->id > 0);
            Assert::type("int",$poll->createdById);
            Assert::true($poll->createdById > 0);
            Assert::type("string",$poll->createdAt);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $poll->createdAt)); //timezone correction check
            Assert::type("int",$poll->updatedById);
            Assert::true($poll->updatedById >= 0); //updatedById can be zero if nobody updated it yet
            Assert::type("string",$poll->updatedAt);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $poll->updatedAt)); //timezone correction check
            Assert::type("string",$poll->caption);
            if(property_exists($poll, "description")) Assert::type("string",$poll->description);
            if(property_exists($poll, "descriptionHtml")) Assert::type("string",$poll->descriptionHtml);
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
