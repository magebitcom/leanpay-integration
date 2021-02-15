<?php
declare(strict_types=1);

namespace Leanpay\Payment\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class RefreshButton
 *
 * @package Leanpay\Payment\Block\System\Config
 */
class RefreshButton extends Field
{
    protected $_template = 'Leanpay_Payment::system/config/button.phtml';

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return mixed
     */
    public function getCustomUrl()
    {
        return $this->getUrl('installment/refresh/index');
    }

    /**
     * @return mixed
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData([
                'id' => 'btn_id',
                'label' => __('Refresh'),
                'onclick' => "setLocation('{$this->getCustomUrl()}')"
            ]);

        return $button->toHtml();
    }
}
