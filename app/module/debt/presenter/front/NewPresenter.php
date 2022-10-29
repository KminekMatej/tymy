<?php

namespace Tymy\Module\Debt\Presenter\Front;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Debt\Manager\DebtManager;
use Tymy\Module\Debt\Model\Debt;

/**
 * Description of NewPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 10. 2. 2020
 */
class NewPresenter extends DebtBasePresenter
{
    /** @inject */
    public DebtManager $debtManager;

    public function renderDefault(): void
    {
        $this->template->debt = (new Debt())
            ->setAmount(1)
            ->setCurrencyIso("CZK")
            ->setCountryIso("CZ")
            ->setCaption("")
            ->setCreated(new DateTime())
            ->setDebtorId(null)
            ->setDebtorType("user")
            ->setPayeeId($this->user->getId())
            ->setPayeeType("user")
            ->setDebtDate(new DateTime())
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

    public function handleDebtCreate(): void
    {
        $bind = $this->getRequest()->getPost();

        try {
            /* @var $createdDebt Debt */
            $createdDebt = $this->debtManager->create($bind["changes"]);
            $this->flashMessage($this->translator->translate("common.alerts.debtAdded"), "success");
            $this->redirect(":Debt:Default:", $createdDebt->getWebName());
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
        }
    }
}
