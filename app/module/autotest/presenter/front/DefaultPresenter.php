<?php

namespace Tymy\Module\Autotest\Presenter\Front;

use Exception;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Http\UrlScript;
use Nette\Utils\DateTime;
use SimpleXMLElement;
use Tracy\Debugger;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\Manager\TestsManager;
use Tymy\Module\Core\Presenter\Api\BasePresenter;
use const TEAM_DIR;

class DefaultPresenter extends BasePresenter
{
    public const PHP_CMD_PARAM = "php_cmd";

    /** @inject */
    public TestsManager $testsManager;

    public function startup(): void
    {
        parent::startup();
        if (Debugger::$productionMode) {
            $this->respondForbidden();
        }

        define('TEST_DIR', Bootstrap::normalizePath(Bootstrap::MODULES_DIR . "/autotest"));
    }

    protected function beforeRender(): void
    {
        assert($this->template instanceof Template);
        $this->template->addFilter('colorize', fn($text): ?string => preg_replace([
            '/\[green\]/',
            '/\[red\]/',
            '/\[\/green\]/',
            '/\[\/red\]/',
            ], [
            "<strong style='color:green'>",
            "<strong style='color:red'>",
            "</strong>",
            "</strong>",
            ], htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
    }

    public function renderDefault($resourceId = null): void
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

    private function processTestsOutput($output): void
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

            if (property_exists($case, 'failure') && $case->failure !== null) {
                $results[$dir] = "fail";
                $hasFailures = true;
            } elseif (property_exists($case, 'skipped') && $case->skipped !== null) {
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

    /**
     * @return string[]|bool[]
     */
    public function runTests($folder = ""): array
    {
        $requestLogFile = TEAM_DIR . "/log_autotest/requests.log";
        if (file_exists($requestLogFile)) {
            unlink($requestLogFile);
        }

        try {
            $this->testsManager->runTests($folder);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        return ["console" => file_get_contents(TestsManager::OUT_CONSOLE), "junit" => file_get_contents(TestsManager::OUT_JUNIT)];
    }

    /**
     * Get team name from url and save it to environment variable to be able to use it in bootstrap later (which doesnt have HTTP_HOST)
     */
    private function mockAutotestServer(UrlScript $url): void
    {
        $this->template->urlroot = "{$url->getScheme()}://{$url->getHost()}{$url->getBasePath()}autotest";
        $team = substr($url->getHost(), 0, strpos($url->getHost(), "."));
        putenv("team=$team");
    }
}
