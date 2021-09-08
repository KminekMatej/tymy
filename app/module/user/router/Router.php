<?php

namespace Tymy\Module\User\Router;

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
            new Route('user[s]/status[/<status>]', array(
                'module' => 'User',
                'presenter' => 'Status',
                'action' => 'default',
                    )),
            new Route('pwd<action reset|lost>[/<code>]', array(
                'module' => 'User',
                'presenter' => 'Pwd',
                'action' => 'default',
                    )),
            new Route('live', array(
                'module' => 'User',
                'presenter' => 'Live',
                'action' => 'default',
                    )),
        ];
    }
}
