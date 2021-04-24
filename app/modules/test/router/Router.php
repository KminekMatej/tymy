<?php

namespace Tymy\Module\Test\Router;

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
            new Route('test[/<resourceId>]', array(
                'module' => 'Test',
                'presenter' => 'Default',
                'action' => 'default',
                    )),
        ];
    }
}
