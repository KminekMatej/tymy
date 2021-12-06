<?php
namespace Tymy\Module\File\Presenter\Front;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\File\Filter\UploadFilter;
use Tymy\Module\File\Handler\FileUploadHandler;
use Tymy\Module\Team\Manager\TeamManager;
use const TEAM_DIR;

/**
 * Description of DebtPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 10. 2. 2020
 */
class DefaultPresenter extends SecuredPresenter
{

    /** @inject */
    public TeamManager $teamManager;
    private array $fileStats;
    private array $contents;

    public function beforeRender()
    {
        parent::beforeRender();
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("file.file", 2), "link" => $this->link(":File:Default:")]]);
        $this->initFileStats();
        $this->template->addFilter('filesize', function ($sizeInBytes) {
            return $this->formatBytes($sizeInBytes);
        });
    }

    public function renderDefault(string $folder = "/")
    {
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

        $this->template->folder = $folder;
        $this->template->fileTypes = $this->fileStats["fileTypes"];
        $this->template->contents = $this->getContents($folder);
    }

    /**
     * Get download folder contents
     * 
     * @param string $folder
     * @return array
     */
    private function getContents(string $folder): array
    {
        $contents = glob(FileUploadHandler::DOWNLOAD_DIR . $folder . "*");

        $arr = [
            "contents" => $contents,
            "dirs" => [],
            "files" => [],
        ];

        foreach ($contents as $filename) {
            if (is_dir($filename)) {
                $arr["dirs"][] = $filename;
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
            $decoded = \json_decode(file_get_contents($cachedSizeFile), true);
            if ($decoded) {
                $this->fileStats = $decoded;
                $timestamp = DateTime::createFromFormat("U", (string) filemtime($cachedSizeFile));
            }
        }

        if (empty($this->fileStats) || $timestamp < new DateTime("- 10 minutes")) {
            $this->fileStats = [
                "usedSpace" => $this->folderSize(FileUploadHandler::DOWNLOAD_DIR),
            ];
            $this->loadFileTypeSizes(FileUploadHandler::DOWNLOAD_DIR);
            file_put_contents($cachedSizeFile, \json_encode($this->fileStats));
        }
    }

    private function loadFileTypeSizes(string $folder)
    {
        foreach (glob(rtrim($folder, '/') . '/*', GLOB_NOSORT) as $each) {
            if (is_file($each)) {
                $mime = mime_content_type($each);

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

                switch (true) {
                    case array_key_exists($mime, UploadFilter::getArchiveMimeTypes()):
                        $this->fileStats["fileTypes"]["archive"]["size"] += filesize($each);
                        $this->fileStats["fileTypes"]["archive"]["count"]++;
                        break;
                    case array_key_exists($mime, UploadFilter::getAudioMimeTypes()):
                        $this->fileStats["fileTypes"]["audio"]["size"] += filesize($each);
                        $this->fileStats["fileTypes"]["audio"]["count"]++;
                        break;
                    case array_key_exists($mime, UploadFilter::getDocumentMimeTypes()):
                        $this->fileStats["fileTypes"]["document"]["size"] += filesize($each);
                        $this->fileStats["fileTypes"]["document"]["count"]++;
                        break;
                    case array_key_exists($mime, UploadFilter::getImageMimeTypes()):
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
            $size += is_file($each) ? filesize($each) : folderSize($each);
        }

        return $size;
    }

    /**
     * Format number of bytes into human readable string.
     * 805 = 805 B
     * 824320 = 805 KB
     * etc
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
