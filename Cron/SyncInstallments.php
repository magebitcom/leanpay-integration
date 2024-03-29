<?php

declare(strict_types=1);

namespace Leanpay\Payment\Cron;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Api\Data\InstallmentProductInterface;
use Leanpay\Payment\Api\InstallmentProductRepositoryInterface;
use Leanpay\Payment\Api\InstallmentRepositoryInterface;
use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Helper\InstallmentHelper;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Profiler;
use Magento\PageCache\Model\Cache\Type;
use Psr\Log\LoggerInterface;

class SyncInstallments
{
    /**
     * @var Curl
     */
    private $curlClient;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var InstallmentRepositoryInterface
     */
    private $repository;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @var InstallmentHelper
     */
    private $installmentHelper;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $emulation;

    /**
     * @var InstallmentProductRepositoryInterface
     */
    private $installmentProductRepository;

    /**
     * SyncInstallments constructor.
     * @param Curl $curl
     * @param Data $helper
     * @param ManagerInterface $eventManager
     * @param LoggerInterface $logger
     * @param InstallmentRepositoryInterface $repository
     * @param ResourceConnection $resource
     * @param Manager $manager
     * @param InstallmentHelper $installmentHelper
     */
    public function __construct(
        Curl $curl,
        Data $helper,
        ManagerInterface $eventManager,
        LoggerInterface $logger,
        InstallmentRepositoryInterface $repository,
        ResourceConnection $resource,
        Manager $manager,
        InstallmentHelper $installmentHelper,
        InstallmentProductRepositoryInterface $installmentProductRepository,
        \Magento\Store\Model\App\Emulation $emulation
    ) {
        $this->logger = $logger;
        $this->curlClient = $curl;
        $this->helper = $helper;
        $this->eventManager = $eventManager;
        $this->repository = $repository;
        $this->resource = $resource;
        $this->cacheManager = $manager;
        $this->installmentHelper = $installmentHelper;
        $this->installmentProductRepository = $installmentProductRepository;
        $this->emulation = $emulation;
    }

    /**
     * Sync all Installments from Leanpay server
     */
    public function execute()
    {
        Profiler::start('leanpay_sync_installment');
        if ($this->helper->isActive()) {
            $urls = $this->helper->getInstallmentURL();
            $apiKeys = $this->helper->getAllLeanpayApiKeys();
            foreach ($apiKeys as $apiType => $apiKey) {
                $this->syncInstallments($urls[$apiType], $apiKey['key'], $apiType, $apiKey['store_id']);
            }
        }
        Profiler::stop('leanpay_sync_installment');
    }

    /**
     * @param $url
     * @param $apiKey
     * @param $apiType
     */
    public function syncInstallments($url, $apiKey, $apiType, $store_id = 0)
    {
        try {
            if ($apiKey) {
                $this->emulation->startEnvironmentEmulation($store_id, $area = 'frontend');
                $curl = $this->addHeaders($this->curlClient);
                $curl->post($url, json_encode(['vendorApiKey' => $apiKey]));
                $data = $curl->getBody();
                if ($data) {
                    $connection = $this->resource->getConnection();
                    if (!$connection->isTableExists(InstallmentInterface::TABLE_NAME)) {
                        return;
                    }

                    $parse = json_decode($data);
                    if ($parse->groups) {
                        $installmentsTable = $connection->getTableName(InstallmentInterface::TABLE_NAME);
                        $productsTable = $connection->getTableName(InstallmentProductInterface::TABLE_NAME);
                        $connection->delete($installmentsTable, 'api_type = \'' . $apiType . '\'');
                        $connection->delete($productsTable, 'country = \'' . $apiType . '\'');
                        $models = $this->extractInstallmentData($parse, $apiType);
                        $this->saveAllModels($models, $apiType);
                        $this->cacheManager->clean([Type::TYPE_IDENTIFIER]);
                    }
                }
                $this->emulation->stopEnvironmentEmulation();
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $this->emulation->stopEnvironmentEmulation();
        }
    }

    /**
     * Adds headers to curl
     *
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
     * Saves all models
     *
     * @param array $models
     * @param string $apiType
     */
    private function saveAllModels($models = [], $apiType = Data::API_ENDPOINT_SLOVENIA)
    {
        $this->eventManager->dispatch(
            'leanpay_syncinstallment_cron_models_save',
            [
                'models' => $models,
                'api_type' => $apiType
            ]
        );

        foreach ($models as $model) {
            try {
                $model = array_merge(
                    $model,
                    [InstallmentInterface::API_TYPE => $apiType]
                );
                $this->repository->save($this->repository->newModel()->setData($model));
            } catch (CouldNotSaveException $exception) {
                $this->logger->critical($exception);
            }
        }
    }

    /**
     * Extract installment data
     *
     * @param array $parse
     * @return array
     */
    private function extractInstallmentData($parse, $apiType)
    {
        $models = [];
        $index = 0;

        $installmentProductCache = [];
        $connection = $this->resource->getConnection();

        $array = (array) $parse;
        foreach (reset($array) as $group) {
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
                            $countryCode = $group->currencyCode;
                            if (!isset($installmentProductCache[$group->groupId . $countryCode])) {
                                $isStored = $this->installmentProductRepository->getByGroupId(
                                    $group->groupId,
                                    $countryCode
                                );

                                if (!$isStored) {
                                    $vendorProduct = $this->installmentProductRepository->newModel();
                                    $vendorProduct->setGroupId($group->groupId);
                                    $groupName = $group->groupName;
                                    $vendorProduct->setGroupName($groupName);
                                    $vendorProduct->setCountry($apiType);
                                    $this->installmentProductRepository->save($vendorProduct);
                                }

                                $installmentProductCache[$group->groupId . $countryCode] = true;
                            }

                            $index++;
                        }
                    }
                }
            }
        }

        return $models;
    }
}
