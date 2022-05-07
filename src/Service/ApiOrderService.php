<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\NewOrder;
use App\Dto\Order;
use App\Dto\OrderBill;
use App\Dto\OrderDetails;
use App\Dto\WeekSales;
use DusanKasan\Knapsack\Collection;
use Symfony\Contracts\HttpClient\HttpClientInterface;

const UNKNOWN_STATUS = 'UNKNOWN STATUS';

class ApiOrderService implements OrderService
{
    private const CODE = 57149595;
    const ERROR_INVALID_PAGE = 'Invalid page';
    private HttpClientInterface $client;
    private ?string $token = null;
    private ?int $max_pages = null;

    public function __construct(HttpClientInterface $fixablyClient)
    {
        $this->client = $fixablyClient;
    }

    public function setMaxPages(int $max_pages): void
    {
        $this->max_pages = $max_pages;
    }

    public function fetchStatusCounts(): iterable
    {
        $status_counts = Collection::from($this->fetchPages('/orders'))
            ->map(fn (array $page) => $page['results'])
            ->flatten(1)
            ->map(fn (array $order) => $this->mapStatus($order['status']))
            ->countBy(fn (string $status) => $status)
            ->sort(fn ($value1, $value2) => $value2 <=> $value1)
            ->toArray()
        ;

        return $status_counts;
    }

    /**
     * @return iterable<Order>
     */
    public function fetchBrandOrders(string $brand): Collection
    {
        return Collection::from($this->fetchPostPages('/search/devices', ['Criteria' => sprintf("*%s*", $brand)]))
            ->map(fn (array $page) => $page['results'])
            ->flatten(1)
            ->map(fn (array $row) => $this->buildOrder($row))
        ;
    }

    public function fetchWeeklySales(\DateTimeInterface $start, \DateTimeInterface $end)
    {
        $path = sprintf("/report/%s/%s", $start->format('Y-m-d'), $end->format('Y-m-d'));

        return Collection::from($this->fetchPostPages($path, []))
            ->map(fn (array $page) => $page['results'])
            ->flatten(1)
            ->map(fn (array $row) => $this->buildOrderBill($row))
            ->groupBy(fn (OrderBill $orderBill) => $orderBill->created->format('Y, W'))
            ->map(function (Collection $weekly_bills, $week) {
                $bills = $weekly_bills->map(fn (OrderBill $ob) => $ob->amount)
                    ->toArray()
                ;

                return new WeekSales($week,
                    count($bills),
                    array_sum($bills));
            })
            ->sort(fn (WeekSales $weekSales1, WeekSales $weekSales2) => $weekSales1->week <=> $weekSales2->week)
        ;
    }

    public function submitOrder(NewOrder $new_order): OrderDetails
    {
        $res = $this->postJson('/orders/create',
            [
                'DeviceManufacturer' => $new_order->deviceManufacturer,
                'DeviceBrand' => $new_order->deviceBrand,
                'DeviceType' => $new_order->deviceType,
            ]
        );

        $new_order->id = $res['id'];

        $this->postJson("/orders/{$new_order->id}/notes/create", [
            'Type' => 'Issue',
            'Description' => $new_order->issue,
        ]);

        return $this->fetchOrder($new_order->id);
    }

    private function fetchPages(string $path): iterable
    {
        $page = 1;
        $finished = false;
        do {
            try {
                yield $this->getJson($path, $page++);
            } catch (\DomainException $e) {
                $finished = true;
            }
        } while (!$finished && (!$this->max_pages || $page <= $this->max_pages));
    }

    private function fetchPostPages(string $path, array $data): iterable
    {
        $page = 1;
        $finished = false;
        do {
            try {
                yield $this->postJson($path, $data, $page++);
            } catch (\DomainException $e) {
                $finished = true;
            }
        } while (!$finished && (!$this->max_pages || $page <= $this->max_pages));
    }

    private function getJson(string $path, int $page = 1): array
    {
        $token = $this->resolveToken();

        $response = $this->client->request('GET', $path, [
            'headers' => ['X-Fixably-Token' => $token],
            'query' => ['page' => $page]
        ]);

        return $this->parseResponse($response);
    }

    private function postJson(string $path, array $form_data, int $page = 1): array
    {
        $token = $this->resolveToken();

        $response = $this->client->request('POST', $path, [
            'headers' => ['X-Fixably-Token' => $token],
            'body' => $form_data,
            'query' => ['page' => $page]
        ]);

        return $this->parseResponse($response);
    }

    private function resolveToken(): string
    {
        if (!$this->token) {
            $this->token = $this->fetchTokenForCode(self::CODE);
        }

        return $this->token;
    }

    private function fetchTokenForCode(int $code): string
    {
        $response = $this->client->request('POST', '/token', [
            'body' => ['Code' => $code],
        ]);

        $data = $response->toArray();

        return $data['token'] ?? throw new \RuntimeException("Could not fetch token: {$data['error']}");
    }

    private function fetchStatusMap(): array
    {
        $data = $this->getJson('/statuses');

        return Collection::from($data)
            ->indexBy(fn (array $row) => $row['id'])
            ->map(fn (array $row) => $row['description'])
            ->toArray()
        ;
    }

    private function mapStatus(string|int $status): string
    {
        static $status_map = null;
        if (!$status_map) {
            $status_map = $this->fetchStatusMap();
        }

        return $status_map[$status] ?? UNKNOWN_STATUS;
    }

    private function parseResponse(\Symfony\Contracts\HttpClient\ResponseInterface $response): array
    {
        $data = $response->toArray();

        if ($data['error'] ?? false) {
            if ($data['error'] === self::ERROR_INVALID_PAGE) {
                throw new \DomainException("Out of pages");
            }

            if ($data['error'] === 'No results matching search criteria') {
                throw new \DomainException("Nothing found");
            }

            throw new \RuntimeException("Could not fetch data: {$data['error']}");
        }

        return $data;
    }

    private function buildOrder(array $row): Order
    {
        $row['status'] = $this->mapStatus($row['status']);

        return Order::createFromRow($row);
    }

    private function buildOrderBill(array $row): OrderBill
    {
        return OrderBill::createFromRow($row);
    }

    private function fetchOrder(int $id): OrderDetails
    {
        $res = $this->getJson("/orders/{$id}");

        return new OrderDetails(...$res);
    }

}
 