<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart;

/**
 * @package checkout
 */
class CartEvents
{
    /**
     * @Event("Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent")
     */
    final public const CHECKOUT_ORDER_PLACED = 'checkout.order.placed';
}
