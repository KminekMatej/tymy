<?php

declare(strict_types=1);

use Nette\Application\Application;
use Tymy\Bootstrap;


// absolute filesystem path to the web root
define('WWW_DIR', dirname(__FILE__));

require __DIR__ . '/../app/Bootstrap.php';

Bootstrap::boot()
	->getByType(Application::class)
	->run();
