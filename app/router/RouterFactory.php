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
        $router->addApiRoute('<module>[s][/<resourceId \d+>][/<presenter>][s][/<subResourceId \d+>][/<action>]', [
            'presenter' => 'Default',
            'action' => 'default',
        ]);
/*
        $moduleFilters = [
            "diskuze" => "Discussion",
            "udalosti" => "Event",
            "dluznicek" => "Debt",
            "tym" => "Team",
            "ankety" => "Poll",
            "nastaveni" => "Setting",
        ];

        $presenterFilters = [
            "registrovani" => "inits",
            "hraci" => "players",
            "clenove" => "members",
            "marodi" => "sicks",
            "dresy" => "jerseys",
            "novy" => "new",
        ];*/

        // APP routes
        /*$router->addRoute('index.php', 'Homepage:default', Route::ONE_WAY);
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
        $router->addRoute('ankety', 'Poll:default');
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
        $router->addRoute('nastaveni/aplikace', 'Settings:app');*/
        $router->addRoute('team[/<action players|members|sicks|inits|jerseys>]', [
            "module" => [
                Route::VALUE => "Team",
            //    Route::FILTER_TABLE => $moduleFilters,
            ],
            "presenter" => [
                Route::VALUE => "Default",
            //    Route::FILTER_TABLE => $presenterFilters,
            ],
            "action" => [
                Route::VALUE => "default",
            ],
        ]);
        $router->addRoute('discussion/<discussion>[/<page \d+>][/<action>] ? search=<search>', [
            "module" => [
                Route::VALUE => "Discussion",
            //    Route::FILTER_TABLE => $moduleFilters,
            ],
            "presenter" => [
                Route::VALUE => "Discussion",
            //    Route::FILTER_TABLE => $presenterFilters,
            ],
            "action" => [
                Route::VALUE => "default",
            ],
        ]);

        $router->addRoute('jump/<teamSysName>', "Core:Default:jump");

        $router->addRoute('[<module>][/<presenter>]/<resource \d+(\-.+)?>[/<action>]', [
            "module" => [
                Route::VALUE => "Core",
            //    Route::FILTER_TABLE => $moduleFilters,
            ],
            "presenter" => [
                Route::VALUE => "Detail",
            //    Route::FILTER_TABLE => $presenterFilters,
            ],
            "action" => [
                Route::VALUE => "default",
            //    Route::FILTER_TABLE => $presenterFilters,
            ],
        ]);

        $router->addRoute('[<module>][/<presenter>][/<resource \d+(\-.+)?>][/<action>]', [
            "module" => [
                Route::VALUE => "Core",
            //    Route::FILTER_TABLE => $moduleFilters,
            ],
            "presenter" => [
                Route::VALUE => "Default",
            //    Route::FILTER_TABLE => $presenterFilters,
            ],
            "action" => [
                Route::VALUE => "default",
            //    Route::FILTER_TABLE => $presenterFilters,
            ],
        ]);

        return $router;
    }

    /**
     * Create RouteList, already containing routes from modules
     * @return Routelist
     */
    private function moduleRouteList(): Routelist
    {
        $router = new RouteList();

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
