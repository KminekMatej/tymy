<?php

namespace Tymy\Module\User\Manager;

use Contributte\Translation\Translator;
use Nette\Application\LinkGenerator;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Core\Service\MailService;
use Tymy\Module\User\Mapper\InvitationMapper;
use Tymy\Module\User\Model\Invitation;

/**
 * @extends BaseManager<Invitation>
 */
class InvitationManager extends BaseManager
{
    public function __construct(ManagerFactory $managerFactory, private UserManager $userManager, private LinkGenerator $linkGenerator, private Translator $translator, private MailService $mailService)
    {
        parent::__construct($managerFactory);
    }

    public function getClassName(): string
    {
        return Invitation::class;
    }

    /**
     * @return Field[]
     */
    public function getScheme(): array
    {
        return InvitationMapper::scheme();
    }

    public function canEdit(BaseModel $entity, int $userId): bool
    {
        return in_array($userId, $this->userManager->getUserIdsWithPrivilege("SYS:USR_CREATE"));
    }

    protected function allowCreate(?array &$data = null): void
    {
        if (!$this->user->isAllowed((string) $this->user->getId(), "SYS:USR_CREATE")) {
            $this->responder->E4003_CREATE_NOT_PERMITTED("Invitiation");
        }

        $this->checkInputs($data);

        if (empty($data["firstName"]) && empty($data["lastName"]) || empty($data["email"])) {
            $this->respondBadRequest($this->translator->translate("team.errors.invitationAtLeast"));
        }

        $existingUser = $this->userManager->getIdByEmail($data["email"]);

        if ($existingUser) {
            $this->respondBadRequest($this->translator->translate("team.alerts.emailExists"));
        }
    }

    protected function allowDelete(?int $recordId): void
    {
        if (!$this->user->isAllowed((string) $this->user->getId(), "SYS:USR_CREATE")) {
            $this->responder->E4004_DELETE_NOT_PERMITTED("Invitiation", $recordId);
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        if (!$this->user->isAllowed((string) $this->user->getId(), "SYS:USR_CREATE")) {
            $this->responder->E4001_VIEW_NOT_PERMITTED("Invitiation", $recordId);
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        if (!$this->user->isAllowed((string) $this->user->getId(), "SYS:USR_CREATE")) {
            $this->responder->E4002_EDIT_NOT_PERMITTED("Invitiation", $recordId);
        }
    }

    public function canRead(BaseModel $entity, int $userId): bool
    {
        return in_array($userId, $this->userManager->getUserIdsWithPrivilege("SYS:USR_CREATE"));
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $data["code"] = bin2hex(openssl_random_pseudo_bytes(6));
        $data["validUntil"] = new DateTime("+ 30 days");

        $this->allowCreate($data);

        $createdRow = $this->createByArray($data);

        $invitation = $this->map($createdRow);
        assert($invitation instanceof Invitation);

        if ($invitation->getEmail()) {
            $this->notifyByEmail($invitation);
        }

        return $invitation;
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowDelete($resourceId);

        return parent::deleteRecord($resourceId);
    }

    /**
     * @return mixed[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        return $this->userManager->getUserIdsWithPrivilege("SYS:USR_CREATE");
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $invitation = parent::getById($resourceId);

        $this->allowRead($resourceId);

        return $invitation;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowUpdate($resourceId, $data);

        parent::updateByArray($resourceId, $data);

        return $this->getById($resourceId, true);
    }

    private function notifyByEmail(Invitation $invitation): void
    {
        $name = $invitation->getFirstName() || $invitation->getLastName() ? implode(" ", [$invitation->getFirstName(), $invitation->getLastName()]) : null;

        $creator = $this->userManager->getById($this->user->getId());

        $this->mailService->mailInvitation($name, $invitation->getEmail(), $creator->getFullName() . "({$creator->getDisplayName()})", $this->linkGenerator->link("Sign:ByInvite:default", ["invite" => $invitation->getCode()]), $invitation->getValidUntil());
    }

    /**
     * Get Invitation by its code
     */
    public function getByCode(string $code): ?Invitation
    {
        return $this->map($this->database->table($this->getTable())->where("code", $code)->fetch());
    }

    /**
     * Load e-mails that already exists - for form validation
     * @return string[]
     */
    public function getExistingEmails(): array
    {
        return $this->database->table($this->getTable())->fetchPairs(null, "email");
    }
}
