<?php

namespace Tymy\Module\Autotest\Presenter\Front;

use Nette\Http\UrlScript;
use Nette\Utils\DateTime;
use SimpleXMLElement;
use Swoole\MySQL\Exception;
use Tracy\Debugger;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\Manager\TestsManager;
use Tymy\Module\Core\Presenter\Api\BasePresenter;

use const ROOT_DIR;

class DefaultPresenter extends BasePresenter
{
    public const PHP_CMD_PARAM = "php_cmd";

    private array $log;

    /** @inject */
    public TestsManager $testsManager;

    public function startup()
    {
        parent::startup();
        if (Debugger::$productionMode) {
            $this->respondForbidden();
        }

        define('TEST_DIR', Bootstrap::normalizePath(Bootstrap::MODULES_DIR . "/autotest"));
    }

    public function renderDefault($resourceId = null)
    {
        $this->mockAutotestServer($this->getHttpRequest()->getUrl());
        $output = $resourceId ? $this->runTests($resourceId) : null;
        $this->template->results = null;
        if (!empty($output)) {
            $this->processTestsOutput($output);
        }

        $cols = 5;

        $allModules = $this->testsManager->getAllFolders();
        $this->template->modules = $allModules;
        $this->template->testsOutput = $output ? $output['console'] : null;
        $this->template->cols = $cols;
        $this->template->rows = ceil(count($allModules) / $cols);
        $this->template->requests = file_exists(TEAM_DIR . "/log_autotest/requests.log") ? file(TEAM_DIR . "/log_autotest/requests.log") : [];
    }

    private function processTestsOutput($output)
    {
        $results = [];

        $xml = new SimpleXMLElement($output['junit']);
        $hasFailures = false;
        foreach ($xml->testsuite->testcase as $case) {
            $nameParts = explode("/", $case->attributes()->classname);
            $cnt = count($nameParts);
            $dir = $nameParts[$cnt - 2];
            if (isset($results[$dir]) && $results[$dir] == "fail") {
                continue;   //if there is already fail on current dir, just simply continue
            }

            if (isset($case->failure)) {
                $results[$dir] = "fail";
                $hasFailures = true;
            } elseif (isset($case->skipped)) {
                $results[$dir] = "skip";
            } else {
                $results[$dir] = "success";
            }
        }

        $results["all"] = $hasFailures ? "fail" : "success";

        $this->template->results = $results;

        $attrs = $xml->testsuite->attributes();
        $this->template->attributes = [
            "errors" => (int) ((array) $attrs->errors)[0],
            "skipped" => (int) ((array) $attrs->skipped)[0],
            "tests" => (int) ((array) $attrs->tests)[0],
            "time" => (float) ((array) $attrs->time)[0],
            "timestamp" => new DateTime(((array) $attrs->timestamp)[0]),
        ];
    }

    public function runTests($folder = "")
    {
        $requestLogFile = ROOT_DIR . '/log/requests.log';
        if (file_exists($requestLogFile)) {
            unlink($requestLogFile);
        }

        try {
            $output = $this->testsManager->runTests($folder);
        } catch (\Exception $exc) {
            $this->handleException($exc);
        }

        return ["console" => file_get_contents(TestsManager::OUT_CONSOLE), "junit" => file_get_contents(TestsManager::OUT_JUNIT)];
    }

    /**
     * Get team name from url and save it to environment variable to be able to use it in bootstrap later (which doesnt have HTTP_HOST)
     * @param UrlScript $url
     */
    private function mockAutotestServer(UrlScript $url)
    {
        $this->template->urlroot = "{$url->scheme}://{$url->host}{$url->basePath}autotest";
        $team = substr($url->host, 0, strpos($url->host, "."));
        putenv("team=$team");
    }
}
