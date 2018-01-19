<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;
use Tester\TestCase;
use Environment;
use Tester\DomQuery;
use Tapi\AttendanceConfirmResource;
use Tapi\AttendancePlanResource;
use Tapi\AvatarUploadResource;
use Tapi\CachedResult;
use Tapi\DiscussionCreateResource;
use Tapi\DiscussionDeleteResource;
use Tapi\DiscussionDetailResource;
use Tapi\DiscussionEditResource;
use Tapi\DiscussionListResource;
use Tapi\DiscussionNewsListResource;
use Tapi\DiscussionPageResource;
use Tapi\DiscussionPostCreateResource;
use Tapi\DiscussionPostDeleteResource;
use Tapi\DiscussionPostEditResource;
use Tapi\EventCreateResource;
use Tapi\EventDeleteResource;
use Tapi\EventDetailResource;
use Tapi\EventEditResource;
use Tapi\EventListResource;
use Tapi\EventTypeListResource;
use Tapi\LoginResource;
use Tapi\LogoutResource;
use Tapi\OptionCreateResource;
use Tapi\OptionDeleteResource;
use Tapi\OptionEditResource;
use Tapi\OptionListResource;
use Tapi\PasswordLostResource;
use Tapi\PasswordResetResource;
use Tapi\PollCreateResource;
use Tapi\PollDeleteResource;
use Tapi\PollDetailResource;
use Tapi\PollEditResource;
use Tapi\PollListResource;
use Tapi\PollVoteResource;
use Tapi\RequestMethod;
use Tapi\ResultStatus;
use Tapi\TapiObject;
use Tapi\TapiRequestTimestamp;
use Tapi\TapiService;
use Tapi\TracyTapiPanel;
use Tapi\UserCreateResource;
use Tapi\UserDeleteResource;
use Tapi\UserDetailResource;
use Tapi\UserEditResource;
use Tapi\UserListResource;
use Tapi\UserRegisterResource;
use Tapi\UsersLiveResource;


$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class DiscussionPresenterTest extends IPresenterTest {

    const PRESENTERNAME = "Discussion";
    
    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    function testActionDefault(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container'));
        Assert::true($dom->has('ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 2); //last item aint link
        
        Assert::true($dom->has('div.container.discussions'));
        Assert::true(count($dom->find('div.container.discussions div.row')) >= 1);
    }
    
    
    function getDiscussionNames() {
        return $GLOBALS["testedTeam"]["testDiscussionName"];
    }

    /**
     *
     * @dataProvider getDiscussionNames
     */
    function testActionDiscussion($discussionName, $canWrite){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'discussion', 'discussion' => $discussionName));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $re = '/&(?!(?:apos|quot|[gl]t|amp);|#)/';

        $dom = NULL;
        $html = (string)$response->getSource();
        //replace unescaped ampersands in html to prevent tests from failing
        $html = preg_replace($re, "&amp;", $html);
        
        $dom = DomQuery::fromHtml($html);
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        
        //has breadcrumbs
        
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3); //last item aint link
        
        Assert::equal(count($dom->find('div.container.my-2 div.row.justify-content-md-center')), $canWrite ? 2 : 1);
        if($canWrite){
            Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 textarea#addPost'));
            Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 div.addPost form.form-inline input.form-control.mr-sm-2'));
            Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 div.addPost form.form-inline input.form-control.btn.btn-outline-success.mr-auto'));
            Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 div.addPost form.form-inline button.btn.btn-primary'));
        }
        
        Assert::true($dom->has('div.container.discussion#snippet--discussion'));
        Assert::true(count($dom->find('div.container.discussion#snippet--discussion div.row'))<=20);
    }
}

$test = new DiscussionPresenterTest($container);
$test->run();
