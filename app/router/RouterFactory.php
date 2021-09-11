<?php

namespace Tymy;

use Nette\Application\Routers\Route;
use Nette\DI\Container;
use Tymy\Module\Core\Interfaces\RouterInterface;
use Tymy\Module\Core\Router\RouteList;

class RouterFactory
{

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return RouteList
     */
    public function createRouter(): RouteList
    {
        /* @var $router RouteList */
        $router = $this->moduleRouteList();
        
        // API routes
        $router->withPath("api")
                ->addRoute('<module>[s][/<resourceId \d+>][/<presenter>][s][/<subResourceId \d+>][/<action>]', [
                    'module' => [
                        Route::FILTER_IN => function ($module) {
                            return 'Api:' . ucfirst($module);
                        },
                        Route::FILTER_OUT => function ($module) {
                            return is_array($module) ? strtolower(explode(':', $module)[1]) : $module;
                        },
                    ],
                    'presenter' => 'Default',
                    'action' => 'default',
                        ]
        );

        // APP routes
        $router->addRoute('index.php', 'Homepage:default', Route::ONE_WAY);
        $router->addRoute('diskuze', 'Discussion:default');
        $router->addRoute('udalosti', 'Event:default');
        //$router->addRoute('poznamky', 'Notes:default');
        $router->addRoute('dluznicek', 'Debt:default');
        $router->addRoute('tym', 'Team:default');
        $router->addRoute('tym/registrovani', 'Team:inits');
        $router->addRoute('tym/hraci', 'Team:players');
        $router->addRoute('tym/clenove', 'Team:members');
        $router->addRoute('tym/marodi', 'Team:sicks');
        $router->addRoute('tym/dresy', 'Team:jerseys');
        $router->addRoute('tym/novy', 'Team:new');
        $router->addRoute('tym/<player>', 'Team:player');
        $router->addRoute('tym/<player>/novy', 'Team:new');
        $router->addRoute('ankety', 'Poll:default');
        $router->addRoute('diskuze/<discussion>[/<page>] ? search=<search>', 'Discussion:discussion');
        $router->addRoute('udalosti/<udalost>', 'Event:event');
        $router->addRoute('dluznicek/<dluh>', 'Debt:debt');
        $router->addRoute('dluznicek/<dluh>/qr', 'Debt:debtImg');
        $router->addRoute('ankety/<anketa>', 'Poll:poll');
        //$router->addRoute('poznamky/<poznamka>', 'Notes:note');
        $router->addRoute('nastaveni', 'Settings:default');
        $router->addRoute('nastaveni/diskuze/nova', 'Settings:discussion_new');
        $router->addRoute('nastaveni/diskuze[/<discussion>]', 'Settings:discussions');
        $router->addRoute('nastaveni/udalosti/nova', 'Settings:event_new');
        $router->addRoute('nastaveni/udalosti[/<event>]', 'Settings:events');
        $router->addRoute('nastaveni/ankety/nova', 'Settings:poll_new');
        $router->addRoute('nastaveni/ankety[/<poll>]', 'Settings:polls');
        $router->addRoute('nastaveni/poznamky/nova', 'Settings:note_new');
        //$router->addRoute('nastaveni/poznamky[/<note>]', 'Settings:notes');
        $router->addRoute('nastaveni/tym', 'Settings:team');
        $router->addRoute('nastaveni/reporty', 'Settings:reports');
        $router->addRoute('nastaveni/multiucet', 'Settings:multiaccount');
        $router->addRoute('nastaveni/opravneni/nove', 'Settings:permission_new');
        $router->addRoute('nastaveni/opravneni[/<permission>]', 'Settings:permissions');
        $router->addRoute('nastaveni/aplikace', 'Settings:app');
        $router->addRoute('[<module>][/<presenter>][/<action>]', 'Core:Homepage:default');

        return $router;
    }

    /**
     * Create RouteList, already containing routes from modules
     * @return Routelist
     */
    private function moduleRouteList(): Routelist
    {
        $router = new RouteList;

        $routerNames = $this->container->findByType(RouterInterface::class);

        foreach ($routerNames as $routerName) {
            $this->container->getByName($routerName)->addRoutes($router);
        }

        return $router;
    }

    public function addModuleRoutes(array $moduleRoutes): RouterFactory
    {
        $this->moduleRoutes = array_merge($this->moduleRoutes, $moduleRoutes);
        return $this;
    }
}