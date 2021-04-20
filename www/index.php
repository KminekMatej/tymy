<?php

declare(strict_types=1);

// absolute filesystem path to the web root
define('WWW_DIR', dirname(__FILE__));

require __DIR__ . '/../app/bootstrap.php';

\Tymy\Bootstrap::boot()
	->getByType(Nette\Application\Application::class)
	->run();
