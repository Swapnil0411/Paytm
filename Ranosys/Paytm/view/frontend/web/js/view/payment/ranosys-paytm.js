define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paytm',
                component: 'Ranosys_Paytm/js/view/payment/method-renderer/ranosys-paytm'
            }
        );
        return Component.extend({});
    }
 );