<?php
namespace Tymy\Module\File\Handler;

use Nette\Http\FileUpload;
use Nette\NotImplementedException;
use Tracy\Debugger;
use Tymy\Module\Core\Manager\Responder;
use Tymy\Module\User\Model\User;
use Zet\FileUpload\Model\IUploadModel;
use const TEAM_DIR;

/**
 * Description of FileUploadHandler
 *
 * @author kminekmatej, 7.11.2018
 */
class FileUploadHandler implements IUploadModel
{

    const DOWNLOAD_DIR = TEAM_DIR . "/download";

    private User $user;
    private Responder $responder;

    public function __construct(User $user, Responder $responder)
    {
        $this->user = $user;
        $this->responder = $responder;
    }

    /**
     * Zpracování požadavku o smazání souboru.
     *
     * @param mixed $uploaded Hodnota navrácená funkcí save.
     */
    public function remove($uploaded): void
    {
        if (file_exists($uploaded)) {
            unlink($uploaded);
        }
    }

    /**
     * Zpracování přejmenování souboru.
     *
     * @param mixed  $upload  Hodnota navrácená funkcí save.
     * @param string $newName Nové jméno souboru.
     * @return mixed Vlastní návratová hodnota.
     */
    public function rename($upload, $newName)
    {
        Debugger::barDump([$upload, $newName], "Renaming");
        throw new NotImplementedException();
    }

    /**
     * Uložení nahraného souboru.
     *
     * @param FileUpload   $file
     * @param array<mixed> $params Pole vlastních parametrů.
     * @return mixed               Vlastní navrátová hodnota.
     */
    public function save(FileUpload $file, array $params = array())
    {
        $targetFile = self::FILE_UPLOAD_TEMP_DIR . "/" . $file->getSanitizedName();
        if (!$file->isOk()) {
            unlink($file->getTemporaryFile());
            $this->responder->E4009_CREATE_FAILED("File");
        }

        if (!is_dir(self::FILE_UPLOAD_TEMP_DIR)) {
            mkdir(self::FILE_UPLOAD_TEMP_DIR, 0777, true);
        }

        if (!$file->move($targetFile)) {
            Debugger::log("Image saving failed [$targetFile]");
        }

        return $targetFile;
    }
}
