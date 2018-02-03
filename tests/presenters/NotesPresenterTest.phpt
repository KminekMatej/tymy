<?php

namespace Test;

use Nette;
use Tester\Assert;
use Environment;
use Tester\DomQuery;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class NotesPresenterTest extends IPresenterTest {

    const PRESENTERNAME = "Notes";

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
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 2); //last item aint link
        
        Assert::true($dom->has('div.container.notes'));
        Assert::true($dom->has('div.container.notes div.row.justify-content-md-center div.col-8.my-3 div.card.sh-box div.card-body'));
        
    }
    
    function testActionNote(){
        $noteId = $GLOBALS["testedTeam"]["testNoteId"];
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'note', 'poznamka' => $noteId));
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
        
        Assert::true($dom->has('div.container.note div.row div.col.my-3 div.card.sh-box div.card-header h4.card-title'));
        Assert::true($dom->has('div.container.note div.row div.col.my-3 div.card.sh-box div.card-header h6.card-subtitle.mb-2.text-muted'));
        
        Assert::true($dom->has('div.container.note div.row div.col.my-3 div.card.sh-box div.card-body div.card-text'));
    }
}

$test = new NotesPresenterTest($container);
$test->run();
