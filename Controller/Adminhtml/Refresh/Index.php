<?php

namespace Leanpay\Payment\Controller\Adminhtml\Refresh;

use Magento\Backend\App\Action;
use Magento\Cron\Model\ResourceModel\Schedule;
use Magento\Cron\Model\Schedule as ScheduleModel;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CronException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Index
 *
 * @package Leanpay\Payment\Controller\Adminhtml\Refresh
 */
class Index extends Action
{
    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @var DateTime
     */
    private $dataTime;

    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * Index constructor.
     *
     * @param Action\Context $context
     * @param ScheduleFactory $scheduleFactory
     * @param DateTime $dateTime
     * @param Schedule $schedule
     */
    public function __construct(
        Action\Context $context,
        ScheduleFactory $scheduleFactory,
        DateTime $dateTime,
        Schedule $schedule
    ) {
        $this->dataTime = $dateTime;
        $this->scheduleFactory = $scheduleFactory;
        $this->schedule = $schedule;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws CronException
     */
    public function execute()
    {
        $schedule = $this->scheduleFactory->create();
        $schedule->setStatus(ScheduleModel::STATUS_PENDING);
        $schedule->setJobCode('leanpay_sync_installment');
        $schedule->setCronExpr('* * * * *');
        $schedule->setScheduledAt($this->dataTime->gmtTimestamp());

        $resultFactory = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultFactory->setUrl($this->_redirect->getRefererUrl());

        if ($schedule->trySchedule()) {
            try {
                $this->schedule->save($schedule);
                $this->getMessageManager()->addSuccessMessage(__('Leanpay successfully scheduled a refresh'));
            } catch (AlreadyExistsException | \Exception $e) {
                $this->getMessageManager()->addErrorMessage(__('Failed to schedule a Leanpay refresh, please try later'));
            }
        }

        return $resultFactory;
    }
}
