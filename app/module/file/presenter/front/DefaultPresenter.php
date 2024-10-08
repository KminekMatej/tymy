<?php

namespace Tymy\Module\File\Presenter\Front;

use Exception;
use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Attributes\Inject;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\File\Handler\FileManager;
use Tymy\Module\Team\Manager\TeamManager;

use function count;

use const TEAM_DIR;

class DefaultPresenter extends SecuredPresenter
{
    private const DIR_NAME_REGEX = '([a-zA-Z_\/\-0-9áčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ ]+\.?)*[a-zA-Z_\/\-0-9áčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ ]+';

    #[Inject]
    public TeamManager $teamManager;

    #[Inject]
    public FileManager $fileManager;
    private array $fileStats;

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("file.file", 2), $this->link(":File:Default:"));
        $this->initFileStats();

        assert($this->template instanceof Template);
        $this->template->addFilter('filesize', fn($sizeInBytes): string => $this->formatBytes($sizeInBytes));

        $this->template->addFilter('filetype', function ($filename) {
            $mime = mime_content_type($filename);
            return match (true) {
                array_key_exists($mime, FileManager::getArchiveMimeTypes()) => "ARCHIVE",
                array_key_exists($mime, FileManager::getAudioMimeTypes()) => "AUDIO",
                array_key_exists($mime, FileManager::getDocumentMimeTypes()) => FileManager::getDocumentMimeTypes()[$mime] ?? "DOCUMENT",
                array_key_exists($mime, FileManager::getImageMimeTypes()) => "IMAGE",
                default => "OTHER",
            };
        });
    }

    public function renderDefault(string $folder = "/"): void
    {
        $folderSanitized = "/" . trim($folder, "/");
        $folderLink = "";
        $folderParts = explode("/", $folder);
        foreach ($folderParts as $folderPart) {
            $folderLink .= "$folderPart";
            $this->addBreadcrumb($folderPart, $this->link(":File:Default:", $folderLink));
            $folderLink .= "/";
        }

        $usedSpace = $this->fileStats["usedSpace"];
        $maxSpace = $this->teamManager->getMaxDownloadSize($this->team);

        $percentUsed = ($usedSpace / $maxSpace) * 100;

        $this->template->usedSpace = $this->formatBytes($usedSpace);
        $this->template->maxSpace = $this->formatBytes($maxSpace);

        $blue = min($percentUsed, 70);  //0-70% is progress bar blue
        $yell = min(max($percentUsed - $blue, 0), 20);  //70%-90% is progress bar yellow
        $red = min(max($percentUsed - ($blue + $yell), 0), 10);  //90%-100% is progress bar red

        $this->template->bluePercent = $blue;
        $this->template->yellPercent = $yell;
        $this->template->redPercent = $red;
        $this->template->locale = $this->translator->getLocale();

        $this->template->folder = $folderSanitized;
        $this->template->folderSlashed = rtrim($folderSanitized, "/") . "/";
        array_pop($folderParts);
        $this->template->parentFolder = implode("/", $folderParts);

        $this->template->fileTypes = $this->fileStats["fileTypes"];
        $this->template->contents = $this->getContents($folderSanitized);
    }

    public function createComponentNewFolderForm(): Form
    {
        $form = new Form();

        $form->addHidden("folder")
            ->addRule(Form::PATTERN_ICASE, $this->translator->translate("file.dirNameError"), self::DIR_NAME_REGEX);

        $form->addText('name')
            ->setRequired('Vyplňte název složky')
            ->addRule(Form::PATTERN_ICASE, $this->translator->translate("file.dirNameError"), self::DIR_NAME_REGEX);

        $form->addSubmit('send', $this->translator->translate("file.add"));
        $form->onSuccess[] = function (Form $form, $values): void {
            $currentFolder = $values->folder;
            $folderName = $values->name;
            mkdir(FileManager::DOWNLOAD_DIR . $currentFolder . "/" . $folderName);
            $this->redirect(":File:Default:", $currentFolder . "/" . $folderName);  //redirect to new folder
        };

        return $form;
    }

    public function createComponentRenameForm(): Form
    {
        $form = new Form();

        $form->addHidden("folder")->addRule(Form::PATTERN_ICASE, $this->translator->translate("file.dirNameError"), self::DIR_NAME_REGEX);
        $form->addHidden("oldName")->addRule(Form::PATTERN_ICASE, $this->translator->translate("file.dirNameError"), self::DIR_NAME_REGEX);

        $form->addText('name')
            ->setRequired()
            ->addRule(Form::PATTERN_ICASE, $this->translator->translate("file.dirNameError"), self::DIR_NAME_REGEX);

        $form->addSubmit('send', $this->translator->translate("file.rename"));
        $form->onSuccess[] = function (Form $form, $values): void {
            $oldpath = FileManager::DOWNLOAD_DIR . $values->folder . "/" . trim($values->oldName, "/. ");
            if (file_exists($oldpath)) {
                $newpath = FileManager::DOWNLOAD_DIR . $values->folder . "/" . trim($values->name, "/. ");
                rename($oldpath, $newpath);
            }

            $this->redirect(":File:Default:", $values->folder);
        };

        return $form;
    }

    public function createComponentUploadFileForm(): Form
    {
        $form = new Form();

        $form->addHidden("folder")->addRule(Form::PATTERN_ICASE, $this->translator->translate("file.dirNameError"), self::DIR_NAME_REGEX);

        $form->addUpload('upload');
        $form->onError[] = function (Form $form): void {
            foreach ($form->getErrors() as $error) {
                $this->presenter->flashMessage($error, "danger");
            }

            $this->presenter->redrawControl("flashes");
        };

        $form->onSuccess[] = function (Form $form, $values): void {
            $folder = trim($values->folder, "/. ");
            try {
                $this->fileManager->save($values->upload, $folder);
            } catch (Exception $exc) {
                $this->presenter->flashMessage($exc->getMessage(), "danger");
                $this->presenter->redrawControl("flashes");
            }

            $this->reloadFileList($folder);
        };

        return $form;
    }

    /**
     * Get download folder contents
     *
     * @return array<string, mixed[]>
     */
    private function getContents(string $folder): array
    {
        $contents = glob(FileManager::DOWNLOAD_DIR . $folder . "/*");
        $arr = [
            "contents" => $contents,
            "dirs" => [],
            "files" => [],
        ];

        foreach ($contents as $filename) {
            if (is_dir($filename)) {
                $arr["dirs"][] = [
                    "name" => $filename,
                    "size" => $this->folderSize($filename),
                    "count" => count(array_filter(glob("$filename/*"), 'is_file')),
                ];
            } elseif (is_file($filename)) {
                $arr["files"][] = $filename;
            }
        }

        return $arr;
    }

    private function initFileStats(): void
    {
        if (isset($this->fileStats)) {
            return;
        }

        $cachedSizeFile = TEAM_DIR . "/temp/cache/file-stats.json";

        $this->fileStats = [];
        $timestamp = new DateTime();

        if (file_exists($cachedSizeFile)) {
            $decoded = \json_decode(file_get_contents($cachedSizeFile), true, 512, JSON_THROW_ON_ERROR);
            if ($decoded) {
                $this->fileStats = $decoded;
                $timestamp = DateTime::createFromFormat("U", (string) filemtime($cachedSizeFile));
            }
        }

        if (empty($this->fileStats) || $timestamp < new DateTime("- 10 minutes")) {
            $this->fileStats = [
                "usedSpace" => $this->folderSize(FileManager::DOWNLOAD_DIR),
            ];

            $this->loadFileTypeSizes(FileManager::DOWNLOAD_DIR);
            file_put_contents($cachedSizeFile, \json_encode($this->fileStats, JSON_THROW_ON_ERROR));
        }
    }

    private function loadFileTypeSizes(string $folder): void
    {
        if (!array_key_exists("fileTypes", $this->fileStats)) {
            $this->fileStats["fileTypes"] = [
                "archive" => [
                    "size" => 0,
                    "count" => 0,
                    "name" => "Archiv",
                ],
                "audio" => [
                    "size" => 0,
                    "count" => 0,
                    "name" => "Hudba",
                ],
                "document" => [
                    "size" => 0,
                    "count" => 0,
                    "name" => "Dokumenty",
                ],
                "image" => [
                    "size" => 0,
                    "count" => 0,
                    "name" => "Obrázky",
                ],
                "other" => [
                    "size" => 0,
                    "count" => 0,
                    "name" => "Ostatní",
                ],
            ];
        }

        foreach (glob(rtrim($folder, '/') . '/*', GLOB_NOSORT) as $each) {
            if (is_file($each)) {
                $mime = mime_content_type($each);
                switch (true) {
                    case array_key_exists($mime, FileManager::getArchiveMimeTypes()):
                        $this->fileStats["fileTypes"]["archive"]["size"] += filesize($each);
                        $this->fileStats["fileTypes"]["archive"]["count"]++;
                        break;
                    case array_key_exists($mime, FileManager::getAudioMimeTypes()):
                        $this->fileStats["fileTypes"]["audio"]["size"] += filesize($each);
                        $this->fileStats["fileTypes"]["audio"]["count"]++;
                        break;
                    case array_key_exists($mime, FileManager::getDocumentMimeTypes()):
                        $this->fileStats["fileTypes"]["document"]["size"] += filesize($each);
                        $this->fileStats["fileTypes"]["document"]["count"]++;
                        break;
                    case array_key_exists($mime, FileManager::getImageMimeTypes()):
                        $this->fileStats["fileTypes"]["image"]["size"] += filesize($each);
                        $this->fileStats["fileTypes"]["image"]["count"]++;
                        break;
                    default:
                        $this->fileStats["fileTypes"]["other"]["size"] += filesize($each);
                        $this->fileStats["fileTypes"]["other"]["count"]++;
                        break;
                }
            } elseif (is_dir($each)) {
                $this->loadFileTypeSizes($folder . "/" . $each);
            }
        }
    }

    private function folderSize(string $dir): int
    {
        $size = 0;

        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->folderSize($each);
        }

        return $size;
    }

    /**
     * Format number of bytes into human readable string.
     * 805 = 805 B
     * 824320 = 805 KB
     * etc
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes !== 0 ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function handleDelete(string $folder = "/", string $filename = ""): void
    {
        if (empty($filename)) {
            return;
        }

        $filepath = FileManager::DOWNLOAD_DIR . "/" . trim($folder, "/. ") . "/" . trim($filename, "/. ");

        if (is_file($filepath) || is_link($filepath)) {
            unlink($filepath);
        } elseif (is_dir($filepath)) {
            $this->rrmdir($filepath);
        }

        $this->reloadFileList($folder);
    }

    private function rrmdir($dir): void
    {
        if (is_link($dir)) {
            unlink($dir);
        } elseif (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    private function reloadFileList(string $folder): void
    {
        $this->template->contents = $this->getContents("/" . $folder);
        $this->redrawControl("file-list");
    }
}
