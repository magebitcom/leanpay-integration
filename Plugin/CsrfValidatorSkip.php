<?php
declare(strict_types=1);

namespace Leanpay\Payment\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\CsrfValidator;
use Magento\Framework\App\RequestInterface;

/**
 * Class CsrfValidatorSkip
 *
 * @package Leanpay\Payment\Plugin
 */
class CsrfValidatorSkip
{
    /**
     * @param CsrfValidator $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     * @param ActionInterface $action
     */
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        if ($request->getModuleName() == 'leanpay') {
            return;
        }
        $proceed($request, $action);
    }
}
