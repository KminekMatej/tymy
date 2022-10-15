<?php

namespace Tymy\Module\User\Manager;

use Nette\Security\User;
use Nette\Utils\Image;
use Tymy\Module\Core\Manager\Responder;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Model\User as User2;
use const TEAM_DIR;

/**
 * Description of AvatarManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 10. 9. 2020
 */
class AvatarManager
{
    public const WIDTH = 400;
    public const HEIGHT = 500;
    private User2 $userModel;

    public function __construct(private Responder $responder, private UserManager $userManager, private User $user)
    {
    }

    private function allowUpload(int $userId): void
    {
        $this->userModel = $this->userManager->getById($userId);

        if (!$this->userModel instanceof BaseModel) {
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
     */
    public function uploadAvatarImage(Image $image, int $type, int $userId): void
    {
        $this->allowUpload($userId);

        $image->resize(self::WIDTH, self::HEIGHT)->save(TEAM_DIR . "/user_pics/$userId.png");
    }
}
