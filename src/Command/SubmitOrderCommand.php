<?php
declare(strict_types=1);

namespace App\Command;

use App\Dto\NewOrder;
use App\Service\OrderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SubmitOrderCommand extends Command
{
    protected static $defaultName = 'fixably:submit-order';
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        parent::__construct(null);
        $this->orderService = $orderService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $type = $io->choice('Device Type', ['Laptop', 'Phone', 'Tablet']);
        $manufacturer = $io->ask('Manufacturer');
        $brand = $io->ask('Brand');
        $issue = $io->ask('Issue description');

        $new_order = new NewOrder($manufacturer, $brand, $type, $issue);

        $details = $this->orderService->submitOrder($new_order);

        $io->success("Order created with Id: {$new_order->id}");

        $table = new Table($output);

        $table->addRow(['Id', $details->id]);
        $table->addRow(['Type', $details->deviceType]);
        $table->addRow(['Manufacturer', $details->deviceManufacturer]);
        $table->addRow(['Brand', $details->deviceBrand]);
        $table->addRow(['Created', $details->created->format('d.m.Y H:i')]);

        $table->render();

        $table = new Table($output);

        foreach ($details->notes as $note) {
            $table->addRow([$note->created->format('d.m.Y H:i'), $note->type, $note->description]);
        }

        $table->render();

        return Command::SUCCESS;
    }


}