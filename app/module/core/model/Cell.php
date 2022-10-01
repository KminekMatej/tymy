<?php

namespace Tymy\Module\Core\Model;

/**
 * Description of Cell
 *
 * @author kminekmatej, 12. 7. 2022, 22:58:19
 */
class Cell
{
    private const TYPE_DETAIL = "detail";
    private ?string $href = null;
    private ?string $class = null;
    private ?string $title = null;
    private ?string $style = null;

    public function __construct(private string $type)
    {
    }

    public static function detail(string $href): self
    {
        return (new Cell(self::TYPE_DETAIL))
                ->setHref($href);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getHref(): ?string
    {
        return $this->href;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function setHref(?string $href)
    {
        $this->href = $href;
        return $this;
    }

    public function setClass(?string $class)
    {
        $this->class = $class;
        return $this;
    }

    public function setTitle(?string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function setStyle(?string $style)
    {
        $this->style = $style;
        return $this;
    }
}
