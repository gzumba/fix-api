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

class WeeklySalesCommand extends Command
{
    protected static $defaultName = 'fixably:weekly-sales';
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        parent::__construct(null);
        $this->orderService = $orderService;
    }

    protected function configure()
    {
        $this->addArgument('start_date', InputArgument::REQUIRED, 'Period start')
            ->addArgument('end_date', InputArgument::REQUIRED, 'Period end')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sales = $this->orderService->fetchWeeklySales(
            new \DateTime($input->getArgument('start_date')),
            new \DateTime($input->getArgument('end_date'))
        );

        $table = new Table($output);
        $table->setHeaders(['Week', 'Invoices', 'Invoice Change %', 'Total amount', 'Change-%']);

        $prev_week = [];
        foreach ($sales as $week_sales) {
            $amount_change = '';
            $count_change = '';
            if ($prev_week) {
                $amount_change = round(($week_sales->amount / $prev_week['amount'] - 1)*100.0, 1);
                $count_change = round(($week_sales->invoices / $prev_week['invoices'] - 1)*100.0, 1);
            }
            $table->addRow([$week_sales->week, $week_sales->invoices, $count_change, $week_sales->amount, $amount_change]);
            $prev_week = [
                'amount' => $week_sales->amount,
                'invoices' => $week_sales->invoices,
            ];
        }
        $table->render();

        return Command::SUCCESS;
    }


}