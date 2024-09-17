<?php

namespace Tymy\Module\Core\Model;

/**
 * Description of Order
 */
class Order
{
    private string $order;

    public function __construct(private string $column, string $order)
    {
        $this->order = strtoupper($order) == "ASC" ? "ASC" : "DESC";
    }

    public static function toString(array $orders): string
    {
        $outOrders = [];

        foreach ($orders as $order) {
            assert($order instanceof Order);
            $outOrders[] = "{$order->getColumn()} {$order->getOrder()}";
        }

        return implode(", ", $outOrders);
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getOrder(): string
    {
        return $this->order;
    }
}
