<?php

namespace Tymy\App\Presenters;

use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use QrCode\QRcode;
use Tapi\TapiObject;
use Tymy\Module\Debt\Manager\DebtManager;
use Tymy\Module\Debt\Model\Debt;
use Tymy\Module\Permission\Model\Privilege;
use const QR_ECLEVEL_H;
use function iban_set_checksum;

/**
 * Description of DebtPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 10. 2. 2020
 */
class DebtPresenter extends SecuredPresenter
{

    /** @inject */
    public DebtManager $debtManager;

    public function startup()
    {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("debt.debt", 2), "link" => $this->link("Debt:")]]);
    }

    public function renderDefault()
    {
        $this->template->debts = $this->debtManager->getListUserAllowed();
    }

    public function renderDebtImg($dluh)
    {
        $debtId = $this->parseIdFromWebname($dluh);

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
                ->setCreated((new DateTime())->format(TapiObject::MYSQL_DATE))
                ->setDebtorId(null)
                ->setDebtorType("user")
                ->setPayeeId($this->user->getId())
                ->setPayeeType("user")
                ->setDebtDate((new DateTime())->format(TapiObject::MYSQL_DATE))
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

    /**
     * Get list of possible payees - always Me, but if user is permitted to manage team debts, also TEAM mocked user
     * 
     * @return array
     */
    private function getPayeeList(): array
    {
        $payeeList = [$this->userManager->getById($this->user->getId())];
        if ($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("DEBTS_TEAM"))) {
            $payeeList[] = $this->userManager->mockTeamUser();
        }
        return $payeeList;
    }

    private function generateQRCodeString($payeeCallName, $payeeEmail, $accountNumber, $amount, $varcode, $message, $currencyISO = "CZK", $countryISO = "CZ")
    {
        $accPrefix = null;
        $accountNumberBody = $accountNumber;

        if (strpos($accountNumber, "/") === FALSE) {
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
            if (empty($value))
                continue;
            $paymentString .= "*$key:$value";
        }

        return rtrim($paymentString, "*");
    }

    public function renderDebt($dluh)
    {
        $debtId = $this->parseIdFromWebname($dluh);

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

    public function handleDebtCreate()
    {
        $bind = $this->getRequest()->getPost();

        $createdDebt = $this->debtManager->create($bind["changes"]);

        $this->flashMessage($this->translator->translate("common.alerts.debtAdded"), "success");

        $this->redirect("Debt:debt", $createdDebt->getId());
    }

    public function handleDebtEdit()
    {
        $bind = $this->getRequest()->getPost();
        $this->editDebt($bind);
    }

    public function handleDebtDelete()
    {
        $this->debtManager->delete($bind["id"]);
    }

    private function editDebt($bind)
    {
        $this->debtManager->update($bind["changes"], $bind["id"]);
    }

    private function getCountryList()
    {
        return [
            'AF' => 'Afghánistán',
            'AX' => 'Ålandy',
            'AL' => 'Albánie',
            'DZ' => 'Alžírsko',
            'AS' => 'Americká Samoa',
            'VI' => 'Americké Panenské ostrovy',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarktida',
            'AG' => 'Antigua a Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Arménie',
            'AW' => 'Aruba',
            'AC' => 'Ascension',
            'AU' => 'Austrálie',
            'AZ' => 'Ázerbájdžán',
            'BS' => 'Bahamy',
            'BH' => 'Bahrajn',
            'BD' => 'Bangladéš',
            'BB' => 'Barbados',
            'BE' => 'Belgie',
            'BZ' => 'Belize',
            'BY' => 'Bělorusko',
            'BJ' => 'Benin',
            'BM' => 'Bermudy',
            'BT' => 'Bhútán',
            'BO' => 'Bolívie',
            'BA' => 'Bosna a Hercegovina',
            'BW' => 'Botswana',
            'BR' => 'Brazílie',
            'IO' => 'Britské indickooceánské území',
            'VG' => 'Britské Panenské ostrovy',
            'BN' => 'Brunej',
            'BG' => 'Bulharsko',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'EA' => 'Ceuta a Melilla',
            'CK' => 'Cookovy ostrovy',
            'CW' => 'Curaçao',
            'TD' => 'Čad',
            'ME' => 'Černá Hora',
            'CZ' => 'Česko',
            'CN' => 'Čína',
            'DK' => 'Dánsko',
            'DG' => 'Diego García',
            'DM' => 'Dominika',
            'DO' => 'Dominikánská republika',
            'DJ' => 'Džibutsko',
            'EG' => 'Egypt',
            'EC' => 'Ekvádor',
            'ER' => 'Eritrea',
            'EE' => 'Estonsko',
            'ET' => 'Etiopie',
            'FO' => 'Faerské ostrovy',
            'FK' => 'Falklandské ostrovy',
            'FJ' => 'Fidži',
            'PH' => 'Filipíny',
            'FI' => 'Finsko',
            'FR' => 'Francie',
            'GF' => 'Francouzská Guyana',
            'TF' => 'Francouzská jižní území',
            'PF' => 'Francouzská Polynésie',
            'GA' => 'Gabon',
            'GM' => 'Gambie',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GD' => 'Grenada',
            'GL' => 'Grónsko',
            'GE' => 'Gruzie',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HN' => 'Honduras',
            'HK' => 'Hongkong – ZAO Číny',
            'CL' => 'Chile',
            'HR' => 'Chorvatsko',
            'IN' => 'Indie',
            'ID' => 'Indonésie',
            'IQ' => 'Irák',
            'IR' => 'Írán',
            'IE' => 'Irsko',
            'IS' => 'Island',
            'IT' => 'Itálie',
            'IL' => 'Izrael',
            'JM' => 'Jamajka',
            'JP' => 'Japonsko',
            'YE' => 'Jemen',
            'JE' => 'Jersey',
            'ZA' => 'Jihoafrická republika',
            'GS' => 'Jižní Georgie a Jižní Sandwichovy ostrovy',
            'KR' => 'Jižní Korea',
            'SS' => 'Jižní Súdán',
            'JO' => 'Jordánsko',
            'KY' => 'Kajmanské ostrovy',
            'KH' => 'Kambodža',
            'CM' => 'Kamerun',
            'CA' => 'Kanada',
            'IC' => 'Kanárské ostrovy',
            'CV' => 'Kapverdy',
            'BQ' => 'Karibské Nizozemsko',
            'QA' => 'Katar',
            'KZ' => 'Kazachstán',
            'KE' => 'Keňa',
            'KI' => 'Kiribati',
            'CC' => 'Kokosové ostrovy',
            'CO' => 'Kolumbie',
            'KM' => 'Komory',
            'CG' => 'Kongo – Brazzaville',
            'CD' => 'Kongo – Kinshasa',
            'XK' => 'Kosovo',
            'CR' => 'Kostarika',
            'CU' => 'Kuba',
            'KW' => 'Kuvajt',
            'CY' => 'Kypr',
            'KG' => 'Kyrgyzstán',
            'LA' => 'Laos',
            'LS' => 'Lesotho',
            'LB' => 'Libanon',
            'LR' => 'Libérie',
            'LY' => 'Libye',
            'LI' => 'Lichtenštejnsko',
            'LT' => 'Litva',
            'LV' => 'Lotyšsko',
            'LU' => 'Lucembursko',
            'MO' => 'Macao – ZAO Číny',
            'MG' => 'Madagaskar',
            'HU' => 'Maďarsko',
            'MY' => 'Malajsie',
            'MW' => 'Malawi',
            'MV' => 'Maledivy',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MA' => 'Maroko',
            'MH' => 'Marshallovy ostrovy',
            'MQ' => 'Martinik',
            'MU' => 'Mauricius',
            'MR' => 'Mauritánie',
            'YT' => 'Mayotte',
            'UM' => 'Menší odlehlé ostrovy USA',
            'MX' => 'Mexiko',
            'FM' => 'Mikronésie',
            'MD' => 'Moldavsko',
            'MC' => 'Monako',
            'MN' => 'Mongolsko',
            'MS' => 'Montserrat',
            'MZ' => 'Mosambik',
            'MM' => 'Myanmar (Barma)',
            'NA' => 'Namibie',
            'NR' => 'Nauru',
            'DE' => 'Německo',
            'NP' => 'Nepál',
            'NE' => 'Niger',
            'NG' => 'Nigérie',
            'NI' => 'Nikaragua',
            'NU' => 'Niue',
            'NL' => 'Nizozemsko',
            'NF' => 'Norfolk',
            'NO' => 'Norsko',
            'NC' => 'Nová Kaledonie',
            'NZ' => 'Nový Zéland',
            'OM' => 'Omán',
            'IM' => 'Ostrov Man',
            'PK' => 'Pákistán',
            'PW' => 'Palau',
            'PS' => 'Palestinská území',
            'PA' => 'Panama',
            'PG' => 'Papua-Nová Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PN' => 'Pitcairnovy ostrovy',
            'CI' => 'Pobřeží slonoviny',
            'PL' => 'Polsko',
            'PR' => 'Portoriko',
            'PT' => 'Portugalsko',
            'AT' => 'Rakousko',
            'RE' => 'Réunion',
            'GQ' => 'Rovníková Guinea',
            'RO' => 'Rumunsko',
            'RU' => 'Rusko',
            'RW' => 'Rwanda',
            'GR' => 'Řecko',
            'PM' => 'Saint-Pierre a Miquelon',
            'SV' => 'Salvador',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'SA' => 'Saúdská Arábie',
            'SN' => 'Senegal',
            'KP' => 'Severní Korea',
            'MK' => 'Severní Makedonie',
            'MP' => 'Severní Mariany',
            'SC' => 'Seychely',
            'SL' => 'Sierra Leone',
            'XA' => 'simulovaná diakritika',
            'XB' => 'simulovaný obousměrný zápis',
            'SG' => 'Singapur',
            'SK' => 'Slovensko',
            'SI' => 'Slovinsko',
            'SO' => 'Somálsko',
            'AE' => 'Spojené arabské emiráty',
            'GB' => 'Spojené království',
            'US' => 'Spojené státy',
            'RS' => 'Srbsko',
            'LK' => 'Srí Lanka',
            'CF' => 'Středoafrická republika',
            'SD' => 'Súdán',
            'SR' => 'Surinam',
            'SH' => 'Svatá Helena',
            'LC' => 'Svatá Lucie',
            'BL' => 'Svatý Bartoloměj',
            'KN' => 'Svatý Kryštof a Nevis',
            'MF' => 'Svatý Martin (Francie)',
            'SX' => 'Svatý Martin (Nizozemsko)',
            'ST' => 'Svatý Tomáš a Princův ostrov',
            'VC' => 'Svatý Vincenc a Grenadiny',
            'SZ' => 'Svazijsko',
            'SY' => 'Sýrie',
            'SB' => 'Šalamounovy ostrovy',
            'ES' => 'Španělsko',
            'SJ' => 'Špicberky a Jan Mayen',
            'SE' => 'Švédsko',
            'CH' => 'Švýcarsko',
            'TJ' => 'Tádžikistán',
            'TZ' => 'Tanzanie',
            'TH' => 'Thajsko',
            'TW' => 'Tchaj-wan',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad a Tobago',
            'TA' => 'Tristan da Cunha',
            'TN' => 'Tunisko',
            'TR' => 'Turecko',
            'TM' => 'Turkmenistán',
            'TC' => 'Turks a Caicos',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukrajina',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistán',
            'CX' => 'Vánoční ostrov',
            'VU' => 'Vanuatu',
            'VA' => 'Vatikán',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'TL' => 'Východní Timor',
            'WF' => 'Wallis a Futuna',
            'ZM' => 'Zambie',
            'EH' => 'Západní Sahara',
            'ZW' => 'Zimbabwe',
        ];
    }

}