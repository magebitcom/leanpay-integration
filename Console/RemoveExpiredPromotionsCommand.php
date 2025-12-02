<?php
declare(strict_types=1);

namespace Leanpay\Payment\Console;

use Magento\Framework\App\State as AppState;
use Leanpay\Payment\Cron\RemoveExpiredPromotions;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveExpiredPromotionsCommand extends Command
{
    private AppState $appState;
    /**
     * @var RemoveExpiredPromotions
     */
    private $removeExpiredPromotions;

    /**
     * @param RemoveExpiredPromotions $removeExpiredPromotions
     * @param string|null $name
     */
    public function __construct(
        AppState $appState,
        RemoveExpiredPromotions $removeExpiredPromotions,
        ?string $name = null
    ) {
        $this->appState = $appState;
        $this->removeExpiredPromotions = $removeExpiredPromotions;
        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface  $input,
        OutputInterface $output
    ): int
    {
        $this->appState->setAreaCode('frontend');
        $output->writeln('Removing Expired Promotions Started... ');
        $this->removeExpiredPromotions->execute();
        $output->writeln('Removing Expired Promotions Completed... ');
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('leanpay:promotions');
        $this->setDescription('Removes expired Leanpay promotions.');

        parent::configure();
    }
}
