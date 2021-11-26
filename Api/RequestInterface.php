<?php

namespace Leanpay\Payment\Api;

use Exception;

interface RequestInterface
{
    /**
     * Get leanpay token
     *
     * @param array $additionalData
     * @return array
     * @throws Exception
     */
    public function getLeanpayToken(array $additionalData = []): array;
}
