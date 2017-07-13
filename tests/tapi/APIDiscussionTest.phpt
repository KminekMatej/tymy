<?php
/**
 * TEST: Test Discussion on TYMY api
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

class APIDiscussionTest extends ITapiTest {

    public $container;
    
    /** @var \Tymy\Discussion */
    private $discussion;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->discussion;
    }
    
    protected function setUp() {
        $this->discussion = $this->container->getByType('Tymy\Discussion');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    function testPage(){
        $page = 2;
        $this->discussion->setPage($page);
        Assert::equal($page, $this->discussion->getPage());
        $page = "not-a-number";
        $this->discussion->setPage($page);
        Assert::equal(1, $this->discussion->getPage(), "Page should be 1 when set non numeric");
    }
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */
    
    function testSelectFailsPageDoNotExist(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->discussion->recId(1)->setPage(0)->getResult(TRUE);}, "\Tymy\Exception\APIException", "Invalid page specified");
    }
    
    function testSelectFailsNoRecId(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->discussion->recId(NULL)->getResult(TRUE);} , "\Tymy\Exception\APIException", "Discussion ID not set!");
    }

    function testSelectNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->discussion->recId(1)->setPage(1)->getResult(TRUE);} , "Nette\Security\AuthenticationException", "Login failed.");
        
    }
        
    function testSelectSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $discussionId = $GLOBALS["testedTeam"]["testDiscussionId"];
        $this->discussion->recId($discussionId)->setPage(1)->getResult(TRUE);
        
        Assert::true(is_object($this->discussion));
        Assert::true(is_object($this->discussion->result));
        Assert::type("string",$this->discussion->result->status);
        Assert::same("OK",$this->discussion->result->status);
        Assert::true(is_object($this->discussion->result->data->discussion));//returned discussion object
        
        Assert::type("int",$this->discussion->result->data->discussion->id);
        Assert::same($discussionId, $this->discussion->result->data->discussion->id);
        Assert::type("string",$this->discussion->result->data->discussion->caption);
        Assert::type("string",$this->discussion->result->data->discussion->description);
        Assert::type("string",$this->discussion->result->data->discussion->readRightName);
        Assert::type("string",$this->discussion->result->data->discussion->writeRightName);
        Assert::type("string",$this->discussion->result->data->discussion->deleteRightName);
        Assert::type("string",$this->discussion->result->data->discussion->stickyRightName);
        Assert::type("bool",$this->discussion->result->data->discussion->publicRead);
        Assert::same(FALSE, $this->discussion->result->data->discussion->publicRead);
        Assert::type("string",$this->discussion->result->data->discussion->status);
        Assert::same("ACTIVE", $this->discussion->result->data->discussion->status);
        Assert::type("bool",$this->discussion->result->data->discussion->editablePosts);
        Assert::type("int",$this->discussion->result->data->discussion->order);
        Assert::type("bool",$this->discussion->result->data->discussion->canRead);
        Assert::type("bool",$this->discussion->result->data->discussion->canWrite);
        Assert::type("bool",$this->discussion->result->data->discussion->canDelete);
        Assert::type("bool",$this->discussion->result->data->discussion->canStick);
        Assert::type("int",$this->discussion->result->data->discussion->newPosts);
        Assert::true($this->discussion->result->data->discussion->newPosts >= 0);
        Assert::type("int",$this->discussion->result->data->discussion->numberOfPosts);
        Assert::true($this->discussion->result->data->discussion->numberOfPosts >= 0);
        Assert::true(is_object($this->discussion->result->data->discussion->newInfo));
        Assert::type("int",$this->discussion->result->data->discussion->newInfo->discussionId);
        Assert::same($discussionId, $this->discussion->result->data->discussion->newInfo->discussionId);
        Assert::type("int",$this->discussion->result->data->discussion->newInfo->newsCount);
        Assert::same($this->discussion->result->data->discussion->newPosts, $this->discussion->result->data->discussion->newInfo->newsCount);
        Assert::type("string",$this->discussion->result->data->discussion->newInfo->lastVisit);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $this->discussion->result->data->discussion->newInfo->lastVisit)); //timezone correction check
        
        Assert::true(is_object($this->discussion->result->data->paging));
        Assert::type("int",$this->discussion->result->data->paging->currentPage);
        Assert::same(1, $this->discussion->result->data->paging->currentPage);
        Assert::type("int",$this->discussion->result->data->paging->numberOfPages);
        Assert::true($this->discussion->result->data->paging->numberOfPages > 0);
        
        Assert::type("array", $this->discussion->result->data->posts);
        Assert::same(20, count($this->discussion->result->data->posts)); // only 20 posts on each page
        
        foreach ($this->discussion->result->data->posts as $post) {
            Assert::type("int",$post->id);
            Assert::true($post->id > 0);
            Assert::type("int",$post->discussionId);
            Assert::same($discussionId, $post->discussionId);
            Assert::type("string",$post->post);
            Assert::type("int",$post->createdById);
            Assert::true($post->createdById > 0);
            Assert::type("string",$post->createdAt);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $post->createdAt)); //timezone correction check
            if(property_exists($post, "updatedAt")){
                Assert::type("string",$post->updatedAt);
                Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $post->updatedAt)); //timezone correction check
            }
            Assert::type("int",$post->updatedById);
            Assert::type("bool",$post->sticky);
            Assert::type("bool",$post->newPost);
            Assert::type("string",$post->createdAtStr);
            Assert::true(is_object($post->createdBy));
            if(property_exists($post->createdBy, "id")){ // when ID doesnt exists, the user of this post has been deleted
                Assert::type("int", $post->createdBy->id);
                Assert::type("string", $post->createdBy->login);
                Assert::type("string", $post->createdBy->callName);
                Assert::type("string", $post->createdBy->pictureUrl);
            }
            
        }
    }
    
    /* TAPI : POST */
    
    function testPostFailsNoRecId() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->discussion->recId(NULL)->insert("AUTOTEST automatic discussion post");} , "\Tymy\Exception\APIException", "Discussion ID not set!");
    }
    
    function testPost() {
        if(!$GLOBALS["testedTeam"]["invasive"])
            return null;
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $discussionId = $GLOBALS["testedTeam"]["testDiscussionId"];
        $insertText = "AUTOTEST automatic discussion post";
        $this->discussion->recId($discussionId)->insert($insertText);
        
        Assert::true(is_object($this->discussion));
        Assert::true(is_object($this->discussion->result));
        Assert::type("string",$this->discussion->result->status);
        Assert::same("OK",$this->discussion->result->status);

        Assert::true(is_object($this->discussion->result->data));

        Assert::type("int",$this->discussion->result->data->discussionId);
        Assert::same($discussionId,$this->discussion->result->data->discussionId);
        Assert::type("string",$this->discussion->result->data->post);
        Assert::same($insertText,$this->discussion->result->data->post);
        Assert::type("int",$this->discussion->result->data->createdById);
        Assert::same($this->login->id,$this->discussion->result->data->createdById);
        Assert::type("string",$this->discussion->result->data->createdAt); // no timezone check here, this is only feedback
        
        Assert::type("bool",$this->discussion->result->data->sticky);
        Assert::same(FALSE,$this->discussion->result->data->sticky);
        Assert::type("bool",$this->discussion->result->data->newPost);
        Assert::same(FALSE,$this->discussion->result->data->newPost);
        Assert::type("string",$this->discussion->result->data->createdAtStr);
        
        Assert::true(is_object($this->discussion->result->data->createdBy));
        Assert::type("int",$this->discussion->result->data->createdBy->id);
        Assert::same($this->login->id,$this->discussion->result->data->createdBy->id);
        Assert::type("string",$this->discussion->result->data->createdBy->login);
        Assert::type("string",$this->discussion->result->data->createdBy->callName);
        Assert::type("string",$this->discussion->result->data->createdBy->pictureUrl);
    }
    
    /* TAPI : SEARCH */
    
    function testSearch() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $discussionId = $GLOBALS["testedTeam"]["searchDiscussionId"];
        $searchHash = $GLOBALS["testedTeam"]["searchHash"];
        $this->discussion->dumpResult();
        $this->discussion->recId($discussionId)->search($searchHash)->getResult(TRUE);
        
        Assert::true(is_object($this->discussion));
        Assert::true(is_object($this->discussion->result));
        Assert::type("string",$this->discussion->result->status);
        Assert::same("OK",$this->discussion->result->status);
        Assert::true(is_object($this->discussion->result->data->discussion));//returned discussion object
        Assert::type("int",$this->discussion->result->data->discussion->id);
        Assert::same($discussionId,$this->discussion->result->data->discussion->id);
        
        Assert::type("array",$this->discussion->result->data->posts);
        Assert::same(1,count($this->discussion->result->data->posts)); // only one post with that hash
        
        Assert::true(is_object($this->discussion->result->data->posts[0]));
        
        Assert::type("int",$this->discussion->result->data->posts[0]->id);
        Assert::true($this->discussion->result->data->posts[0]->id >= 0);
        Assert::type("int",$this->discussion->result->data->posts[0]->discussionId);
        Assert::same($discussionId,$this->discussion->result->data->posts[0]->discussionId);
        Assert::type("string",$this->discussion->result->data->posts[0]->post);
        Assert::contains($searchHash, $this->discussion->result->data->posts[0]->post);
        Assert::type("int",$this->discussion->result->data->posts[0]->createdById);
        Assert::same($GLOBALS["testedTeam"]["searchedItemUserId"],$this->discussion->result->data->posts[0]->createdById);
        
        Assert::type("string",$this->discussion->result->data->posts[0]->createdAt);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $this->discussion->result->data->posts[0]->createdAt)); //timezone correction check
        Assert::type("int",$this->discussion->result->data->posts[0]->updatedById);
        Assert::same(0,$this->discussion->result->data->posts[0]->updatedById); // no one updated
        Assert::type("bool",$this->discussion->result->data->posts[0]->sticky);
        Assert::same(false,$this->discussion->result->data->posts[0]->sticky);
        Assert::type("bool",$this->discussion->result->data->posts[0]->newPost);
        Assert::same(false,$this->discussion->result->data->posts[0]->newPost);
        Assert::type("string",$this->discussion->result->data->posts[0]->createdAtStr);
        Assert::true(is_object($this->discussion->result->data->posts[0]->createdBy));
    }

}

$test = new APIDiscussionTest($container);
$test->run();
