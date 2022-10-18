<?php

namespace Tymy\Module\File\Presenter\Front;

use Nette\Application\Responses\FileResponse;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\File\Handler\FileManager;

/**
 * Description of DownloadPresenter
 *
 * @author kminekmatej, 2. 5. 2022, 22:48:27
 */
class DownloadPresenter extends SecuredPresenter
{
    public function actionDefault(string $filename, string $folder = "/"): void
    {
        $folderSlashed = rtrim($folder, "/") . "/";
        $fullpath = FileManager::DOWNLOAD_DIR . $folderSlashed . \Tymy\Module\Core\Helper\StringHelper::urldecode($filename);

        if (!file_exists($fullpath)) {
            $this->flashMessage("File $fullpath not found", 'warning');
            $this->redirect(":File:Default:");
        }

        $this->sendResponse(new FileResponse($fullpath, null, null, true));
    }
}
