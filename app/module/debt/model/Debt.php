<?php

namespace Tymy\Module\Debt\Model;

use JsonSerializable;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Debt\Mapper\DebtMapper;
use Tymy\Module\User\Model\User;

/**
 * Description of Debt
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 29. 11. 2020
 */
class Debt extends BaseModel implements JsonSerializable
{
    public const TABLE = "debt";
    public const MODULE = "debt";
    public const CURRENCIES = ["CZK" => "Kč", "EUR" => "€"];

    private DateTime $created;
    private ?int $createdUserId = null;
    private float $amount;
    private ?string $currencyIso = null;
    private ?string $countryIso = null;
    private int $debtorId = 0;
    private ?string $debtorType = null;
    private ?int $payeeId = null;
    private ?string $payeeType = null;
    private ?string $payeeAccountNumber = null;
    private ?string $varcode = null;
    private ?DateTime $debtDate = null;
    private ?string $caption = null;
    private ?DateTime $paymentSent = null;
    private ?DateTime $paymentReceived = null;
    private ?string $note = null;
    private bool $canRead = false;
    private bool $canEdit = false;
    private bool $isTeamDebt = false;
    private bool $canSetSentDate = false;
    private bool $paymentPending = false;
    private ?string $webName = null;
    private string $class;
    private ?User $debtor = null;
    private ?User $payee = null;
    private ?User $author = null;
    private string $debtorCallName;
    private string $payeeCallName;
    private string $displayString;

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getCreatedUserId(): ?int
    {
        return $this->createdUserId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrencyIso(): ?string
    {
        return $this->currencyIso;
    }

    public function getCountryIso(): ?string
    {
        return $this->countryIso;
    }

    public function getDebtorId(): int
    {
        return $this->debtorId;
    }

    public function getDebtorType(): ?string
    {
        return $this->debtorType;
    }

    public function getPayeeId(): ?int
    {
        return $this->payeeId;
    }

    public function getPayeeType(): ?string
    {
        return $this->payeeType;
    }

    public function getPayeeAccountNumber(): ?string
    {
        return $this->payeeAccountNumber;
    }

    public function getVarcode(): ?string
    {
        return $this->varcode;
    }

    public function getDebtDate(): ?DateTime
    {
        return $this->debtDate;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getPaymentSent(): ?DateTime
    {
        return $this->paymentSent;
    }

    public function getPaymentReceived(): ?DateTime
    {
        return $this->paymentReceived;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getCanRead(): bool
    {
        return $this->canRead;
    }

    public function getCanEdit(): bool
    {
        return $this->canEdit;
    }

    public function getIsTeamDebt(): bool
    {
        return $this->isTeamDebt;
    }

    public function getCanSetSentDate(): bool
    {
        return $this->canSetSentDate;
    }

    public function getPaymentPending(): bool
    {
        return $this->paymentPending;
    }

    public function getWebName(): ?string
    {
        return $this->webName;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getDebtor(): ?User
    {
        return $this->debtor;
    }

    public function getPayee(): ?User
    {
        return $this->payee;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function getDebtorCallName(): string
    {
        return $this->debtorCallName;
    }

    public function getPayeeCallName(): string
    {
        return $this->payeeCallName;
    }

    public function getDisplayString(): string
    {
        return $this->displayString;
    }

    public function setCreated(DateTime $created): static
    {
        $this->created = $created;
        return $this;
    }

    public function setCreatedUserId(?int $createdUserId): static
    {
        $this->createdUserId = $createdUserId;
        return $this;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function setCurrencyIso(?string $currencyIso): static
    {
        $this->currencyIso = $currencyIso;
        return $this;
    }

    public function setCountryIso(?string $countryIso): static
    {
        $this->countryIso = $countryIso;
        return $this;
    }

    public function setDebtorId(?int $debtorId): static
    {
        $this->debtorId = $debtorId ?: 0;
        return $this;
    }

    public function setDebtorType(?string $debtorType): static
    {
        $this->debtorType = $debtorType;
        return $this;
    }

    public function setPayeeId(?int $payeeId): static
    {
        $this->payeeId = $payeeId;
        return $this;
    }

    public function setPayeeType(?string $payeeType): static
    {
        $this->payeeType = $payeeType;
        return $this;
    }

    public function setPayeeAccountNumber(?string $payeeAccountNumber): static
    {
        $this->payeeAccountNumber = $payeeAccountNumber;
        return $this;
    }

    public function setVarcode(?string $varcode): static
    {
        $this->varcode = $varcode;
        return $this;
    }

    public function setDebtDate(?DateTime $debtDate): static
    {
        $this->debtDate = $debtDate;
        return $this;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;
        return $this;
    }

    public function setPaymentSent(?DateTime $paymentSent): static
    {
        $this->paymentSent = $paymentSent;
        return $this;
    }

    public function setPaymentReceived(?DateTime $paymentReceived): static
    {
        $this->paymentReceived = $paymentReceived;
        return $this;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function setCanRead(bool $canRead): static
    {
        $this->canRead = $canRead;
        return $this;
    }

    public function setCanEdit(bool $canEdit): static
    {
        $this->canEdit = $canEdit;
        return $this;
    }

    public function setIsTeamDebt(bool $isTeamDebt): static
    {
        $this->isTeamDebt = $isTeamDebt;
        return $this;
    }

    public function setCanSetSentDate(bool $canSetSentDate): static
    {
        $this->canSetSentDate = $canSetSentDate;
        return $this;
    }

    public function setPaymentPending(bool $paymentPending): static
    {
        $this->paymentPending = $paymentPending;
        return $this;
    }

    public function setWebName(?string $webName): static
    {
        $this->webName = $webName;
        return $this;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;
        return $this;
    }

    public function setDebtor(?User $debtor): static
    {
        $this->debtor = $debtor;
        return $this;
    }

    public function setPayee(?User $payee): static
    {
        $this->payee = $payee;
        return $this;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function setDebtorCallName(string $debtorCallName): static
    {
        $this->debtorCallName = $debtorCallName;
        return $this;
    }

    public function setPayeeCallName(string $payeeCallName): static
    {
        $this->payeeCallName = $payeeCallName;
        return $this;
    }

    public function setDisplayString(string $displayString): static
    {
        $this->displayString = $displayString;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    public function getScheme(): array
    {
        return DebtMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return parent::jsonSerialize() + [
            "canRead" => $this->canRead,
            "canEdit" => $this->canEdit,
            "isTeamDebt" => $this->isTeamDebt,
            "canSetSentDate" => $this->canSetSentDate,
        ];
    }
}
