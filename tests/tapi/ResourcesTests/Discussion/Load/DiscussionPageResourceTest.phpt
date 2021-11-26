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

class DiscussionPageResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tymy\Module\Core\Model\RequestMethod::GET;
    }

    public function setCorrectInputParams() {
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testDiscussionId"]);
    }
    
    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Discussion ID is missing");
    }

    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        Assert::true(is_object($data->discussion));//returned discussion object
        
        Assert::type("int",$data->discussion->id);
        Assert::type("string",$data->discussion->caption);
        Assert::type("string",$data->discussion->description);
        Assert::type("string",$data->discussion->readRightName);
        Assert::type("string",$data->discussion->writeRightName);
        Assert::type("string",$data->discussion->deleteRightName);
        Assert::type("string",$data->discussion->stickyRightName);
        Assert::type("bool",$data->discussion->publicRead);
        Assert::type("string",$data->discussion->status);
        Assert::same("ACTIVE", $data->discussion->status);
        Assert::type("bool",$data->discussion->editablePosts);
        Assert::type("int",$data->discussion->order);
        Assert::type("bool",$data->discussion->canRead);
        Assert::type("bool",$data->discussion->canWrite);
        Assert::type("bool",$data->discussion->canDelete);
        Assert::type("bool",$data->discussion->canStick);
        Assert::type("int",$data->discussion->newPosts);
        Assert::true($data->discussion->newPosts >= 0);
        Assert::type("int",$data->discussion->numberOfPosts);
        Assert::true($data->discussion->numberOfPosts >= 0);
        Assert::true(is_object($data->discussion->newInfo));
        Assert::type("int",$data->discussion->newInfo->discussionId);
        Assert::type("int",$data->discussion->newInfo->newsCount);
        Assert::same($data->discussion->newPosts, $data->discussion->newInfo->newsCount);
        Assert::type("string",$data->discussion->newInfo->lastVisit);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $data->discussion->newInfo->lastVisit)); //timezone correction check
        
        Assert::true(is_object($data->paging));
        Assert::type("int",$data->paging->currentPage);
        Assert::same(1, $data->paging->currentPage);
        Assert::type("int",$data->paging->numberOfPages);
        Assert::true($data->paging->numberOfPages > 0);
        
        Assert::type("array", $data->posts);
        Assert::same(20, count($data->posts)); // only 20 posts on each page
        
        foreach ($data->posts as $post) {
            Assert::type("int",$post->id);
            Assert::true($post->id > 0);
            Assert::type("int",$post->discussionId);
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

    public function testSearch(){
        $this->authenticateTapi($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $discussionId = $GLOBALS["testedTeam"]["searchDiscussionId"];
        $searchHash = $GLOBALS["testedTeam"]["searchHash"];
        
        $data = $this->tapiObject->init()->setId($discussionId)->setSearch($searchHash)->getData(TRUE);
        
        Assert::type("array", $data->posts);
        Assert::count(1, $data->posts);
    }
}

$test = new DiscussionPageResourceTest($container);
$test->run();
