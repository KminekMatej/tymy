<?php

namespace Tymy\Module\News\Router;

use Tymy\Module\Core\Router\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 */
class Router implements RouterInterface
{
    public function addRoutes(RouteList &$router): void
    {
        $router->addApiRoute(
            'news[/<resourceId \d+>][/<presenter>][s][/<subResourceId \d+>][/<action>]',
            [
                    'module' => 'News',
                    'presenter' => 'Default',
                    'action' => 'default',
                ]
        );
    }
}
