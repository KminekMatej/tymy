<?php

namespace Tymy\Module\Core\Interfaces;

use Nette\Application\Routers\Route;

/**
 * Description of RouterInterface
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 8. 2020
 */
interface RouterInterface
{
    /** @return Route[] */
    public function createRoutes(): array;
}
