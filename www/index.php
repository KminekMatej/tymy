<?php

use Nette\Application\Application;
use Tymy\Bootstrap;

declare(strict_types=1);

// absolute filesystem path to the web root
define('WWW_DIR', dirname(__FILE__));

require __DIR__ . '/../app/Bootstrap.php';

Bootstrap::boot()
	->getByType(Application::class)
	->run();
