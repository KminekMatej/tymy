<?php
/**
 * TEST: Test Poll detail on TYMY api
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

class APIPollTest extends ITapiTest {

    /** @var \Tymy\Poll */
    private $poll;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->poll;
    }
    
    protected function setUp() {
        $this->poll = $this->container->getByType('Tymy\Poll');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */

    function testSelectNotLoggedInFailsNoRecId() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->poll->reset()->getResult(TRUE);} , "\Tymy\Exception\APIException", "Poll ID not set!");

    }
    
    function testFetchNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->poll->reset()->recId(1)->getResult(TRUE);} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
    }
        
    
    function testFetchSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $pollId = $GLOBALS["testedTeam"]["testPollId"];
        $this->poll->reset()->recId($pollId)->getResult(TRUE);

        Assert::true(is_object($this->poll));
        Assert::true(is_object($this->poll->result));
        Assert::type("string",$this->poll->result->status);
        Assert::same("OK",$this->poll->result->status);
        
        Assert::type("int",$this->poll->result->data->id);
        Assert::same($pollId,$this->poll->result->data->id);
        
        Assert::type("int",$this->poll->result->data->createdById);
        Assert::type("string",$this->poll->result->data->createdAt);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $this->poll->result->data->createdAt)); //timezone correction check
        Assert::type("int",$this->poll->result->data->updatedById);
        Assert::type("string",$this->poll->result->data->updatedAt);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $this->poll->result->data->updatedAt)); //timezone correction check
        Assert::type("string",$this->poll->result->data->caption);
        Assert::type("string",$this->poll->result->data->description);
        if(property_exists($this->poll->result->data, "minItems")){
            Assert::type("int",$this->poll->result->data->minItems);
            Assert::true($this->poll->result->data->minItems > 0 || $this->poll->result->data->minItems == -1);
        }
        if(property_exists($this->poll->result->data, "maxItems")){
            Assert::type("int",$this->poll->result->data->maxItems);
            Assert::true($this->poll->result->data->maxItems > 0 || $this->poll->result->data->maxItems == -1);
            Assert::true($this->poll->result->data->maxItems >= $this->poll->result->data->minItems);
        }
        Assert::type("bool",$this->poll->result->data->changeableVotes);
        Assert::type("bool",$this->poll->result->data->mainMenu);
        Assert::type("bool",$this->poll->result->data->anonymousResults);
        Assert::type("string",$this->poll->result->data->showResults);
        Assert::true(in_array($this->poll->result->data->showResults, ["NEVER", "ALWAYS", "AFTER_VOTE", "WHEN_CLOSED"]));
        Assert::type("string",$this->poll->result->data->status);
        Assert::true(in_array($this->poll->result->data->status, ["DESIGN", "OPENED", "CLOSED"]));
        Assert::type("string",$this->poll->result->data->resultRightName);
        Assert::type("string",$this->poll->result->data->voteRightName);
        Assert::type("int",$this->poll->result->data->orderFlag);
        
        Assert::type("array",$this->poll->result->data->options);
        foreach ($this->poll->result->data->options as $opt) {
            Assert::type("int",$opt->id);
            Assert::true($opt->id > 0);
            Assert::type("int",$opt->pollId);
            Assert::same($pollId,$opt->pollId);
            Assert::type("string",$opt->caption);
            Assert::type("string",$opt->type);
            Assert::true(in_array($opt->type, ["TEXT", "NUMBER", "BOOLEAN"]));
        }
        foreach ($this->poll->result->data->votes as $vote) {
            Assert::type("int",$vote->pollId);
            Assert::same($pollId,$vote->pollId);
            Assert::type("int",$vote->userId);
            Assert::true($vote->userId > 0);
            
            //check option
            Assert::type("int",$vote->optionId);
            Assert::true($vote->optionId > 0);
            
            $found = FALSE;
            foreach ($this->poll->result->data->options as $option) {
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
    
    function testVoteSuccess(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $pollId = $GLOBALS["testedTeam"]["testPollId"];
        $votes = [["userId" => $this->user->getId(), "optionId" => 5, "stringValue" => md5("xxx".rand(0,1000))],
                    ["userId" => $this->user->getId(), "optionId" => 8, "numericValue" => ""]];
        $this->poll->reset()->recId($pollId)->vote($votes);
    }
    
    /**
     * @throws \Tymy\Exception\APIException
     */
    function testVoteFailureTooMuch(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $pollId = $GLOBALS["testedTeam"]["testPollId"];
        $votes = [
            ["userId" => $this->user->getId(), "optionId" => 5, "stringValue" => md5("xxx".rand(0,1000))],
            ["userId" => $this->user->getId(), "optionId" => 8, "numericValue" => 333],
            ["userId" => $this->user->getId(), "optionId" => 7, "stringValue" => "neprojde, moc polozek"]
            ];
        $this->poll->reset()->recId($pollId)->vote($votes);
    }

    /**
     * @throws \Tymy\Exception\APIException
     */
    function testVoteFailureInvalidNumericInput(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $pollId = $GLOBALS["testedTeam"]["testPollId"];
        $votes = [
            ["userId" => $this->user->getId(), "optionId" => 5, "stringValue" => md5("xxx".rand(0,1000))],
            ["userId" => $this->user->getId(), "optionId" => 8, "numericValue" => md5("xxx".rand(0,1000))],
            ];
        $this->poll->reset()->recId($pollId)->vote($votes);
    }

    /**
     * @throws \Tymy\Exception\APIException
     */
    function testVoteFailureInvalidBoolInput(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $pollId = $GLOBALS["testedTeam"]["testPollId"];
        $votes = [
            ["userId" => $this->user->getId(), "optionId" => 5, "stringValue" => md5("xxx".rand(0,1000))],
            ["userId" => $this->user->getId(), "optionId" => 10, "booleanValue" => md5("xxx".rand(0,1000))],
            ];
        $this->poll->reset()->recId($pollId)->vote($votes);
    }
    
    protected function tearDown() {
        parent::tearDown();
        //make last correct vote to set the database to normal
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $votes = [
            ["userId" => $this->user->getId(), "optionId" => 5, "stringValue" => md5("xxx".rand(0,1000))],
            ["userId" => $this->user->getId(), "optionId" => 8, "numericValue" => rand(0,1000)],
            ];
        $pollId = $GLOBALS["testedTeam"]["testPollId"];
        $this->poll->reset()->recId($pollId)->vote($votes);
    }

    
}

$test = new APIPollTest($container);
$test->run();
