<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\NewOrder;
use App\Dto\Order;
use App\Dto\OrderDetails;
use App\Dto\WeekSales;
use DusanKasan\Knapsack\Collection;

interface OrderService
{
    /**
     * Fetch a Collection of order status counts
     * @return array<string, int>
     */
    public function fetchStatusCounts(): iterable;

    /**
     * @return iterable<Order>|Collection
     */
    public function fetchBrandOrders(string $brand): Collection;

    /**
     * @return iterable<WeekSales>
     */
    public function fetchWeeklySales(\DateTimeInterface $start, \DateTimeInterface $end): iterable;

    public function submitOrder(NewOrder $new_order): OrderDetails;
}