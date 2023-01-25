<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Log;

/**
 * @interal
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
#[\Shopware\Core\Framework\Log\Package(null)]
final class Package
{
    public const PACKAGE_TRACE_ATTRIBUTE_KEY = 'pTrace';

    public function __construct(public string $package)
    {
    }

    public static function getPackageName(string $class): ?string
    {
        if (!class_exists($class)) {
            return null;
        }

        $reflection = new \ReflectionClass($class);

        $attrs = $reflection->getAttributes(Package::class);

        if (!empty($attrs)) {
            return $attrs[0]->getArguments()[0] ?? null;
        }

        return null;
    }
}
