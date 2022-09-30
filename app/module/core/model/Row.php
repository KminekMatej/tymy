<?php

namespace Tymy\Module\Core\Model;

use Iterator;

/**
 * Description of Row
 *
 * @author kminekmatej, 30. 9. 2022, 23:57:39
 */
class Row implements Iterator
{
    private array $cells = [];
    private array $classes = [];
    private ?string $style = null;
    private $position = 0;

    public function __construct(array $cells, array $classes = [])
    {
        $this->cells = $cells;
        $this->classes = $classes;
    }

    public function getCells(): array
    {
        return $this->cells;
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setStyle(?string $style)
    {
        $this->style = $style;
        return $this;
    }

    public function classStr()
    {
        return join(",", $this->classes);
    }

    public function addCell(mixed $cell)
    {
        $this->cells[] = $cell;
        return $this;
    }

    public function addClass(string $class)
    {
        $this->classes[] = $class;
        return $this;
    }

    public function current()
    {
        return $this->cells[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->cells[$this->position]);
    }
}
