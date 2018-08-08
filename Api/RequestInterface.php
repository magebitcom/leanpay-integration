<?php

namespace Leanpay\Payment\Api;

interface RequestInterface
{
    public function getLeanpayToken($additionalData = []): array;
}