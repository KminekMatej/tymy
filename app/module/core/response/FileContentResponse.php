<?php

namespace Tymy\Module\Core\Response;

use Nette;
use Override;

class FileContentResponse implements Nette\Application\IResponse
{
    use Nette\SmartObject;

    public bool $resuming = true;

    /**
     * @param string $fileContent file content
     * @param string|null $name imposed file name
     * @param string|null $contentType MIME content type
     * @param bool $forceDownload True to download instead of display
     */
    public function __construct(
        private string $fileContent,
        private ?string $name = null,
        private ?string $contentType = 'application/octet-stream',
        private bool $forceDownload = true
    ) {
        $this->name = $name;
    }

    /**
     * Returns the content of a downloaded file.
     *
     * @return string
     */
    public function getFileContent(): string
    {
        return $this->fileContent;
    }

    /**
     * Returns the file name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the MIME content type of a downloaded file.
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }


    /**
     * Sends response to output.
     *
     * @return void
     */
    #[Override]
    public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
    {
        $httpResponse->setContentType($this->contentType);
        $httpResponse->setHeader(
            'Content-Disposition',
            ($this->forceDownload ? 'attachment' : 'inline')
                . '; filename="' . $this->name . '"'
                . '; filename*=utf-8\'\'' . rawurlencode($this->name)
        );

        $filesize = $length = strlen($this->fileContent);

        if ($this->resuming) {
            $httpResponse->setHeader('Accept-Ranges', 'bytes');
            if ($httpRequest->getHeader('Range') && preg_match('#^bytes=(\d*)-(\d*)\z#', $httpRequest->getHeader('Range'), $matches)) {
                $start = !empty($matches[1]) ? intval($matches[1]) : null;
                $end = !empty($matches[2]) ? intval($matches[2]) : null;
                if (is_null($start)) {
                    $start = max(0, $filesize - $end);
                    $end = $filesize - 1;
                } elseif (is_null($end) || $end > $filesize - 1) {
                    $end = $filesize - 1;
                }
                if ($end < $start) {
                    $httpResponse->setCode(416); // requested range not satisfiable
                    return;
                }

                $httpResponse->setCode(206);
                $httpResponse->setHeader('Content-Range', 'bytes ' . $start . '-' . $end . '/' . $filesize);
                $length = $end - $start + 1;
                $this->fileContent = substr($this->fileContent, $start, $end - $start);
            } else {
                $httpResponse->setHeader('Content-Range', 'bytes 0-' . ($filesize - 1) . '/' . $filesize);
            }
        }

        $httpResponse->setHeader('Content-Length', $length);
        echo $this->fileContent;
    }
}
