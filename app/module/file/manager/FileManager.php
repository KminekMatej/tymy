<?php

namespace Tymy\Module\File\Handler;

use Nette\Http\FileUpload;
use Nette\Security\User;
use Tracy\Debugger;
use Tracy\ILogger;
use Tymy\Module\Core\Manager\Responder;

use const TEAM_DIR;

/**
 * Description of FileManager
 *
 * @author kminekmatej, 7.11.2018
 */
class FileManager
{
    public const DOWNLOAD_DIR = TEAM_DIR . "/download";

    public function __construct(private Responder $responder)
    {
    }

    /**
     * Uložení nahraného souboru.
     *
     * @return string Saved file path
     */
    public function save(FileUpload $file, string $folder): string
    {
        $mime = mime_content_type($file->getTemporaryFile());

        if (!array_key_exists($mime, $this->getMimeTypes())) {
            //mime not matched
            unlink($file->getTemporaryFile());
            $this->responder->E403_FORBIDDEN("Uploading type `$mime` is forbidden");
        }

        if (!$file->isOk()) {
            unlink($file->getTemporaryFile());
            $this->responder->E4009_CREATE_FAILED("File");
        }

        if (!is_dir(self::DOWNLOAD_DIR)) {
            mkdir(self::DOWNLOAD_DIR, 0777, true);
        }

        $targetFile = self::DOWNLOAD_DIR . "/$folder/" . $file->getSanitizedName();

        if (!$file->move($targetFile)) {
            Debugger::log("File saving failed [$targetFile]", ILogger::ERROR);
        }

        return $targetFile;
    }

    /**
     * @return mixed[]
     */
    private function getMimeTypes(): array
    {
        return self::getArchiveMimeTypes() +
            self::getAudioMimeTypes() +
            self::getDocumentMimeTypes() +
            self::getImageMimeTypes();
    }

    public static function getArchiveMimeTypes(): array
    {
        return [
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'application/x-tar' => 'tar',
            'application/x-7z-compressed' => '7z',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getAudioMimeTypes(): array
    {
        return [
            'audio/mpeg3' => 'mp3',
            'audio/x-mpeg-3' => 'mp3',
            'audio/ogg' => 'ogg',
            'audio/x-aiff' => 'aiff',
        ];
    }

    public static function getDocumentMimeTypes(): array
    {
        return [
            'text/plain' => 'txt',
            'application/msword' => 'doc',
            'application/vnd.ms-excel' => 'xls',
            'application/pdf' => 'pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        ];
    }

    public static function getImageMimeTypes(): array
    {
        return [
            'image/png' => 'png',
            'image/pjpeg' => 'jpeg',
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
        ];
    }
}
