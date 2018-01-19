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

class PwdLostPresenterTest extends IPresenterTest {

    const PRESENTERNAME = "Sign";
    
    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function testPwdLostComponents(){
        $request = new Nette\Application\Request('Sign', 'GET', array('action' => 'pwdlost'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        Assert::type('Nette\Bridges\ApplicationLatte\Template', $response->getSource());

        $html = (string) $response->getSource();

        $dom = DomQuery::fromHtml($html);

        Assert::true($dom->has('input[name="email"]'));
        Assert::true($dom->has('input[name="send"]'));
        
        Assert::true($dom->has('a[href="/sign/in"]'));
        Assert::true($dom->has('a[href="/sign/pwdreset"]'));
    }
    
    function testPwdResetComponents(){
        $request = new Nette\Application\Request('Sign', 'GET', array('action' => 'pwdreset'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        Assert::type('Nette\Bridges\ApplicationLatte\Template', $response->getSource());

        $html = (string) $response->getSource();

        $dom = DomQuery::fromHtml($html);

        Assert::true($dom->has('input[name="code"]'));
        Assert::true($dom->has('input[name="send"]'));
        
        Assert::true($dom->has('a[href="/sign/in"]'));
    }
    
    function testPwdNewComponents(){
        $request = new Nette\Application\Request('Sign', 'GET', array('action' => 'pwdnew', 'pwd' => 'newpass123'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        Assert::type('Nette\Bridges\ApplicationLatte\Template', $response->getSource());

        $html = (string) $response->getSource();

        $dom = DomQuery::fromHtml($html);

        Assert::true($dom->has('input#pwd'));
        Assert::true($dom->has('a[href="/sign/in"]'));
    }
    
}

$test = new PwdLostPresenterTest($container);
$test->run();
