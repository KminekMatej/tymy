<?php

namespace Tymy\Module\Admin\Presenter\Api;

use Tymy\Module\Admin\Manager\MigrationManager;

/**
 * Description of MigratePresenter
 */
class MigratePresenter extends AdminSecuredPresenter
{
    #[\Nette\DI\Attributes\Inject]
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
