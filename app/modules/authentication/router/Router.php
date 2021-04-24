<?php

namespace Tymy\Module\Authentication\Router;

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
            new Route('log<action \D+>[/<username>][/<password>]', array(
                'module' => 'Authentication',
                'presenter' => 'Default',
                'action' => 'default',
                    )),
            new Route('is', array(
                'module' => 'Authentication',
                'presenter' => 'Is',
                'action' => 'default',
                    )),
        ];
    }
}
