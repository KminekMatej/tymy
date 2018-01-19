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

class HomepagePresenterTest extends IPresenterTest {

    const PRESENTERNAME = "Homepage";

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
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 1);
        
        Assert::true($dom->has('div.container.homepage'));
        Assert::true(count($dom->find('div.container.homepage div.row')) >= 2); // at least two rows, two makes the main layout, more rows are inside for discussions
        Assert::true($dom->has('div.container.homepage div.row div.col-md-5.my-3 div.card.sh-box#calendar'));
        Assert::true($dom->has('div.container.homepage div.row div.col-md-5.my-3 a.btn.btn-sm.btn-light.btn-light-bordered.my-1.d-block'));
        
        Assert::true($dom->has('div.container.homepage div.row div.col-md-7.my-3 div.card.sh-box div.card-header div.row div.col h4.card-title'));
        Assert::true($dom->has('div.container.homepage div.row div.col-md-7.my-3 div.card.sh-box div.card-header div.row div.col.col-md-auto button.btn.btn-sm.btn-light.btn-light-bordered i.fa.fa-refresh'));
        Assert::true($dom->has('div.container.homepage div.row div.col-md-7.my-3 div.card.sh-box div.card-body'));
        
    }
    
    function testAccessibleSettings(){
        $accessibleSettings = $this->presenter->getAccessibleSettings();
        Assert::type("array", $accessibleSettings);
        foreach ($accessibleSettings as $setting) {
            Assert::type("SettingMenu", $setting);
            Assert::truthy($setting->code);
            Assert::truthy($setting->name);
            Assert::truthy($setting->href);
            Assert::truthy($setting->icon);
        }
        
    }
}

$test = new HomepagePresenterTest($container);
$test->run();
