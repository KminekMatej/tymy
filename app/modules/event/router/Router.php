<?php

namespace Tymy\Module\Event\Router;

use Nette\Application\Routers\Route;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 13. 9. 2020
 */
class Router
{
    /**
     * @return Array
     */
    public function createRoutes()
    {
        return [
            new Route('event[s]/withMyAttendance', array(
                'module' => 'Event',
                'presenter' => 'Default',
                'action' => 'default',
                    )),
            new Route('eventTypes', array(
                'module' => 'Event',
                'presenter' => 'Types',
                'action' => 'default',
                    )),
        ];
    }
}
