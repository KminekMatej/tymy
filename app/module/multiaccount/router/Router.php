<?php

namespace Tymy\Module\Multiaccount\Router;

use Nette\Application\Routers\Route;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 07.02.2021
 */
class Router
{
    /**
     * @return Array
     */
    public function createRoutes()
    {
        return [
            new Route('multiaccount[s][/<resourceId>]', array(
                'module' => 'Multiaccount',
                'presenter' => 'Default',
                'action' => 'default',
                    ))
        ];
    }
}
