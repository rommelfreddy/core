<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Api\Context;

use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

/**
 * @package core
 */
#[DiscriminatorMap(typeProperty: 'type', mapping: ['system' => SystemSource::class, 'sales-channel' => SalesChannelApiSource::class, 'admin-api' => AdminApiSource::class, 'shop-api' => ShopApiSource::class, 'admin-sales-channel-api' => AdminSalesChannelApiSource::class])]
interface ContextSource
{
}
