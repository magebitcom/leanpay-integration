define(['jquery'], function ($) {
    return {
        showHideAttr: function () {
            var action = $('[name="product[leanpay_product_time_based]"]').val();
            if (action != null) {
                switch (action) {
                    case '1':
                        this.showFields('div[data-index="leanpay_product_start_date"]');
                        this.showFields('div[data-index="leanpay_product_end_date"]');
                        break;
                    case '0':
                        this.hideFields('div[data-index="leanpay_product_start_date"]');
                        this.hideFields('div[data-index="leanpay_product_end_date"]');
                        break;
                }
            } else {
                this.hideFields('div[data-index="leanpay_product_start_date"]');
                this.hideFields('div[data-index="leanpay_product_end_date"]');
            }
        },

        hideFields: function (names) {
            $(names).toggle(false);
        },

        showFields: function (names) {
            $(names).toggle(true);
        }
    };
});
