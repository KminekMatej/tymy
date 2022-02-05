<?php

namespace Tymy\Module\Debt\Presenter\Front;

use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use QrCode\QRcode;
use Tapi\TapiObject;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Debt\Model\Debt;

use const QR_ECLEVEL_H;

use function iban_set_checksum;

/**
 * Description of DebtPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 10. 2. 2020
 */
class DefaultPresenter extends DebtBasePresenter
{
    public function actionDefault(?string $resource = null)
    {
        if ($resource) {
            $this->setView("debt");
        }
    }

    public function renderDefault()
    {
        $this->template->debts = $this->debtManager->getListUserAllowed();
    }

    public function renderDebt(string $resource)
    {
        $debtId = $this->parseIdFromWebname($resource);

        /* @var $debt Debt */
        $debt = $this->debtManager->getById($debtId);
        $this->template->debt = $debt;
        $this->template->userListWithTeam = $this->userManager->getByIdWithTeam();

        if ($debt->getCanEdit()) {
            $this->template->payeeList = $this->getPayeeList();
        } else {
            $this->template->payeeList = $this->userManager->getByIdWithTeam();
        }

        $this->template->countryList = $this->getCountryList();
    }

    public function renderImg(string $resource)
    {
        $debtId = $this->parseIdFromWebname($resource);

        /* @var $debt Debt */
        $debt = $this->debtManager->getById($debtId);
        $userList = $this->userManager->getByIdWithTeam();

        $payeeCallName = $debt->getPayeeId() == 0 ? "TEAM" : $userList[$debt->getPayeeId()]->getDisplayName();
        $payeeMail = $debt->getPayeeId() ? $userList[$debt->getPayeeId()]->getEmail() : "";

        $paymentString = $this->generateQRCodeString($payeeCallName, $payeeMail, $debt->getPayeeAccountNumber(), $debt->getAmount(), $debt->getVarcode(), $debt->getCaption(), $debt->getCurrencyIso(), $debt->getCountryIso());
        QRcode::png($paymentString, false, QR_ECLEVEL_H, 4, 4);
    }

    public function renderNew()
    {
        $newDebt = (new Debt())
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
        $this->template->debt = $newDebt;

        $this->template->userListWithTeam = $this->userManager->getByIdWithTeam();
        $this->template->payeeList = $this->getPayeeList();
        $this->template->countryList = $this->getCountryList();
    }

    private function generateQRCodeString($payeeCallName, $payeeEmail, $accountNumber, $amount, $varcode, $message, $currencyISO = "CZK", $countryISO = "CZ")
    {
        $accPrefix = null;
        $accountNumberBody = $accountNumber;

        if (strpos($accountNumber, "/") === false) {
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

    public function handleDebtCreate()
    {
        $bind = $this->getRequest()->getPost();

        /* @var $createdDebt Debt */
        $createdDebt = $this->debtManager->create($bind["changes"]);

        $this->flashMessage($this->translator->translate("common.alerts.debtAdded"), "success");

        $this->redirect(":Debt:Default:", $createdDebt->getWebName());
    }

    public function handleDebtEdit()
    {
        $bind = $this->getRequest()->getPost();
        $this->editDebt($bind);
    }

    public function handleDebtDelete(string $resource)
    {
        $debtId = $this->parseIdFromWebname($resource);
        $this->debtManager->delete($debtId);
        $this->redirect(":Core:Default:");
    }

    private function editDebt($bind)
    {
        $this->debtManager->update($bind["changes"], $bind["id"]);
    }
}
