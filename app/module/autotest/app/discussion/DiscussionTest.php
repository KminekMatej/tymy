<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Discussion;

use Tymy\Bootstrap;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Autotest\Entity\Assert;
use Tymy\Module\Autotest\RequestCase;
use Tymy\Module\Autotest\SimpleResponse;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

/**
 * Description of DiscussionTest
 *
 * @author kminekmatej, 01.10.2020 22:00:34
 *
 */
class DiscussionTest extends RequestCase
{
    public function testGet(): void
    {
        $data = null;
        $listResponse = $this->getList();
    }

    public function testNewOnly(): void
    {
        $newOnlyResponse = $this->request($this->getBasePath() . "/newOnly")->expect(200, "array");
        $data = $newOnlyResponse->getData();

        foreach ($data as $newInfo) {
            Assert::hasKey("id", $newInfo);
            Assert::hasKey("newPosts", $newInfo);
        }
    }

    public function testCRUDNotPermittedDiscussion(): void
    {
        $dId = $this->recordManager->createDiscussion(null, [
            "readRightName" => "ADMINONLY",
            "writeRightName" => "ADMINONLY",
            "deleteRightName" => "ADMINONLY",
            "stickyRightName" => "MEMBERONLY",
        ]);

        $pid = $this->request($this->getBasePath() . "/$dId/post", "POST", ["post" => "ADMIN first post " . random_int(0, 10000)])->expect(201)->getData()["id"];

        $this->authorizeUser();
        //cannot create discussion
        $this->request($this->getBasePath(), "POST", $this->recordManager->mockDiscussion())->expect(403);

        //cannot edit existing disucssion
        $this->request($this->getBasePath() . "/$dId", "PUT", $this->mockChanges())->expect(403);

        //cannot delete existing disucssion
        $this->request($this->getBasePath() . "/$dId", "DELETE")->expect(403);

        //cannot write post to existing disucssion
        $this->request($this->getBasePath() . "/$dId/post", "POST", ["post" => "Autotest post " . random_int(0, 10000)])->expect(403);

        //cannot read post in existing disucssion
        $this->request($this->getBasePath() . "/$dId/post", "GET")->expect(403);
        $this->request($this->getBasePath() . "/$dId/post/$pid", "GET")->expect(403);

        //check that autotest_admin cannot stick but main admin can
        $this->request($this->getBasePath() . "/$dId/post/$pid", "PUT", ["sticky" => true])->expect(403);
        $this->authorizeAdmin($this->config["user_member_login"], $this->config["user_member_pwd"]);
        $this->request($this->getBasePath() . "/$dId/post/$pid", "PUT", ["sticky" => true])->expect(200, "array");
        $updatedResponse = $this->request($this->getBasePath() . "/$dId/post/$pid")->expect(200, "array");
        Assert::truthy($updatedResponse->getData()["updatedAtStr"]);

        //get as html mode
        $this->request($this->getBasePath() . "/$dId/html")->expect(200);
        $this->request($this->getBasePath() . "/$dId/html/1")->expect(200, "array");
        //get as bb mode
        $this->request($this->getBasePath() . "/$dId/bb")->expect(200);
        $this->request($this->getBasePath() . "/$dId/bb/1")->expect(200, "array");

        //get with jump2date
        $now = new DateTime();
        $prevYear = $now->modifyClone("- 1 year");
        $nextYear = $now->modifyClone("+ 1 year");

        $this->request($this->getBasePath() . "/$dId/bb?jump2date=" . $now->format(BaseModel::DATE_ENG_FORMAT))->expect(200, "array");
        $this->request($this->getBasePath() . "/$dId/bb?jump2date=" . $prevYear->format(BaseModel::DATE_ENG_FORMAT))->expect(200, "array");
        $this->request($this->getBasePath() . "/$dId/bb?jump2date=" . $nextYear->format(BaseModel::DATE_ENG_FORMAT))->expect(200, "array");

        $this->request($this->getBasePath() . "/$dId/bb?search=Autotest&suser=2")->expect(200, "array");
    }

