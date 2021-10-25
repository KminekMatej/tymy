<?php

namespace Tymy\Module\Admin\Presenter\Api;

/**
 * Description of MigratePresenter
 *
 * @author kminekmatej, 25. 10. 2021
 */
class MigratePresenter extends AdminSecuredPresenter
{
    public function actionDefault()
    {
        \Tracy\Debugger::barDump("Migrating");
    }
}