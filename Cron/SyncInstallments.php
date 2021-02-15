<?php
declare(strict_types=1);

namespace Leanpay\Payment\Cron;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Api\InstallmentRepositoryInterface;
use Leanpay\Payment\Helper\Data;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Profiler;
use Magento\PageCache\Model\Cache\Type;
use Psr\Log\LoggerInterface;

/**
 * Class SyncInstallments
 *
 * @package Leanpay\Payment\Cron
 */
class SyncInstallments
{
    /**
     * @var Curl
     */
    private Curl $curlClient;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var InstallmentRepositoryInterface
     */
    private InstallmentRepositoryInterface $repository;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var Manager
     */
    private Manager $cacheManager;

    /**
     * SyncInstallments constructor.
     *
     * @param Curl $curl
     * @param Data $helper
     * @param ManagerInterface $eventManager
     * @param LoggerInterface $logger
     * @param InstallmentRepositoryInterface $repository
     * @param ResourceConnection $resource
     * @param Manager $manager
     */
    public function __construct(
        Curl $curl,
        Data $helper,
        ManagerInterface $eventManager,
        LoggerInterface $logger,
        InstallmentRepositoryInterface $repository,
        ResourceConnection $resource,
        Manager $manager
    ) {
        $this->logger = $logger;
        $this->curlClient = $curl;
        $this->helper = $helper;
        $this->eventManager = $eventManager;
        $this->repository = $repository;
        $this->resource = $resource;
        $this->cacheManager = $manager;
    }

    /**
     * Sync all Installments from Leanpay server
     */
    public function execute()
    {
        Profiler::start('leanpay_sync_installment');

        $url = $this->helper->getInstallmentURL();
        $apiKey = $this->helper->getLeanpayApiKey();
        $enabled = $this->helper->isActive();

        try {
            if ($apiKey && $enabled) {
                $curl = $this->addHeaders($this->curlClient);
                $curl->post($url, json_encode(['vendorApiKey' => $apiKey]));
                $data = $curl->getBody();
                if ($data) {
                    $connection = $this->resource->getConnection();
                    if (!$connection->isTableExists(InstallmentInterface::TABLE_NAME)) {
                        return;
                    }

                    $table = $connection->getTableName(InstallmentInterface::TABLE_NAME);
                    $connection->truncateTable($table);

                    $parse = json_decode($data);
                    if ($parse->groups) {
                        $models = [];
                        $index = 0;
                        foreach (reset($parse) as $group) {
                            if (is_object($group) && $group->groupId && $group->loanAmounts) {
                                foreach ($group->loanAmounts as $amount) {
                                    if (is_object($amount) && is_array($amount->possibleInstallments)) {
                                        foreach ($amount->possibleInstallments as $installment) {
                                            $models[$index][InstallmentInterface::GROUP_ID] = $group->groupId;
                                            $models[$index][InstallmentInterface::GROUP_NAME] = $group->groupName;
                                            $models[$index][InstallmentInterface::CURRENCY_CODE] = $group->currencyCode;
                                            $models[$index][InstallmentInterface::LOAN_AMOUNT] = $amount->loanAmount;
                                            $models[$index][InstallmentInterface::INSTALLMENT_AMOUNT] = $installment->installmentAmout;
                                            $models[$index][InstallmentInterface::INSTALLMENT_PERIOD] = $installment->numberOfMonths;
                                            $index++;
                                        }
                                    }
                                }
                            }
                        }

                        $this->saveAllModels($models);
                        $this->cacheManager->clean([Type::TYPE_IDENTIFIER]);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        Profiler::stop('leanpay_sync_installment');
    }

    /**
     * @param Curl $curl
     * @return Curl
     */
    private function addHeaders(Curl $curl)
    {
        $curl->addHeader('Content-Type', 'application/json');

        $this->eventManager->dispatch(
            'leanpay_syncinstallment_cron_add_headers',
            ['curl' => $curl]
        );

        return $curl;
    }

    /**
     * @param array $models
     */
    private function saveAllModels($models = [])
    {
        $this->eventManager->dispatch(
            'leanpay_syncinstallment_cron_models_save',
            ['models' => $models]
        );

        foreach ($models as $model) {
            try {
                $this->repository->save($this->repository->newModel()->setData($model));
            } catch (CouldNotSaveException $exception) {
                $this->logger->critical($exception);
            }
        }
    }
}
