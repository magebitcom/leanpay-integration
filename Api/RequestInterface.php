<?php

namespace Leanpay\Payment\Api;

use Exception;

/**
 * Interface RequestInterface
 *
 * @package Leanpay\Payment\Api
 */
interface RequestInterface
{
    /**
     * @param array $additionalData
     * @return array
     * @throws Exception
     */
    public function getLeanpayToken(array $additionalData = []): array;
}
