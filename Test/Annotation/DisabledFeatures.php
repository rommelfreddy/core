<?php declare(strict_types=1);

namespace Shopware\Core\Test\Annotation;

/**
 * @package core
 *
 * @internal
 * @Annotation
 *
 * @Target({"METHOD", "CLASS"})
 */
final class DisabledFeatures
{
    /**
     * @var string[]
     */
    public array $features;
}
