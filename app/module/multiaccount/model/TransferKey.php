<?php

namespace Tymy\Module\Multiaccount\Model;

use JsonSerializable;
use Tymy\Module\Core\Model\BaseModel;

/**
 * Description of TransferKey
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 7. 2. 2021
 */
class TransferKey extends BaseModel implements JsonSerializable
{
    public const MODULE = "multiaccount";
    public const TABLE = "multi_accounts";

    /** @var string 40-char long transfer key */
    private string $transferKey;

    /** @var int target team user's id */
    private int $uid;

    public function __construct(string $transferKey, int $uid)
    {
        $this->transferKey = $transferKey;
        $this->uid = $uid;
    }

    public function jsonSerialize()
    {
        return [
            "transferKey" => $this->transferKey,
            "uid" => $this->uid,
        ];
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return []; //no mapping needed here
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}