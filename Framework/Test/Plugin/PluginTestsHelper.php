<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\Plugin;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Changelog\ChangelogParser;
use Shopware\Core\Framework\Plugin\Changelog\ChangelogService;
use Shopware\Core\Framework\Plugin\KernelPluginCollection;
use Shopware\Core\Framework\Plugin\PluginService;
use Shopware\Core\Framework\Plugin\Util\PluginFinder;
use Shopware\Core\Framework\Plugin\Util\VersionSanitizer;
use SwagTest\SwagTest;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait PluginTestsHelper
{
    protected function createPluginService(
        EntityRepositoryInterface $pluginRepo,
        EntityRepositoryInterface $languageRepo,
        string $projectDir,
        PluginFinder $pluginFinder
    ): PluginService {
        return new PluginService(
            __DIR__ . '/_fixture/plugins',
            $projectDir,
            $pluginRepo,
            $languageRepo,
            new ChangelogService(new ChangelogParser()),
            $pluginFinder,
            new VersionSanitizer()
        );
    }

    protected function createPlugin(
        EntityRepositoryInterface $pluginRepo,
        Context $context,
        string $version = SwagTest::PLUGIN_VERSION,
        ?string $installedAt = null
    ): void {
        $pluginRepo->create(
            [
                [
                    'baseClass' => SwagTest::class,
                    'name' => 'SwagTest',
                    'version' => $version,
                    'label' => SwagTest::PLUGIN_LABEL,
                    'installedAt' => $installedAt,
                    'active' => false,
                    'autoload' => [],
                ],
            ],
            $context
        );
    }

    abstract protected function getContainer(): ContainerInterface;

    private function addTestPluginToKernel(string $pluginName, bool $active = false): void
    {
        $testPluginBaseDir = __DIR__ . '/_fixture/plugins/' . $pluginName;
        /** @var class-string<Plugin> $class */
        $class = '\\' . $pluginName . '\\' . $pluginName;

        require_once $testPluginBaseDir . '/src/' . $pluginName . '.php';

        /** @var KernelPluginCollection $pluginCollection */
        $pluginCollection = $this->getContainer()->get(KernelPluginCollection::class);
        $plugin = new $class($active, $testPluginBaseDir);
        $pluginCollection->add($plugin);

        $this->getContainer()->get(Plugin\KernelPluginLoader\KernelPluginLoader::class)->getPluginInstances()->add($plugin);
    }
}
