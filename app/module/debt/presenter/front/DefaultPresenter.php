<?php

namespace Tymy\Module\Debt\Presenter\Front;

use Nette\Application\Responses\TextResponse;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use QrCode\QRcode;
use Tracy\Debugger;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Debt\Model\Debt;

use function iban_set_checksum;

use const QR_ECLEVEL_H;

/**
 * Description of DebtPresenter
 */
class DefaultPresenter extends DebtBasePresenter
{
    public function actionDefault(?string $resource = null): void
    {
        if ($resource) {
            $this->setView("debt");
        }
    }

    public function renderDefault(): void
    {
        $this->template->debts = $this->debtManager->getListUserAllowed();
    }

    public function renderDebt(string $resource): void
    {
        $debtId = $this->parseIdFromWebname($resource);

        $debt = $this->loadDebt($debtId);

        $this->template->debt = $debt;
        $this->template->userListWithTeam = $this->userManager->getByIdWithTeam();

        $this->template->payeeList = $debt->getCanEdit() ? $this->getPayeeList() : $this->userManager->getByIdWithTeam();

        $this->template->countryList = $this->getCountryList();
    }

    public function renderImg(string $resource): void
    {
        $debtId = $this->parseIdFromWebname($resource);

        $debt = $this->loadDebt($debtId);

        $userList = $this->userManager->getByIdWithTeam();

        $payeeCallName = $debt->getPayeeId() == 0 ? "TEAM" : $userList[$debt->getPayeeId()]->getDisplayName();
        $payeeMail = $debt->getPayeeId() ? $userList[$debt->getPayeeId()]->getEmail() : "";

        $paymentString = $this->generateQRCodeString($payeeCallName, $payeeMail, $debt->getPayeeAccountNumber(), $debt->getAmount(), $debt->getVarcode(), $debt->getCaption(), $debt->getCurrencyIso(), $debt->getCountryIso());
        if (!$paymentString) {
            $this->sendResponse(new TextResponse("Insufficient data to create QR code"));
        }
        QRcode::png($paymentString, false, QR_ECLEVEL_H, 4, 4);
        $this->terminate();
    }

    /**
     * Load debt of exact ID or redirect to homepage and display warning message
     *
     * @param int $debtId
     * @return Debt
     */
    private function loadDebt(int $debtId): Debt
    {
        $debt = $this->debtManager->getById($debtId);
        if (!$debt instanceof Debt) {
            $this->flashMessage($this->translator->translate("debt.debt", 1) . " $debtId " . $this->translator->translate("common.alerts.notFound", 1));
            $this->redirect(":Core:Default:");
        }
        assert($debt instanceof Debt);
        return $debt;
    }

    public function renderNew(): void
    {
        $this->template->debt = (new Debt())
                ->setAmount(1)
                ->setCurrencyIso("CZK")
                ->setCountryIso("CZ")
                ->setCaption("")
                ->setCreated((new DateTime())->format(BaseModel::DATE_ENG_FORMAT))
                ->setDebtorId(null)
                ->setDebtorType("user")
                ->setPayeeId($this->user->getId())
                ->setPayeeType("user")
                ->setDebtDate((new DateTime())->format(BaseModel::DATE_ENG_FORMAT))
                ->setPayeeAccountNumber("")
                ->setVarcode(null)
                ->setCanRead(true)
                ->setCanEdit(true)
                ->setCanSetSentDate(false)
                ->setPaymentSent(null)
                ->setPaymentReceived(null);

        $this->template->userListWithTeam = $this->userManager->getByIdWithTeam();
        $this->template->payeeList = $this->getPayeeList();
        $this->template->countryList = $this->getCountryList();
    }

    private function generateQRCodeString(string $payeeCallName, $payeeEmail, $accountNumber, $amount, $varcode, string $message, $currencyISO = "CZK", $countryISO = "CZ"): ?string
    {
        $accPrefix = null;
        $accountNumberBody = $accountNumber;

        if (empty($accountNumber) || !str_contains($accountNumber, "/")) {
            return null;
        }

        if (strpos($accountNumber, "-")) {
            $accNumberData = explode("-", $accountNumber);
            $accPrefix = $accNumberData[0];
            $accountNumberBody = $accNumberData[1];
        }

        $accNumberBodyData = explode("/", $accountNumberBody);

        $accBody = $accNumberBodyData[0];
        $bankCode = $accNumberBodyData[1];

        if (empty($accBody)) {
            return null;
        }

        if (empty($bankCode)) {
            return null;
        }

        $iban = iban_set_checksum($countryISO . "00" . $bankCode . sprintf("%06s", $accPrefix) . sprintf("%010s", $accBody));

        $payment = [];
        $payment["ACC"] = substr($iban, 0, 46);
        $payment["AM"] = substr(number_format($amount, 2, ".", ""), 0, 10);
        $payment["CC"] = $currencyISO;
        $payment["RN"] = substr(strtoupper(Strings::toAscii($payeeCallName)), 0, 35);
        $payment["X-VS"] = substr(strtoupper((string) $varcode), 0, 10);
        $payment["DT"] = date("Ymd");
        $payment["MSG"] = substr(strtoupper(Strings::toAscii($message)), 0, 60);
        $payment["NT"] = "E";
        $payment["NTA"] = substr($payeeEmail, 0, 320);

        $paymentString = "SPD*1.0";

        foreach ($payment as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $paymentString .= "*$key:$value";
        }

        return rtrim($paymentString, "*");
    }

    public function handleDebtCreate(): void
    {
        $bind = $this->getRequest()->getPost();

        $createdDebt = $this->debtManager->create($bind["changes"]);
        assert($createdDebt instanceof Debt);

        $this->flashMessage($this->translator->translate("common.alerts.debtAdded"), "success");

        $this->redirect(":Debt:Default:", $createdDebt->getWebName());
    }

    public function handleDebtEdit(): void
    {
        $bind = $this->getRequest()->getPost();
        $this->editDebt($bind);
    }

    public function handleDebtDelete(string $resource): void
    {
        $debtId = $this->parseIdFromWebname($resource);
        $this->debtManager->delete($debtId);
        $this->redirect(":Core:Default:");
    }

    private function editDebt($bind): void
    {
        $this->debtManager->update($bind["changes"], $bind["id"]);
    }
}
