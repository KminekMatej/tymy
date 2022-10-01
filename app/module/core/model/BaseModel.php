<?php

namespace Tymy\Module\Core\Model;

use JsonSerializable;
use Nette\Utils\DateTime;

/**
 * Description of BaseModel
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
abstract class BaseModel implements JsonSerializable
{
    public const DATE_FORMAT = "Y-m-d\TH:i:s.000\Z";
    public const TIME_FORMAT = "H:i:s";
    public const DATEINTERVAL_NO_SECS_FORMAT = "%H:%I";
    public const TIME_NO_SECS_FORMAT = "H:i";
    public const DATETIME_CZECH_FORMAT = "j.n.Y H:i:s";
    public const DATETIME_CZECH_NO_SECS_FORMAT = "j.n.Y H:i";
    public const DATE_CZECH_FORMAT = "j.n.Y";
    public const DAY_CZECH_FORMAT = "j.n";
    public const DATE_ENG_FORMAT = "Y-m-d";
    public const YEAR_MONTH = "Y-m";
    public const DATETIME_ENG_FORMAT = "Y-m-d H:i:s";
    public const DATETIME_ISO_FORMAT = "Y-m-d\TH:i:s";
    public const DATETIME_ISO_NO_SECS_FORMAT = "Y-m-d\TH:i:00";

    public const MAIL_REGEX = '/^(([^<>()\[\]\\\\.,;:\s@"]+(\.[^<>()\[\]\\\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
    public const B64_REGEX = '/^data:(\w+)\/(\w+);base64,(.*)/m';

    private int $id;
    private bool $hasMeta = true;    //defaultly set hasMeta to true. This is set to false during metaMap() inside _Manager function

    /** @return Field[] */
    abstract public function getScheme(): array;
    abstract public function getTable(): string;
    abstract public function getModule(): string;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function getHasMeta(): bool
    {
        return $this->hasMeta;
    }

    public function setHasMeta(bool $hasMeta)
    {
        $this->hasMeta = $hasMeta;
        return $this;
    }

    /**
     * Transform array of entities into jsonizable array
     *
     * @param BaseModel[] $entities
     * @return array
     */
    protected function arrayToJson($entities)
    {
        if (empty($entities)) {
            return [];
        }

        return array_map(fn($entity) => /* @var $entity BaseModel */
$entity->jsonSerialize(), $entities);
    }

    public function jsonSerialize()
    {
        $ret = [];
        foreach ($this->getScheme() as $field) {
            $getField = "get" . ucfirst($field->getProperty());
            $output = $this->$getField();
            $value = $output instanceof DateTime ? (clone $output)->setTimezone(new \DateTimeZone("UTC"))->format(self::DATE_FORMAT) : $output; //API takes datetime, stored in local timezone and prints it always in UTC timezone
            if ($field->getAlias()) {
                $ret[$field->getAlias()] = $value;
            } else {
                $ret[$field->getProperty()] = $value;
            }
        }
        return $ret;
    }
}
