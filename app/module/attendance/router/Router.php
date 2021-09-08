<?php

namespace Tymy\Module\Attendance\Router;

use Nette\Application\Routers\Route;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 03. 11. 2020
 */
class Router
{
    /**
     * @return Array
     */
    public function createRoutes()
    {
        return [
            new Route('attendance<action Status|StatusSet>[/<resourceId>]', array(
                'module' => 'Attendance',
                'presenter' => 'Status',
                    )),
        ];
    }
}
