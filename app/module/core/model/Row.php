<?php

namespace Tymy\Module\Core\Model;

use Iterator;

/**
 * Description of Row
 */
class Row implements Iterator
{
    private ?string $style = null;
    private int $position = 0;

    /**
     * @param mixed[] $cells
     * @param mixed[] $classes
     */
    public function __construct(private array $cells, private array $classes = [])
    {
    }

    /**
     * @return mixed[]
     */
    public function getCells(): array
    {
        return $this->cells;
    }

    /**
     * @return mixed[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setStyle(?string $style): static
    {
        $this->style = $style;
        return $this;
    }

    public function classStr(): string
    {
        return implode(",", $this->classes);
    }

    public function addCell(mixed $cell): static
    {
        $this->cells[] = $cell;
        return $this;
    }

    public function addClass(string $class): static
    {
        $this->classes[] = $class;
        return $this;
    }

    public function current(): mixed
    {
        return $this->cells[$this->position];
    }

    public function key(): int
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