    public function testPostDiscussion(): void
    {
        $this->authorizeAdmin();
        $dId = $this->recordManager->createDiscussion(null, [
            "writeRightName" => "ADMINONLY",
            "deleteRightName" => "ADMINONLY",
            "stickyRightName" => "MEMBERONLY",
        ]);

        $pid = $this->request($this->getBasePath() . "/$dId/post", "POST", ["post" => "ADMIN first post " . random_int(0, 10000)])->expect(201)->getData()["id"];

        $this->authorizeUser();

        //cannot write new post to read-only disucssion
        $this->request($this->getBasePath() . "/$dId/post", "POST", ["post" => "Autotest post " . random_int(0, 10000)])->expect(403);
        //cannot edit post in read-only
        $this->request($this->getBasePath() . "/$dId/post/$pid", "PUT", ["post" => "Autotest post " . random_int(0, 10000)])->expect(403);
        //cannot delete post in read-only
        $this->request($this->getBasePath() . "/$dId/post/$pid", "DELETE")->expect(403);

        //can read post in existing disucssion
        $this->request($this->getBasePath() . "/$dId/post")->expect(200, "array");
        $this->request($this->getBasePath() . "/$dId/post/$pid")->expect(200, "array");

        //unknown post returns 404
        $this->request($this->getBasePath() . "/$dId/post/99999")->expect(404);
        $this->request($this->getBasePath() . "/$dId/post/99999", "PUT", ["post" => "Autotest post " . random_int(0, 10000)])->expect(404);
        $this->request($this->getBasePath() . "/$dId/post/99999", "DELETE")->expect(404);

        //post from other discussion returns 404
        $this->request($this->getBasePath() . "/$dId/post/1")->expect(404);
        $this->request($this->getBasePath() . "/$dId/post/1", "PUT", ["post" => "Autotest post " . random_int(0, 10000)])->expect(404);

        //get as html mode
        $this->request($this->getBasePath() . "/$dId/html")->expect(200);
        $this->request($this->getBasePath() . "/$dId/html/1")->expect(200, "array");
        //get as bb mode
        $this->request($this->getBasePath() . "/$dId/bb")->expect(200);
        $this->request($this->getBasePath() . "/$dId/bb/1")->expect(200, "array");

        $this->authorizeAdmin();
        $this->request($this->getBasePath() . "/$dId/post/$pid", "DELETE")->expect(200);
    }

    public function testBbCodes(): void
    {
        $this->bbTest("[b]Tucnice[/b]", "<strong>Tucnice</strong>");
        $this->bbTest("[i]Kurziva[/i]", "<em>Kurziva</em>");
        $this->bbTest("[u]Podtrh[/u]", "<u>Podtrh</u>");
        $this->bbTest("[s]Skrt[/s]", "<s>Skrt</s>");
        $this->bbTest("[quote]Lorem ipsum[/quote]", "<blockquote>Lorem ipsum</blockquote>");
        $this->bbTest("[code]Lorem ipsum[/code]", "<pre>Lorem ipsum</pre>");
        $this->bbTest("[pre]Lorem ipsum[/pre]", "<pre>Lorem ipsum</pre>");
        $this->bbTest("[size=14px]Lorem ipsum[/size]", '<span style="font-size:14px;">Lorem ipsum</span>');
        $this->bbTest("[size=8pt]Lorem ipsum[/size]", '<span style="font-size:8pt;">Lorem ipsum</span>');
        $this->bbTest("[size=12%]Lorem ipsum[/size]", '<span style="font-size:12%;">Lorem ipsum</span>');
        $this->bbTest("[color=#323232]Lorem ipsum[/color]", '<span style="color:#323232;">Lorem ipsum</span>');

        $this->bbTest("[url]http://www.seznam.cz[/url]", '<a href="http://www.seznam.cz" rel="nofollow">http://www.seznam.cz</a>');
        $this->bbTest("[url=http://www.seznam.cz][/url]", '<a href="http://www.seznam.cz" rel="nofollow">http://www.seznam.cz</a>');
        $this->bbTest("[url=http://www.seznam.cz]Link[/url]", '<a href="http://www.seznam.cz" rel="nofollow">Link</a>');
        $this->bbTest("LINK:http://www.seznam.cz", '<a href="http://www.seznam.cz" rel="nofollow">http://www.seznam.cz</a>');
        $this->bbTest("LINK:http://www.seznam.cz|Seznam.cz", '<a href="http://www.seznam.cz" rel="nofollow">Seznam.cz</a>');

        $this->bbTest("[url]mailto:admin@tymy.cz[/url]", '<a href="mailto:admin@tymy.cz" rel="nofollow">admin@tymy.cz</a>');
        $this->bbTest("[url=mailto:admin@tymy.cz][/url]", '<a href="mailto:admin@tymy.cz" rel="nofollow">admin@tymy.cz</a>');
        $this->bbTest("[url=mailto:admin@tymy.cz]Admin[/url]", '<a href="mailto:admin@tymy.cz" rel="nofollow">Admin</a>');
        $this->bbTest("[email]admin@tymy.cz[/email]", '<a href="mailto:admin@tymy.cz" rel="nofollow">admin@tymy.cz</a>');
        $this->bbTest("MAIL:admin@tymy.cz", '<a href="mailto:admin@tymy.cz" rel="nofollow">admin@tymy.cz</a>');

        $this->bbTest("[img]https://www.tymy.cz/images/logo/tymy_logo_white_250.png[/img]", '<img src="https://www.tymy.cz/images/logo/tymy_logo_white_250.png" alt="" />');
        $this->bbTest("IMG:https://www.tymy.cz/images/logo/tymy_logo_white_250.png", '<img src="https://www.tymy.cz/images/logo/tymy_logo_white_250.png" alt="" />');

        $this->bbTest("[hr]", '<hr />');
        $this->bbTest("[left]Lorem Ipsum[/left]", '<div style="text-align: left;">Lorem Ipsum</div>');
        $this->bbTest("[center]Lorem Ipsum[/center]", '<div style="text-align: center;">Lorem Ipsum</div>');
        $this->bbTest("[right]Lorem Ipsum[/right]", '<div style="text-align: right;">Lorem Ipsum</div>');

        $this->bbTest("[h1]Lorem Ipsum[/h1]", '<h1>Lorem Ipsum</h1>');
        $this->bbTest("[h2]Lorem Ipsum[/h2]", '<h2>Lorem Ipsum</h2>');
        $this->bbTest("[h3]Lorem Ipsum[/h3]", '<h3>Lorem Ipsum</h3>');
        $this->bbTest("[h4]Lorem Ipsum[/h4]", '<h4>Lorem Ipsum</h4>');
        $this->bbTest("[h5]Lorem Ipsum[/h5]", '<h5>Lorem Ipsum</h5>');
        $this->bbTest("[h6]Lorem Ipsum[/h6]", '<h6>Lorem Ipsum</h6>');
        $this->bbTest("[table][tr][th]Co?[/th][th]Kam?[/th][th]Proč?[/th][/tr][tr][td]Nic![/td][td]Nikam![/td][td]Proto![/td][/tr][/table]", '<table border="1" cellpadding="3" cellspacing="1"><tr><th>Co?</th><th>Kam?</th><th>Proč?</th></tr><tr><td>Nic!</td><td>Nikam!</td><td>Proto!</td></tr></table>');

        $this->bbTest("[list=i][*]Lorem Ipsum\n[*]Lorem Ipsum 2\n[/list]", '<ol type="i"><li>Lorem Ipsum</li><br /><li>Lorem Ipsum 2</li><br /></ol>');
        $this->bbTest("[list][*]Lorem Ipsum\n[*]Lorem Ipsum 2\n[/list]", '<ul><li>Lorem Ipsum</li><br /><li>Lorem Ipsum 2</li><br /></ul>');

        //multiple testing
        $this->bbTest("[h1]Nadpis[/h1][code]Lorem Ipsum 2[/code][right][b]Tucny odkaz https://www.seznam.cz[/b][/right][center][size=14pt]Centered [color=green]text[/color][/size][/center]", '<h1>Nadpis</h1><pre>Lorem Ipsum 2</pre><div style="text-align: right;"><strong>Tucny odkaz <a href="https://www.seznam.cz" rel="nofollow">https://www.seznam.cz</a></strong></div><div style="text-align: center;"><span style="font-size:14pt;">Centered <span style="color:green;">text</span></span></div>');

        //should not pass
        $this->bbTest("<script type='text/javascript'>alert('Uu');</script>", "alert('Uu');");
        $this->bbTest("[script type='text/javascript']alert('Uu');[/script]", "[script type='text/javascript']alert('Uu');[/script]");
    }

