<?php

namespace Tymy\Module\Core\Model;

/**
 * Description of Field
 *
 * @author kminekmatej, 25.4.2019
 */
class Field
{
    public const TYPE_INT = "int";
    public const TYPE_FLOAT = "float";
    public const TYPE_STRING = "string";
    public const TYPE_DATETIME = "datetime";

    /** @var string */
    private $column;

    /** @var string */
    private $property;

    /** @var string
     * @deprecated
     */
    private $alias;

    /** @var bool */
    private $mandatory = false;

    /** @var bool */
    private $nonempty = false;

    /** @var bool */
    private $changeable = true;

    /** @var string */
    private $type = self::TYPE_STRING;

    /**
     * Return new field as int type
     * @return Field
     */
    public static function int()
    {
        return (new Field())->setType(self::TYPE_INT);
    }

    /**
     * Return new field as float type
     * @return Field
     */
    public static function float()
    {
        return (new Field())->setType(self::TYPE_FLOAT);
    }

    /**
     * Return new field as string type
     * @return Field
     */
    public static function string()
    {
        return (new Field())->setType(self::TYPE_STRING);
    }

    /**
     * Return new field as datetime type
     * @return Field
     */
    public static function datetime()
    {
        return (new Field())->setType(self::TYPE_DATETIME);
    }

    /**
     * Return field, fill database column name and equal property name.
     * @param string $name Database column name and property name
     * @param bool $mandatory If field is mandatory, then value cannot be null
     * @param bool $changeable If field is not changeable, any future changes are prohibited
     * @return Field
     */
    public function withPropertyAndColumn($name, $mandatory = false, $changeable = true)
    {
        return $this->setColumn($name)
                        ->setProperty($name)
                        ->setMandatory($mandatory)
                        ->setChangeable($changeable);
    }

    /**
     * Return field, fill only database column name. Use setter to set property name.
     * @param string $name Database column name
     * @param bool $mandatory If field is mandatory, then value cannot be null
     * @param bool $changeable If field is not changeable, any future changes are prohibited
     * @return Field
     */
    public function withColumn($name, $mandatory = false, $changeable = true)
    {
        return $this->setColumn($name)
                        ->setMandatory($mandatory)
                        ->setChangeable($changeable);
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getProperty()
    {
        return $this->property;
    }

    /** @deprecated */
    public function getAlias()
    {
        return $this->alias;
    }

    public function getMandatory()
    {
        return $this->mandatory;
    }

    public function getChangeable()
    {
        return $this->changeable;
    }

    public function setColumn($column)
    {
        $this->column = $column;
        return $this;
    }

    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }

    /** @deprecated */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
        return $this;
    }

    public function setChangeable($changeable)
    {
        $this->changeable = $changeable;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getNonempty()
    {
        return $this->nonempty;
    }

    public function setNonempty($nonempty = true)
    {
        $this->nonempty = $nonempty;
        return $this;
    }
}
