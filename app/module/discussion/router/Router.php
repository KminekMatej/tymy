<?php

namespace Tymy\Module\Discussion\Router;

use Tymy\Module\Core\Router\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 13. 9. 2020
 */
class Router implements RouterInterface
{
    public function addRoutes(RouteList &$router): void
    {
        $router->addApiRoute('discussion[s][/accessible][/withNew]', ['module' => 'Discussion', 'presenter' => 'Default', 'action' => 'default'])
                ->addApiRoute('discussion[s][/newOnly]', ['module' => 'Discussion', 'presenter' => 'NewOnly', 'action' => 'default'])
                ->addApiRoute('discussion[s][/<resourceId \d+>]/<mode html|bb>[/<subResourceId \d+>]', ['module' => 'Discussion', 'presenter' => 'Post', 'action' => 'mode']);
    }
}
