<?php

namespace Tymy\Module\Core\Interfaces;

use Nette\Application\Routers\RouteList;

/**
 * Description of RouterInterface
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 8. 2020
 */
interface RouterInterface
{

    public function addRoutes(RouteList &$router): void;
}