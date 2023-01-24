<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Api\Context;

/**
 * @package core
 */
class SalesChannelApiSource implements ContextSource
{
    public string $type = 'sales-channel';

    public function __construct(private readonly string $salesChannelId)
    {
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }
}
