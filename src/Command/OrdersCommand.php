<?php
declare(strict_types=1);

namespace App\Command;

use App\Dto\Order;
use App\Service\OrderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrdersCommand extends Command
{
    protected static $defaultName = 'fixably:orders';
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        parent::__construct(null);
        $this->orderService = $orderService;
    }

    protected function configure()
    {
        $this->addArgument('brand', InputArgument::OPTIONAL, 'Brand to fetch', 'iPhone')
            ->setDescription("Fetch all orders for a Brand with an assigned Technician")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orders = $this->orderService->fetchBrandOrders($input->getArgument('brand'))
            ->filter(fn (Order $order) => $order->technician !== null)
        ;

        $table = new Table($output);
        $table->setHeaders(['Id', 'Created', 'Status', 'Technician', 'Device']);
        foreach ($orders as $order) {
            $table->addRow([$order->id, $order->created->format('d.m.Y'), $order->status, $order->technician, $order->deviceBrand]);
        }

        $table->render();

        return Command::SUCCESS;
    }


}