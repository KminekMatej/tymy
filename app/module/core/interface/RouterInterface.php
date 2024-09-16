<?php

namespace Tymy\Module\Core\Interfaces;

use Tymy\Module\Core\Router\RouteList;

/**
 * Description of RouterInterface
 */
interface RouterInterface
{
    public function addRoutes(RouteList &$router): void;
}
