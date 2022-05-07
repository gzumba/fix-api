<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Dto\NewOrder;
use App\Dto\WeekSales;
use App\Service\ApiOrderService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\ScopingHttpClient;

class ApiOrderServiceTest extends TestCase
{
    private ApiOrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $client = ScopingHttpClient::forBaseUri(HttpClient::create(), 'https://careers-api.fixably.com/');

        $code = $_ENV['API_CODE'] ?? '<code>';

        if ($code === '<code>') {
            self::fail('Add a code for Token Fetching to .env');
        }

        $this->orderService = new ApiOrderService($client, $code);
        // this is enough to verify the code works and makes the tests faster:
        $this->orderService->setMaxPages(2);
    }

    public function testFetchingOrderStatusCounts(): void
    {
        $res = $this->orderService->fetchStatusCounts();

        self::assertNotEmpty($res);
        $prev_count = PHP_INT_MAX;
        foreach ($res as $status => $count) {
            self::assertLessThanOrEqual($prev_count, $count, "Should be descending order");
            $prev_count = $count;
        }
    }

    public function testFetchingBrandOrders(): void
    {
        $res = $this->orderService->fetchBrandOrders('non-existing-brand');
        self::assertCount(0, $res, "Should have nothing");

        $res = $this->orderService->fetchBrandOrders('iPhone');
        self::assertGreaterThan(0, $res->size(), "Should have some iPhones");
    }

    public function testFetchingWeeklySales(): void
    {
        $start = new \DateTimeImmutable('2020-1-1');
        $end = new \DateTimeImmutable('2021-1-1');

        $res = $this->orderService->fetchWeeklySales($start, $end);

        self::assertGreaterThan(0, $res->size(), "Should have some sales");
        $prev_week = '';
        foreach ($res as $sales) {
            self::assertInstanceOf(WeekSales::class, $sales);
            self::assertGreaterThan($prev_week, $sales->week);
            self::assertGreaterThan(0, $sales->amount);
            $prev_week = $sales->week;
        }
    }

    public function testSubmittingAnOrder(): void
    {
        $new_order = new NewOrder('Apple', 'iPhone', 'Phone', 'Broken screen');

        $order = $this->orderService->submitOrder($new_order);

        self::assertEquals('Apple', $order->deviceManufacturer);
        self::assertCount(1, $order->notes, "Should have a note");
        $note = reset($order->notes);
        self::assertEquals('Broken screen', $note->description, "Note should match what we submitted");
    }

    public function testSubmittingAnOrderWithBadType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $o = new NewOrder('Apple', 'iPhone', 'Something', 'Broken screen');
    }
}