<?php

namespace Tymy\Module\Debt\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of DebtMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 29. 11. 2020
 */
class DebtMapper extends BaseMapper
{
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::datetime()->withPropertyAndColumn("created", false, false),
            Field::int()->withColumn("created_user_id")->setProperty("createdUserId"),
            Field::float()->withPropertyAndColumn("amount", true),
            Field::string()->withColumn("currency_iso")->setProperty("currencyIso"),
            Field::string()->withColumn("country_iso")->setProperty("countryIso"),
            Field::int()->withColumn("debtor_id")->setProperty("debtorId"),
            Field::string()->withColumn("debtor_type")->setProperty("debtorType"),
            Field::int()->withColumn("payee_id")->setProperty("payeeId"),
            Field::string()->withColumn("payee_type")->setProperty("payeeType"),
            Field::string()->withColumn("payee_account_number")->setProperty("payeeAccountNumber"),
            Field::string()->withPropertyAndColumn("varcode"),
            Field::datetime()->withColumn("debt_date")->setProperty("debtDate"),
            Field::string()->withPropertyAndColumn("caption", true)->setNonempty(),
            Field::datetime()->withColumn("payment_sent")->setProperty("paymentSent"),
            Field::datetime()->withColumn("payment_received")->setProperty("paymentReceived"),
            Field::float()->withPropertyAndColumn("note"),
        ];
    }
}