<?php

namespace Tymy\Module\Core\Router;

use Nette\Application\Routers\RouteList as NetteRouteList;

/**
 * Description of RouteList
 */
class RouteList extends NetteRouteList
{
    public function addApiRoute(string $mask, $metadata = [], int $flags = 0): static
    {
        $this->withPath("api")->withModule("Api")->addRoute($mask, $metadata, $flags);
        return $this;
    }
}
