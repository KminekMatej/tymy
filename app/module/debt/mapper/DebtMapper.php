<?php

namespace Tymy\Module\Debt\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of DebtMapper
 */
class DebtMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::datetime()->withPropertyAndColumn("created", false, false),
            Field::int()->withColumn("created_user_id", false, false)->setProperty("createdUserId"),
            Field::float()->withPropertyAndColumn("amount", true),
            Field::string(3)->withColumn("currency_iso")->setProperty("currencyIso"),
            Field::string(3)->withColumn("country_iso")->setProperty("countryIso"),
            Field::int()->withColumn("debtor_id")->setProperty("debtorId"),
            Field::string()->withColumn("debtor_type")->setProperty("debtorType")->setEnum(['user', 'team', 'other']),
            Field::int()->withColumn("payee_id")->setProperty("payeeId"),
            Field::string()->withColumn("payee_type")->setProperty("payeeType")->setEnum(['user', 'team', 'other']),
            Field::string(32)->withColumn("payee_account_number")->setProperty("payeeAccountNumber"),
            Field::int()->withPropertyAndColumn("varcode"),
            Field::datetime()->withColumn("debt_date")->setProperty("debtDate"),
            Field::string(255)->withPropertyAndColumn("caption", true)->setNonempty(),
            Field::datetime()->withColumn("payment_sent")->setProperty("paymentSent"),
            Field::datetime()->withColumn("payment_received")->setProperty("paymentReceived"),
            Field::string()->withPropertyAndColumn("note"),
        ];
    }
}
