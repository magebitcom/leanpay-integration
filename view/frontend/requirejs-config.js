var config = {
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Leanpay_Payment/js/swatch-renderer-mixin': true
            },
            'Magento_Bundle/js/price-bundle':{
                'Leanpay_Payment/js/price-bundle-mixin': true
            },
            'Magento_Catalog/js/price-box':{
                'Leanpay_Payment/js/price-box-mixin': true
            }
        }
    }
};
