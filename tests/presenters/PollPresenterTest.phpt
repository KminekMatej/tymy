<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;
use Tester\TestCase;
use Tester\Environment;
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

class PollPresenterTest extends IPresenterTest {

    const PRESENTERNAME = "Poll";
    
    /** @var PollListResource */
    private $pollList;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
        $this->pollList = $this->container->getByType("Tapi\PollListResource");
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
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 2); //last item aint link
        
    }
    
    
    function allWebNames(){
        $polls = $this->pollList->getData();
        $inputArray = [];
        foreach ($polls as $p) {
            $inputArray[] = [$p];
        }
        return $inputArray;
    }
    
    /**
     * @dataProvider allWebNames
     */
    function testPoll($obj){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'poll', "anketa" => $obj->webName));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3); // last breadcrumb aint link
        
        //check shown items
        Assert::equal(count($dom->find('div.container.poll div.row div.col.my-3 div.card.sh-box div.card-body h4.card-title')), 1);
        Assert::equal(count($dom->find('div.container.poll div.row div.col.my-3 div.card.sh-box div.card-body h6.card-subtitle')), 1);
        if($obj->canSeeResults){
            Assert::true(count($dom->find('div.container.poll div.row div.col.my-3 div.card.sh-box div.card-body div.container div.row div.col-3.py-3.option')) > 0);
            Assert::true($dom->has('div.container.poll div.row#snippet--poll-results div.col.my-3 div.card.sh-box.text-white.bg-dark div.card-header'));
            Assert::true(count($dom->find('div.container.poll div.row#snippet--poll-results div.col.my-3 div.card.sh-box.text-white.bg-dark div.card-body.p-0 table.table.table-inverse.table-striped.table-hover.mb-0 tr[data-vote]')) > 0);
        }
    }
}

$test = new PollPresenterTest($container);
$test->run();
