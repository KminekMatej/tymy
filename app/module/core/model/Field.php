<?php

namespace Tymy\Module\Core\Model;

/**
 * Description of Field
 */
class Field
{
    public const TYPE_INT = "int";
    public const TYPE_FLOAT = "float";
    public const TYPE_STRING = "string";
    public const TYPE_DATETIME = "datetime";
    public const TYPE_DATE = "date";

    private ?string $column = null;
    private ?string $property = null;
    private bool $mandatory = false;
    private bool $nonempty = false;
    private bool $changeable = true;
    private ?int $maxLength = null;
    private ?array $enum = null;
    private string $type = self::TYPE_STRING;

    /**
     * Return new field as int type
     */
    public static function int(): \Tymy\Module\Core\Model\Field
    {
        return (new Field())->setType(self::TYPE_INT);
    }

    /**
     * Return new field as float type
     */
    public static function float(): \Tymy\Module\Core\Model\Field
    {
        return (new Field())->setType(self::TYPE_FLOAT);
    }

    /**
     * Return new field as string type
     */
    public static function string(?int $maxLength = null): \Tymy\Module\Core\Model\Field
    {
        return (new Field())->setType(self::TYPE_STRING)->setMaxLength($maxLength);
    }

    /**
     * Return new field as datetime type
     */
    public static function datetime(): \Tymy\Module\Core\Model\Field
    {
        return (new Field())->setType(self::TYPE_DATETIME);
    }

    /**
     * Return new field as date type
     *
     * @return Field
     */
    public static function date()
    {
        return (new Field())->setType(self::TYPE_DATE);
    }

    /**
     * Return field, fill database column name and equal property name.
     *
     * @param string $name Database column name and property name
     * @param bool $mandatory If field is mandatory, then value cannot be null
     * @param bool $changeable If field is not changeable, any future changes are prohibited
     */
    public function withPropertyAndColumn(string $name, bool $mandatory = false, bool $changeable = true): static
    {
        return $this->setColumn($name)
                ->setProperty($name)
                ->setMandatory($mandatory)
                ->setChangeable($changeable);
    }

    /**
     * Return field, fill only database column name. Use setter to set property name.
     *
     * @param string $name Database column name
     * @param bool $mandatory If field is mandatory, then value cannot be null
     * @param bool $changeable If field is not changeable, any future changes are prohibited
     */
    public function withColumn(string $name, bool $mandatory = false, bool $changeable = true): static
    {
        return $this->setColumn($name)
                ->setMandatory($mandatory)
                ->setChangeable($changeable);
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getMandatory(): bool
    {
        return $this->mandatory;
    }

    public function getChangeable(): bool
    {
        return $this->changeable;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function getEnum(): ?array
    {
        return $this->enum;
    }

    public function setColumn(string $column): static
    {
        $this->column = $column;
        return $this;
    }

    public function setProperty(string $property): static
    {
        $this->property = $property;
        return $this;
    }

    public function setMandatory(bool $mandatory): static
    {
        $this->mandatory = $mandatory;
        return $this;
    }

    public function setChangeable(bool $changeable): static
    {
        $this->changeable = $changeable;
        return $this;
    }

    public function setMaxLength(?int $maxLength)
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    public function setEnum(?array $enum)
    {
        $this->enum = $enum;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getNonempty(): bool
    {
        return $this->nonempty;
    }

    public function setNonempty(bool $nonempty = true): static
    {
        $this->nonempty = $nonempty;
        return $this;
    }
}
