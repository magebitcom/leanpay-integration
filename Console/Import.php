<?php
declare(strict_types=1);

namespace Leanpay\Payment\Console;

use Leanpay\Payment\Cron\SyncInstallments;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command
{
    /**
     * @var SyncInstallments
     */
    private $installments;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @param SyncInstallments $installments
     * @param string|null $name
     */
    public function __construct(
        SyncInstallments $installments,
        AppState $appState,
        string $name = null
    ) {
        $this->installments = $installments;
        $this->appState = $appState;
        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface  $input,
        OutputInterface $output
    ) {
        $this->appState->setAreaCode('frontend');
        $output->writeln('Importing Started Leanpay... ');
        $this->installments->execute();
        $output->writeln('Importing Completed Leanpay... ');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('leanpay:import');
        $this->setDescription('Imports leanpay installments and vendorProducts');

        parent::configure();
    }
}
