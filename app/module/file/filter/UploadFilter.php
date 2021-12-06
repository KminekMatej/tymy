<?php
namespace Tymy\Module\File\Filter;

use Zet\FileUpload\Filter\BaseFilter;

/**
 * Description of UploadFilter
 *
 * @author kminekmatej, 6. 12. 2021, 10:43:13
 */
class UploadFilter extends BaseFilter
{

    protected function getMimeTypes(): array
    {
        return self::getArchiveMimeTypes() +
            self::getAudioMimeTypes() +
            self::getDocumentMimeTypes() +
            self::getImageMimeTypes();
    }

    public function getArchiveMimeTypes(): array
    {
        return [
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'application/x-tar' => 'tar',
            'application/x-7z-compressed' => '7z',
        ];
    }

    public function getAudioMimeTypes(): array
    {
        return [
            'audio/mpeg3' => 'mp3',
            'audio/x-mpeg-3' => 'mp3',
            'audio/ogg' => 'ogg',
            'audio/x-aiff' => 'aiff',
        ];
    }

    public function getDocumentMimeTypes(): array
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
