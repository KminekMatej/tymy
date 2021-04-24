<?php

namespace Tymy\Module\Poll\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of PollMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 20. 12. 2020
 */
class PollMapper extends BaseMapper
{
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::int()->withColumn("usr_cre")->setProperty("createdById")->setChangeable(false),
            Field::int()->withColumn("dat_cre")->setProperty("createdAt")->setChangeable(false),
            Field::int()->withColumn("usr_mod")->setProperty("updatedById")->setChangeable(false),
            Field::datetime()->withColumn("dat_mod")->setProperty("updatedAt")->setChangeable(false),
            Field::string()->withPropertyAndColumn("caption"),
            Field::string()->withColumn("descr")->setProperty("description"),
            Field::int()->withColumn("min_items")->setProperty("minItems"),
            Field::int()->withColumn("max_items")->setProperty("maxItems"),
            Field::int()->withColumn("changeable_votes")->setProperty("changeableVotes"),
            Field::int()->withColumn("main_menu")->setProperty("mainMenu"),
            Field::int()->withColumn("anonymous_results")->setProperty("anonymousResults"),
            Field::string()->withColumn("show_results")->setProperty("showResults"),
            Field::string()->withPropertyAndColumn("status"),
            Field::string()->withColumn("result_rights")->setProperty("resultRightName"),
            Field::string()->withColumn("vote_rights")->setProperty("voteRightName"),
            Field::string()->withColumn("alien_vote_rights")->setProperty("alienVoteRightName"),
            Field::int()->withColumn("order_flag")->setProperty("orderFlag"),
        ];
    }
}
