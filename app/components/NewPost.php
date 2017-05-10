<?php

namespace Nette\Application\UI;

use Nette;
use App\Presenters\DiscussionPresenter;
use Nette\Utils\Strings;
use Tymy;
use Tracy\Debugger;

/**
 * Description of Navbar
 *
 * @author matej
 */
class NewPostControl extends Control {

    public function __construct() {
        parent::__construct();
    }
    
    public function render($discussion){
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/newpost.latte');
        $template->discussion = $discussion;
        $template->render();
    }
}
