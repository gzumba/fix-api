<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\OrderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrderStatusCountsCommand extends Command
{
    protected static $defaultName = 'fixably:order-status-counts';
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        parent::__construct(null);
        $this->orderService = $orderService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $status_counts = $this->orderService->fetchStatusCounts();

        $table = new Table($output);
        $table->setHeaders(['Status', 'Count']);
        foreach ($status_counts as $status => $count) {
            $table->addRow([$status, $count]);
        }

        $table->render();

        return Command::SUCCESS;
    }


}