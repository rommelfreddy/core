<?php declare(strict_types=1);

namespace Shopware\Core\System\CustomEntity\Xml\Field;

use Shopware\Core\System\CustomEntity\Xml\Field\Traits\RequiredTrait;
use Shopware\Core\System\CustomEntity\Xml\Field\Traits\TranslatableTrait;

/**
 * @package core
 */
class JsonField extends Field
{
    use TranslatableTrait;
    use RequiredTrait;

    protected string $type = 'json';

    /**
     * @internal
     */
    public static function fromXml(\DOMElement $element): Field
    {
        return new self(self::parse($element));
    }
}
