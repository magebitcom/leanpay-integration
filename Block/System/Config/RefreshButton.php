<?php
declare(strict_types=1);

namespace Leanpay\Payment\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class RefreshButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Leanpay_Payment::system/config/button.phtml';

    /**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate url by route and parameters
     *
     * @return string
     */
    public function getCustomUrl()
    {
        return $this->getUrl('installment/refresh/index');
    }

    /**
     * Returns button HTML
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setData([
                'id' => 'btn_id',
                'label' => __('Refresh'),
                'onclick' => "setLocation('{$this->getCustomUrl()}')"
            ]);

        return $button->toHtml();
    }
}
