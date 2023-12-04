<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\Datetime;

class ModifyEndDate extends Datetime
{
    /**
     * Prepare date for save in DB
     *
     * Modify the time part to 23:59:59
     *
     * @param string|int|\DateTimeInterface $date
     * @return string|null
     */
    public function formatDate($date): string | null
    {
        // Use parent method to handle date formatting
        $formattedDate = parent::formatDate($date);

        if (empty($date)) {
            return $formattedDate;
        }

        // Adjust the time portion to 23:59:59
        return $this->modifyTimeToEndOfDay($formattedDate);
    }

    /**
     * Modify time part to 23:59:59
     *
     * @param string $formattedDate
     * @return string
     */
    private function modifyTimeToEndOfDay($formattedDate)
    {
        $dateTime = new \DateTime($formattedDate);
        $dateTime->setTime(23, 59, 59);
        return $dateTime->format('Y-m-d H:i:s');
    }
}
