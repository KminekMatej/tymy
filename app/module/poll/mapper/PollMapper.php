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
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::int()->withColumn("created_user_id")->setProperty("createdById")->setChangeable(false),
            Field::datetime()->withColumn("created")->setProperty("createdAt")->setChangeable(false),
            Field::int()->withColumn("updated_user_id")->setProperty("updatedById")->setChangeable(false),
            Field::datetime()->withColumn("updated")->setProperty("updatedAt")->setChangeable(false),
            Field::string(255)->withPropertyAndColumn("caption"),
            Field::string()->withColumn("descr")->setProperty("description"),
            Field::int()->withColumn("min_items")->setProperty("minItems"),
            Field::int()->withColumn("max_items")->setProperty("maxItems"),
            Field::int()->withColumn("changeable_votes")->setProperty("changeableVotes"),
            Field::int()->withColumn("anonymous_results")->setProperty("anonymousResults"),
            Field::string()->withColumn("show_results")->setProperty("showResults")->setEnum(['NEVER', 'ALWAYS', 'AFTER_VOTE', 'WHEN_CLOSED']),
            Field::string()->withPropertyAndColumn("status")->setEnum(['DESIGN', 'OPENED', 'CLOSED', 'HIDDEN']),
            Field::string(20)->withColumn("result_rights")->setProperty("resultRightName"),
            Field::string(20)->withColumn("vote_rights")->setProperty("voteRightName"),
            Field::string(20)->withColumn("alien_vote_rights")->setProperty("alienVoteRightName"),
            Field::int()->withColumn("order_flag")->setProperty("orderFlag"),
        ];
    }
}
