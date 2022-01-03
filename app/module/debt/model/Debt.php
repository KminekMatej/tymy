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
    private ?User $debtor;
    private ?User $payee;
    private ?User $author;
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

    public function setCreated(DateTime $created)
    {
        $this->created = $created;
        return $this;
    }

    public function setCreatedUserId(?int $createdUserId)
    {
        $this->createdUserId = $createdUserId;
        return $this;
    }

    public function setAmount(float $amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setCurrencyIso(?string $currencyIso)
    {
        $this->currencyIso = $currencyIso;
        return $this;
    }

    public function setCountryIso(?string $countryIso)
    {
        $this->countryIso = $countryIso;
        return $this;
    }

    public function setDebtorId(?int $debtorId)
    {
        $this->debtorId = $debtorId ?: 0;
        return $this;
    }

    public function setDebtorType(?string $debtorType)
    {
        $this->debtorType = $debtorType;
        return $this;
    }

    public function setPayeeId(?int $payeeId)
    {
        $this->payeeId = $payeeId;
        return $this;
    }

    public function setPayeeType(?string $payeeType)
    {
        $this->payeeType = $payeeType;
        return $this;
    }

    public function setPayeeAccountNumber(?string $payeeAccountNumber)
    {
        $this->payeeAccountNumber = $payeeAccountNumber;
        return $this;
    }

    public function setVarcode(?string $varcode)
    {
        $this->varcode = $varcode;
        return $this;
    }

    public function setDebtDate(?DateTime $debtDate)
    {
        $this->debtDate = $debtDate;
        return $this;
    }

    public function setCaption(?string $caption)
    {
        $this->caption = $caption;
        return $this;
    }

    public function setPaymentSent(?DateTime $paymentSent)
    {
        $this->paymentSent = $paymentSent;
        return $this;
    }

    public function setPaymentReceived(?DateTime $paymentReceived)
    {
        $this->paymentReceived = $paymentReceived;
        return $this;
    }

    public function setNote(?string $note)
    {
        $this->note = $note;
        return $this;
    }

    public function setCanRead(bool $canRead)
    {
        $this->canRead = $canRead;
        return $this;
    }

    public function setCanEdit(bool $canEdit)
    {
        $this->canEdit = $canEdit;
        return $this;
    }

    public function setIsTeamDebt(bool $isTeamDebt)
    {
        $this->isTeamDebt = $isTeamDebt;
        return $this;
    }

    public function setCanSetSentDate(bool $canSetSentDate)
    {
        $this->canSetSentDate = $canSetSentDate;
        return $this;
    }

    public function setPaymentPending(bool $paymentPending)
    {
        $this->paymentPending = $paymentPending;
        return $this;
    }

    public function setWebName(?string $webName)
    {
        $this->webName = $webName;
        return $this;
    }

    public function setClass(string $class)
    {
        $this->class = $class;
        return $this;
    }

    public function setDebtor(?User $debtor)
    {
        $this->debtor = $debtor;
        return $this;
    }

    public function setPayee(?User $payee)
    {
        $this->payee = $payee;
        return $this;
    }

    public function setAuthor(?User $author)
    {
        $this->author = $author;
        return $this;
    }

    public function setDebtorCallName(string $debtorCallName)
    {
        $this->debtorCallName = $debtorCallName;
        return $this;
    }

    public function setPayeeCallName(string $payeeCallName)
    {
        $this->payeeCallName = $payeeCallName;
        return $this;
    }

    public function setDisplayString(string $displayString)
    {
        $this->displayString = $displayString;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return DebtMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            "canRead" => $this->canRead,
            "canEdit" => $this->canEdit,
            "isTeamDebt" => $this->isTeamDebt,
            "canSetSentDate" => $this->canSetSentDate,
        ];
    }
}