<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Plugin\Event;

use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\PluginEntity;

/**
 * @package core
 */
class PluginPreInstallEvent extends PluginLifecycleEvent
{
    public function __construct(PluginEntity $plugin, private readonly InstallContext $context)
    {
        parent::__construct($plugin);
    }

    public function getContext(): InstallContext
    {
        return $this->context;
    }
}
