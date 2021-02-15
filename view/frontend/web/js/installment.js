/* global define */
define(
    [
        'jquery',
    ],
    function ($) {
        'use strict';

        $(document).ready(function (){
            $('.installment-info .installment-logo').on('click',function (){
                $('.installment-tooltip').toggleClass('hidden');
            });
        });
    }
);
