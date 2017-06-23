<?php
/**
 * TEST: Test Discussion on TYMY api
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

class APIDiscussionTest extends Tester\TestCase {

    private $container;
    private $login;
    private $loginObj;
    private $authenticator;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        parent::setUp();
        $this->authenticator = new \App\Model\TestAuthenticator();
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function login(){
        $this->loginObj = new \Tymy\Login();
        $this->login = $this->loginObj->team($GLOBALS["testedTeam"]["team"])
                ->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword($GLOBALS["testedTeam"]["pass"])
                ->fetch();
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchFailsNoRecId(){
        $discussionObj = new \Tymy\Discussion(NULL, NULL, TRUE, 1);
        $discussion = $discussionObj
                ->team($GLOBALS["testedTeam"]["team"])
                ->fetch();
    }

    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchFailsPageDoNotExist(){
        $discussionObj = new \Tymy\Discussion(NULL, NULL, TRUE, -1);
        $discussion = $discussionObj
                ->team($GLOBALS["testedTeam"]["team"])
                ->recId(1)
                ->fetch();
    }
    
    /**
     * @throws Tymy\Exception\APIException
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


        $discussionObj = new \Tymy\Discussion(NULL, NULL, TRUE, 1);
        $discussionObj
                ->setPresenter($mockPresenter)
                ->recId(1)
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


        $discussionObj = new \Tymy\Discussion(NULL, NULL, TRUE, 1);
        $discussionObj
                ->setPresenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }
    
    function testFetchSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => $GLOBALS["testedTeam"]["team"], "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $discussionId = 1;
        $discussionObj = new \Tymy\Discussion($mockPresenter->tapiAuthenticator, $mockPresenter, TRUE, 1);
        $discussionObj->recId($discussionId)
                ->fetch();
        
        Assert::true(is_object($discussionObj));
        Assert::true(is_object($discussionObj->result));
        Assert::type("string",$discussionObj->result->status);
        Assert::same("OK",$discussionObj->result->status);
        Assert::true(is_object($discussionObj->result->data->discussion));//returned discussion object
        
        Assert::type("int",$discussionObj->result->data->discussion->id);
        Assert::same($discussionId, $discussionObj->result->data->discussion->id);
        Assert::type("string",$discussionObj->result->data->discussion->caption);
        Assert::type("string",$discussionObj->result->data->discussion->description);
        Assert::type("string",$discussionObj->result->data->discussion->readRightName);
        Assert::type("string",$discussionObj->result->data->discussion->writeRightName);
        Assert::type("string",$discussionObj->result->data->discussion->deleteRightName);
        Assert::type("string",$discussionObj->result->data->discussion->stickyRightName);
        Assert::type("bool",$discussionObj->result->data->discussion->publicRead);
        Assert::same(FALSE, $discussionObj->result->data->discussion->publicRead);
        Assert::type("string",$discussionObj->result->data->discussion->status);
        Assert::same("ACTIVE", $discussionObj->result->data->discussion->status);
        Assert::type("bool",$discussionObj->result->data->discussion->editablePosts);
        Assert::type("int",$discussionObj->result->data->discussion->order);
        Assert::type("bool",$discussionObj->result->data->discussion->canRead);
        Assert::type("bool",$discussionObj->result->data->discussion->canWrite);
        Assert::type("bool",$discussionObj->result->data->discussion->canDelete);
        Assert::type("bool",$discussionObj->result->data->discussion->canStick);
        Assert::type("int",$discussionObj->result->data->discussion->newPosts);
        Assert::true($discussionObj->result->data->discussion->newPosts >= 0);
        Assert::type("int",$discussionObj->result->data->discussion->numberOfPosts);
        Assert::true($discussionObj->result->data->discussion->numberOfPosts >= 0);
        Assert::true(is_object($discussionObj->result->data->discussion->newInfo));
        Assert::type("int",$discussionObj->result->data->discussion->newInfo->discussionId);
        Assert::same($discussionId, $discussionObj->result->data->discussion->newInfo->discussionId);
        Assert::type("int",$discussionObj->result->data->discussion->newInfo->newsCount);
        Assert::same($discussionObj->result->data->discussion->newPosts, $discussionObj->result->data->discussion->newInfo->newsCount);
        Assert::type("string",$discussionObj->result->data->discussion->newInfo->lastVisit);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $discussionObj->result->data->discussion->newInfo->lastVisit)); //timezone correction check
        
        Assert::true(is_object($discussionObj->result->data->paging));
        Assert::type("int",$discussionObj->result->data->paging->currentPage);
        Assert::same(1, $discussionObj->result->data->paging->currentPage);
        Assert::type("int",$discussionObj->result->data->paging->numberOfPages);
        Assert::true($discussionObj->result->data->paging->numberOfPages > 0);
        
        Assert::type("array", $discussionObj->result->data->posts);
        Assert::same(20, count($discussionObj->result->data->posts)); // only 20 posts on each page
        
        foreach ($discussionObj->result->data->posts as $post) {
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
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testPostFailsNoRecId() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => $GLOBALS["testedTeam"]["team"], "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $insertText = "AUTOTEST automatic discussion post";
        
        $discussionObj = new \Tymy\Discussion($mockPresenter->tapiAuthenticator, $mockPresenter, TRUE, 1);
        $discussionObj
                ->insert($insertText);
        Assert::true(is_object($discussionObj));
        Assert::true(is_object($discussionObj->result));
        Assert::type("string",$discussionObj->result->status);
        Assert::same("OK",$discussionObj->result->status);
        
        Assert::true(FALSE);
    }
    
    function testPost() {
        if(!$GLOBALS["testedTeam"]["invasive"])
            return null;
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => $GLOBALS["testedTeam"]["team"], "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $insertText = "AUTOTEST automatic discussion post";
        $discussionId = 2; //ID of Testovaci Diskuze
        $discussionObj = new \Tymy\Discussion($mockPresenter->tapiAuthenticator, $mockPresenter, TRUE, 1);
        $discussionObj
                ->recId($discussionId)
                ->insert($insertText);
        
        Assert::true(is_object($discussionObj));
        Assert::true(is_object($discussionObj->result));
        Assert::type("string",$discussionObj->result->status);
        Assert::same("OK",$discussionObj->result->status);

        Assert::true(is_object($discussionObj->result->data));

        Assert::type("int",$discussionObj->result->data->discussionId);
        Assert::same($discussionId,$discussionObj->result->data->discussionId);
        Assert::type("string",$discussionObj->result->data->post);
        Assert::same($insertText,$discussionObj->result->data->post);
        Assert::type("int",$discussionObj->result->data->createdById);
        Assert::same($this->login->id,$discussionObj->result->data->createdById);
        Assert::type("string",$discussionObj->result->data->createdAt); // no timezone check here, this is only feedback
        
        Assert::type("bool",$discussionObj->result->data->sticky);
        Assert::same(FALSE,$discussionObj->result->data->sticky);
        Assert::type("bool",$discussionObj->result->data->newPost);
        Assert::same(FALSE,$discussionObj->result->data->newPost);
        Assert::type("string",$discussionObj->result->data->createdAtStr);
        
        Assert::true(is_object($discussionObj->result->data->createdBy));
        Assert::type("int",$discussionObj->result->data->createdBy->id);
        Assert::same($this->login->id,$discussionObj->result->data->createdBy->id);
        Assert::type("string",$discussionObj->result->data->createdBy->login);
        Assert::type("string",$discussionObj->result->data->createdBy->callName);
        Assert::type("string",$discussionObj->result->data->createdBy->pictureUrl);
    }
    
    function testSearch() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => $GLOBALS["testedTeam"]["team"], "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $discussionId = $GLOBALS["testedTeam"]["searchDiscussionId"];
        $searchHash = $GLOBALS["testedTeam"]["searchHash"];
        $discussionObj = new \Tymy\Discussion($mockPresenter->tapiAuthenticator, $mockPresenter, TRUE, 1);
        $discussionObj
                ->recId($discussionId)
                ->search($searchHash)
                ->fetch();
        
        Assert::true(is_object($discussionObj));
        Assert::true(is_object($discussionObj->result));
        Assert::type("string",$discussionObj->result->status);
        Assert::same("OK",$discussionObj->result->status);
        Assert::true(is_object($discussionObj->result->data->discussion));//returned discussion object
        Assert::type("int",$discussionObj->result->data->discussion->id);
        Assert::same($discussionId,$discussionObj->result->data->discussion->id);
        
        Assert::type("array",$discussionObj->result->data->posts);
        Assert::same(1,count($discussionObj->result->data->posts)); // only one post with that hash
        
        Assert::true(is_object($discussionObj->result->data->posts[0]));
        
        Assert::type("int",$discussionObj->result->data->posts[0]->id);
        Assert::true($discussionObj->result->data->posts[0]->id >= 0);
        Assert::type("int",$discussionObj->result->data->posts[0]->discussionId);
        Assert::same($discussionId,$discussionObj->result->data->posts[0]->discussionId);
        Assert::type("string",$discussionObj->result->data->posts[0]->post);
        Assert::contains($searchHash, $discussionObj->result->data->posts[0]->post);
        Assert::type("int",$discussionObj->result->data->posts[0]->createdById);
        Assert::same($GLOBALS["testedTeam"]["searchedItemUserId"],$discussionObj->result->data->posts[0]->createdById);
        
        Assert::type("string",$discussionObj->result->data->posts[0]->createdAt);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $discussionObj->result->data->posts[0]->createdAt)); //timezone correction check
        Assert::type("int",$discussionObj->result->data->posts[0]->updatedById);
        Assert::same(0,$discussionObj->result->data->posts[0]->updatedById); // no one updated
        Assert::type("bool",$discussionObj->result->data->posts[0]->sticky);
        Assert::same(false,$discussionObj->result->data->posts[0]->sticky);
        Assert::type("bool",$discussionObj->result->data->posts[0]->newPost);
        Assert::same(false,$discussionObj->result->data->posts[0]->newPost);
        Assert::type("string",$discussionObj->result->data->posts[0]->createdAtStr);
        Assert::true(is_object($discussionObj->result->data->posts[0]->createdBy));
    }

}

$test = new APIDiscussionTest($container);
$test->run();