    private function bbTest(string $bb, string $html): void
    {
        $out = $this->request($this->getBasePath() . "/preview", "POST", ["post" => $bb])->expect(200, "string")->getData();
        Assert::equal($html, $out);
    }

    public function testSticky(): void
    {
        $this->authorizeAdmin();
        $dId = $this->recordManager->createDiscussion(null, [
            "deleteRightName" => "ADMINONLY",
            "stickyRightName" => "MEMBERONLY",
        ]);

        $this->request($this->getBasePath() . "/$dId/post", "POST", ["post" => "ADMIN first post " . random_int(0, 10000)])->expect(201)->getData();

        $this->authorizeUser();

        $pid2 = $this->request($this->getBasePath() . "/$dId/post", "POST", ["post" => "USER first post " . random_int(0, 10000)])->expect(201)->getData()["id"];

        //check that user can update, but cannot stick
        $this->request($this->getBasePath() . "/$dId/post/$pid2", "PUT", ["sticky" => true])->expect(403);
    }

    /**
     * Load data list
     */
    private function getList(): \Tymy\Module\Autotest\SimpleResponse
    {
        $this->authorizeAdmin();
        return $this->request($this->getBasePath())->expect(200, "array");
    }

    public function testCRUD(): void
    {
        $this->authorizeAdmin();
        $recordId = $this->createRecord();

        $this->request($this->getBasePath() . "/" . $recordId)->expect(200, "array");

        $this->change($recordId);

        $this->deleteRecord($recordId);
    }

    public function createRecord(): int
    {
        return $this->recordManager->createDiscussion();
    }

    protected function getBasePath(): string
    {
        return Discussion::MODULE;
    }

    public function getModule(): string
    {
        return Discussion::MODULE;
    }

    /**
     * @return array<string, string>
     */
    protected function mockChanges(): array
    {
        return [
            "caption" => "Changed discussion caption",
            "description" => "Super another description",
        ];
    }

    /**
     * @return string[]|bool[]
     */
    public function mockRecord(): array
    {
        return $this->recordManager->mockDiscussion();
    }
}

(new DiscussionTest($container))->run();
