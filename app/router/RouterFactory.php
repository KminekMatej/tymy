<?php

namespace Tymy;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    private array $moduleRoutes = [];

    /**
     * @return RouteList
     */
    public function createRouter(): RouteList
    {
        $router = new RouteList;

        foreach ($this->moduleRoutes as $moduleRoute) {
            $router->add($moduleRoute);
        }

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
        $router->addRoute('<presenter>/<action>', 'Homepage:default');

        return $router;
    }

    public function addModuleRoutes(array $moduleRoutes): RouterFactory
    {
        $this->moduleRoutes = array_merge($this->moduleRoutes, $moduleRoutes);
        return $this;
    }
}