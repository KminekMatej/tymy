<?php

namespace Tymy\Module\User\Manager;

use Nette\Security\User;
use Nette\Utils\Image;
use Tymy\Module\Core\Manager\Responder;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Model\User as User2;

/**
 * Description of AvatarManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 10. 9. 2020
 */
class AvatarManager
{
    public const WIDTH = 400;
    public const HEIGHT = 500;

    private string $userPicFolder;
    private User $user;
    private User2 $userModel;
    private Responder $responder;
    private TeamManager $teamManager;
    private UserManager $userManager;

    public function __construct(string $userPicFolder, Responder $responder, TeamManager $teamManager, UserManager $userManager, User $user)
    {
        $this->userPicFolder = $userPicFolder;
        $this->responder = $responder;
        $this->teamManager = $teamManager;
        $this->userManager = $userManager;
        $this->user = $user;
    }

    private function allowUpload(int $userId)
    {
        $this->userModel = $this->userManager->getById($userId);

        if (!$this->userModel) {
            $this->responder->E4005_OBJECT_NOT_FOUND(User2::MODULE, $userId);
        }

        $canEditFull = $this->user->isAllowed($this->user->getId(), Privilege::SYS("USR_UPDATE"));
        $editingMyself = $this->userModel->getId() === $this->user->getId();

        if (!$canEditFull && !$editingMyself) {
            $this->responder->E4002_EDIT_NOT_PERMITTED(User2::MODULE, $userId);
        }
    }

    /**
     * Upload avatar from base64 string
     * @param string $base64Image
     * @param int $userId
     * @return void
     */
    public function uploadAvatarBase64(string $base64Image, int $userId): void
    {
        $imgParts = [];
        preg_match(BaseModel::B64_REGEX, $base64Image, $imgParts);
        if (!is_array($imgParts) || count($imgParts) != 4 || $imgParts[1] != "image") {
            $this->responder->E400_BAD_REQUEST("Base 64 error");
        }
        $imgB64 = $imgParts[3];
        $type = null;
        $image = Image::fromString(base64_decode($imgB64), $type);
        $this->uploadAvatarImage($image, $type, $userId);
    }

    /**
     * Upload avatar from Image
     * @param Image $image
     * @param int $userId
     * @return void
     */
    public function uploadAvatarImage(Image $image, int $type, int $userId): void
    {
        $this->allowUpload($userId);

        $image->resize(self::WIDTH, self::HEIGHT)->save(TEAM_DIR . "/user_pics/$userId.{$image->typeToExtension($type)}");
    }
}
