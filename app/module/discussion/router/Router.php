<?php

namespace Tymy\Module\Discussion\Router;

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
            new Route('discussion[s][/accessible][/withNew]', array(
                'module' => 'Discussion',
                'presenter' => 'Default',
                'action' => 'default',
                    )),
            new Route('discussion[s][/newOnly]', array(
                'module' => 'Discussion',
                'presenter' => 'NewOnly',
                'action' => 'default',
                    )),
            new Route('discussion[s][/<resourceId \d+>]/<mode html|bb>[/<subResourceId \d+>]', array(
                'module' => 'Discussion',
                'presenter' => 'Post',
                'action' => 'mode',
                    )),
        ];
    }
}
