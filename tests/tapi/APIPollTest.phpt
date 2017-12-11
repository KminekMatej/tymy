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

    /** @var \Tymy\PollOption */
    private $pollOption;
    
    private $createdPollId;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->poll;
    }
    
    protected function setUp() {
        $this->poll = $this->container->getByType('Tymy\Poll');
        $this->pollOption = $this->container->getByType('Tymy\PollOption');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : CREATE */
    
    function testCreateFailsNoData(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->poll->reset()->create(NULL);} , "\Tymy\Exception\APIException", "Poll not set!");
    }
    
    function testCreateFailsNoRights(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        Assert::exception(function(){$this->poll->reset()->create(["caption" => "Autotest " . rand(0, 100)]);} , "\Tymy\Exception\APIException", "Permission denied!");
    }
    
    function testCreateSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $pollCaption = "Autotest " . rand(0, 100);
        $this->poll->reset()->create(["caption" => $pollCaption]);
        
        Assert::true(is_object($this->poll));
        Assert::true(is_object($this->poll->result));
        Assert::type("string",$this->poll->result->status);
        Assert::same("OK",$this->poll->result->status);
        $data = $this->poll->result->data;
        Assert::type("int",$data->id);
        $this->createdPollId = $data->id;
        Assert::equal($pollCaption,$data->caption);
        Assert::type("int",$data->createdById);
        Assert::equal(-1,$data->minItems);
        Assert::equal(-1,$data->maxItems);
        Assert::true($data->changeableVotes);
        Assert::true(!$data->mainMenu);
        Assert::true(!$data->anonymousResults);
        Assert::equal("NEVER",$data->showResults);
        Assert::equal("DESIGN",$data->status);
        Assert::true($data->resultRightName == "");
        Assert::true($data->voteRightName == "");
        Assert::true($data->alienVoteRightName == "");
        Assert::true($data->resultRightName == "");
        Assert::equal(0,$data->orderFlag);
        Assert::true(!$data->voted);
    }
    
    /* TAPI : EDIT */
    
    function testEditFailsNoRecId() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->poll->reset()->edit(NULL);} , "\Tymy\Exception\APIException", "Poll ID not set!");
    }
    
    function testEditFailsNoFields() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->poll->reset()->recId($this->createdPollId)->edit(NULL);} , "\Tymy\Exception\APIException", "Fields to edit not set!");
    }
    
    function testEditFailsNoRights() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        Assert::exception(function(){$this->poll->reset()->recId($this->createdPollId)->edit(["caption" => "Autotest " . rand(100, 200)]);} , "\Tymy\Exception\APIException", "Permission denied!");
    }
    
    function testEditSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $pollCaption = "Autotest " . rand(100, 200);
        $this->poll->reset()->recId($this->createdPollId)->edit(["caption" => $pollCaption]);
        
        Assert::true(is_object($this->poll));
        Assert::true(is_object($this->poll->result));
        Assert::type("string",$this->poll->result->status);
        Assert::same("OK",$this->poll->result->status);
        Assert::equal($this->createdPollId,$this->poll->result->data->id);
        Assert::type("string",$this->poll->result->data->caption);
        Assert::equal($pollCaption,$this->poll->result->data->caption);
    }
    
    /* TAPI : CREATE POLL OPTION */
    
    function testCreatePollOptionFailsNoPollId(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->pollOption->reset()->create(NULL);} , "\Tymy\Exception\APIException", "Poll ID not set!");
    }
    
    function testCreatePollOptionFailsNoData(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->pollOption->reset()->recId($this->createdPollId)->create(NULL);} , "\Tymy\Exception\APIException", "Fields to create not set!");
    }
    
    function testCreatePollOptionFailsNoRights(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        Assert::exception(function(){$this->pollOption->reset()->recId($this->createdPollId)->create([["caption"=>"Polozka text 1", "type"=>"TEXT"]]);} , "\Tymy\Exception\APIException", "Permission denied!");
    }
    
    function testCreatePollOptionSuccess(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->pollOption->reset()->recId($this->createdPollId)->create([["caption"=>"Polozka text 1", "type"=>"TEXT"],["caption"=>"Polozka num 1", "type"=>"NUMBER"],["caption"=>"Polozka boolean 1", "type"=>"BOOLEAN"]]);
    }
    
    /* TAPI : DELETE */
    
    function testDeleteFailsNoRecId() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->poll->reset()->delete();} , "\Tymy\Exception\APIException", "Poll ID not set!");
    }
    
    function testDeleteFailsNoRights() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        Assert::exception(function(){$this->poll->reset()->recId($this->createdPollId)->delete();} , "\Tymy\Exception\APIException", "Permission denied!");
    }
    
    function testDeleteSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->poll->reset()->recId($this->createdPollId)->delete();
        
        Assert::true(is_object($this->poll));
        Assert::true(is_object($this->poll->result));
        Assert::type("string",$this->poll->result->status);
        Assert::same("OK",$this->poll->result->status);
    }
    
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
            
            Assert::type("int",$vote->updatedById);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $vote->updatedAt)); //timezone correction check
        }
    }
    
    function testFetchAnonymousSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $pollId = $GLOBALS["testedTeam"]["testAnonymousPollId"];
        $this->poll->reset()->recId($pollId)->getResult(TRUE);

        Assert::true(is_object($this->poll));
        Assert::true(is_object($this->poll->result));
        Assert::type("string",$this->poll->result->status);
        Assert::same("OK",$this->poll->result->status);
        
        Assert::type("int",$this->poll->result->data->id);
        Assert::same($pollId,$this->poll->result->data->id);
        
        Assert::equal(true,$this->poll->result->data->anonymousResults);
        
        Assert::type("array",$this->poll->result->data->options);
        print_r($this->poll->result->data->options);
        foreach ($this->poll->result->data->options as $opt) {
            Assert::true(!property_exists($opt, "updatedById"));
            Assert::true(!property_exists($opt, "updatedBy"));
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
