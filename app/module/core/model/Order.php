<?php

namespace Tymy\Module\Core\Model;

/**
 * Description of Order
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 15. 2. 2021
 */
class Order
{
    private string $column;
    private string $order;

    public function __construct(string $column, string $order)
    {
        $this->column = $column;
        $this->order = strtoupper($order) == "ASC" ? "ASC" : "DESC";
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public static function toString(array $orders): string
    {
        $outOrders = [];

        foreach ($orders as $order) {
            /* @var $order Order */
            $outOrders[] = "{$order->getColumn()} {$order->getOrder()}";
        }

        return join(", ", $outOrders);
    }
}
