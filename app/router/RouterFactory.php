<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter(){
		$router = new RouteList;
                $router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
                $router[] = new Route('diskuze', 'Discussion:default');
                $router[] = new Route('udalosti', 'Event:default');
                $router[] = new Route('poznamky', 'Notes:default');
                $router[] = new Route('dluznicek', 'Debt:default');
                $router[] = new Route('tym', 'Team:default');
                $router[] = new Route('tym/registrovani', 'Team:inits');
                $router[] = new Route('tym/hraci', 'Team:players');
                $router[] = new Route('tym/clenove', 'Team:members');
                $router[] = new Route('tym/marodi', 'Team:sicks');
                $router[] = new Route('tym/dresy', 'Team:jerseys');
                $router[] = new Route('tym/novy', 'Team:new');
                $router[] = new Route('tym/<player>', 'Team:player');
                $router[] = new Route('tym/<player>/novy', 'Team:new');
                $router[] = new Route('ankety', 'Poll:default');
                $router[] = new Route('diskuze/<discussion>[/<page>] ? search=<search>', 'Discussion:discussion');
                $router[] = new Route('udalosti/<udalost>', 'Event:event');
                $router[] = new Route('dluznicek/<dluh>', 'Debt:debt');
                $router[] = new Route('ankety/<anketa>', 'Poll:poll');
                $router[] = new Route('poznamky/<poznamka>', 'Notes:note');
                $router[] = new Route('nastaveni', 'Settings:default');
                $router[] = new Route('nastaveni/diskuze/nova', 'Settings:discussion_new');
                $router[] = new Route('nastaveni/diskuze[/<discussion>]', 'Settings:discussions');
                $router[] = new Route('nastaveni/udalosti/nova', 'Settings:event_new');
                $router[] = new Route('nastaveni/udalosti[/<event>]', 'Settings:events');
                $router[] = new Route('nastaveni/ankety/nova', 'Settings:poll_new');
                $router[] = new Route('nastaveni/ankety[/<poll>]', 'Settings:polls');
                $router[] = new Route('nastaveni/poznamky/nova', 'Settings:note_new');
                $router[] = new Route('nastaveni/poznamky[/<note>]', 'Settings:notes');
                $router[] = new Route('nastaveni/tym', 'Settings:team');
                $router[] = new Route('nastaveni/reporty', 'Settings:reports');
                $router[] = new Route('nastaveni/multiucet', 'Settings:multiaccount');
                $router[] = new Route('nastaveni/opravneni/nove', 'Settings:permission_new');
                $router[] = new Route('nastaveni/opravneni[/<permission>]', 'Settings:permissions');
                $router[] = new Route('nastaveni/aplikace', 'Settings:app');
		$router[] = new Route('<presenter>/<action>', 'Homepage:default');
		return $router;
	}

}
