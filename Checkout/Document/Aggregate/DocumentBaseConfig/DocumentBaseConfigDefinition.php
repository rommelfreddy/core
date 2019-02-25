<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Document\Aggregate\DocumentBaseConfig;

use Shopware\Core\Checkout\Document\Aggregate\DocumentBaseConfigSalesChannel\DocumentBaseConfigSalesChannelDefinition;
use Shopware\Core\Checkout\Document\Aggregate\DocumentType\DocumentTypeDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class DocumentBaseConfigDefinition extends EntityDefinition
{
    public static function getEntityName(): string
    {
        return 'document_base_config';
    }

    public static function getCollectionClass(): string
    {
        return DocumentBaseConfigCollection::class;
    }

    public static function getEntityClass(): string
    {
        return DocumentBaseConfigEntity::class;
    }

    protected static function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            (new FkField('type_id', 'typeId', DocumentTypeDefinition::class))->addFlags(new Required()),
            (new FkField('logo_id', 'logoId', DocumentTypeDefinition::class))->addFlags(),

            (new StringField('name', 'name'))->addFlags(new Required()),
            new JsonField('config', 'config'),
            new CreatedAtField(),

            (new ManyToOneAssociationField('type', 'type_id', DocumentTypeDefinition::class, true))->addFlags(new Required()),
            (new ManyToOneAssociationField('logo', 'logo_id', MediaDefinition::class, true))->addFlags(new Required()),
            new ManyToManyAssociationField('salesChannels', SalesChannelDefinition::class, DocumentBaseConfigSalesChannelDefinition::class, false, 'document_base_config_id', 'sales_channel_id'),
        ]);
    }
}
