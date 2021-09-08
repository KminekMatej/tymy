<?php

namespace Tymy\Module\News\Router;

use Nette\Application\Routers\Route;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 21. 02. 2021
 */
class Router
{

    /**
     * @return Array
     */
    public function createRoutes()
    {
        return [
            new Route('news[/<resourceId \d+>][/<presenter>][s][/<subResourceId \d+>][/<action>]',
                    [
                'module' => 'News',
                'presenter' => 'Default',
                'action' => 'default',
                    ]
            )
        ];
    }

}
