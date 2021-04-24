<?php

namespace Tymy\Module\Permission\Router;

use Nette\Application\Routers\Route;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 8. 2020
 */
class Router
{
    /**
     * @return Array
     */
    public function createRoutes()
    {
        return [
            new Route('permission<action Name|Type>[/<name \D+>]', array(
                'module' => 'Permission',
                'presenter' => 'Default',
                    )),
        ];
    }
}
