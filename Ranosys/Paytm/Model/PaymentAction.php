<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ranosys\Paytm\Model;

/**
 * @api
 * @since 100.0.2
 */
class PaymentAction implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'authorize_capture', 'label' => __('Yes')], ['value' => 'capture', 'label' => __('No')]];
    }

    public function toArray()
    {
        return [0 => __('No'), 1 => __('Yes')];
    }
}
