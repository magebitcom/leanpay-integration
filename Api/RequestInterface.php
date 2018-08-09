<?php

namespace Leanpay\Payment\Api;

use Exception;

interface RequestInterface
{
    /**
     * @param array $additionalData
     * @return array
     * @throws Exception
     */
    public function getLeanpayToken(array $additionalData = []): array;
}