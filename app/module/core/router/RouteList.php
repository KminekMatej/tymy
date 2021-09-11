<?php

namespace Tymy\Module\Core\Router;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList as NetteRouteList;


/**
 * Description of RouteList
 *
 * @author kminekmatej, 11. 9. 2021
 */
class RouteList extends NetteRouteList
{

    public function addApiRoute(string $mask, $metadata = [], int $flags = 0)
    {
        $metadata['module'] = [
            Route::VALUE => isset($metadata['module']) ? "Api:" . $metadata['module'] : null,
            Route::FILTER_IN => function ($module) {
                return 'Api:' . ucfirst($module);
            },
            Route::FILTER_OUT => function ($module) {
                return is_array($module) ? strtolower(explode(':', $module)[1]) : $module;
            },
        ];

        return parent::addRoute("/api/" . $mask, $metadata, $flags);
    }

}