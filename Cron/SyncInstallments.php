<?php
declare(strict_types=1);

namespace Leanpay\Payment\Cron;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Api\InstallmentRepositoryInterface;
use Leanpay\Payment\Helper\Data;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\HTTP\Client\Curl;
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
     * SyncInstallments constructor.
     *
     * @param Curl $curl
     * @param Data $helper
     * @param ManagerInterface $eventManager
     * @param LoggerInterface $logger
     * @param InstallmentRepositoryInterface $repository
     */
    public function __construct(
        Curl $curl,
        Data $helper,
        ManagerInterface $eventManager,
        LoggerInterface $logger,
        InstallmentRepositoryInterface $repository
    ) {
        $this->logger = $logger;
        $this->curlClient = $curl;
        $this->helper = $helper;
        $this->eventManager = $eventManager;
        $this->repository = $repository;
    }

    /**
     * Sync all Installments from Leanpay server
     */
    public function execute()
    {
        $url = $this->helper->getInstallmentURL();
        $apiKey = $this->helper->getLeanpayApiKey();

        try {
            if ($apiKey) {
                $curl = $this->addHeaders($this->curlClient);
                $curl->post($url, json_encode(['vendorApiKey' => $apiKey]));
                $data = $curl->getBody();
                if ($data) {
                    $parse = json_decode($data);
                    if ($parse->groups) {
                        $models = [];
                        $index = 0;
                        foreach (reset($parse) as $group) {
                            if (is_object($group) && $group->groupId && $group->loanAmounts) {
                                foreach ($group->loanAmounts as $amount) {
                                    if (is_object($amount) && is_array($amount->possibleInstallments)) {
                                        foreach ($amount->possibleInstallments as $installment) {
                                            $models[$index][InstallmentInterface::ENTITY_ID] = $index;
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
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
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
