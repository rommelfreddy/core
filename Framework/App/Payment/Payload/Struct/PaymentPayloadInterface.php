<?php declare(strict_types=1);

namespace Shopware\Core\Framework\App\Payment\Payload\Struct;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;

/**
 * @internal only for use by the app-system
 *
 * @package core
 */
interface PaymentPayloadInterface extends SourcedPayloadInterface
{
    public function getOrderTransaction(): OrderTransactionEntity;
}
