<?php

namespace Tymy\Module\Admin\Presenter\Api;

use Tymy\Module\Admin\Manager\MigrationManager;

/**
 * Description of MigratePresenter
 *
 * @author kminekmatej, 25. 10. 2021
 */
class MigratePresenter extends AdminSecuredPresenter
{
    /** @inject */
    public MigrationManager $migrationManager;

    public function actionDefault(): void
    {
        $output = $this->migrationManager->migrateUp();
        if ($output["success"]) {
            $this->respondOk($output["log"]);
        } else {
            $this->responder->E4018_MIGRATION_FAILED($output["log"]);
        }
    }
}
