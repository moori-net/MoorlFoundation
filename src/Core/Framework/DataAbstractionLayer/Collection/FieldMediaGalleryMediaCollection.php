<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\NoConstraint;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldMediaGalleryMediaCollection extends FieldCollection
{
    public static function getFieldItems(string $parentClass, string $mediaReferenceClass): array
    {
        $ed = new ExtractedDefinition(
            class: $mediaReferenceClass,
            fkStorageName: true,
            referenceField: true,
            replace: "_media"
        );

        return [
            (new FkField($ed->getFkStorageName(), 'coverId', $mediaReferenceClass))->addFlags(new ApiAware(), new NoConstraint()),
            (new ManyToOneAssociationField('cover', $ed->getFkStorageName(), $mediaReferenceClass, 'id'))->addFlags(new ApiAware()),
            (new OneToManyAssociationField('media', $mediaReferenceClass, $ed->getReferenceField()))->addFlags(new ApiAware(), new CascadeDelete()),
        ];
    }

    public static function getMediaFieldItems(string $referenceClass): array
    {
        $extracted = new ExtractedDefinition(
            class: $referenceClass,
            propertyName: true,
            fkStorageName: true,
            fkPropertyName: true,
            referenceField: true,
            append: "_media",
        );

        $fieldItems = [
            (new IdField('id', 'id'))
                ->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))
                ->addFlags(new ApiAware(), new Required()),
            (new FkField($extracted->getFkStorageName(), $extracted->getFkPropertyName(), $referenceClass))
                ->addFlags(new ApiAware(), new Required()),
            (new IntField('position', 'position'))
                ->addFlags(new Required(), new ApiAware()),
            (new CustomFields())
                ->addFlags(new ApiAware()),
            (new ManyToOneAssociationField($extracted->getPropertyName(), $extracted->getFkStorageName(), $referenceClass))
                ->addFlags(new CascadeDelete()),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id', true))
                ->addFlags(new ApiAware(), new CascadeDelete()),
            (new OneToManyAssociationField('coverItems', $referenceClass, $extracted->getReferenceField()))
                ->addFlags(new SetNullOnDelete(false)),
        ];

        if (ExtractedDefinition::isVersionDefinition($referenceClass)) {
            $fieldItems[] = new VersionField();
            $fieldItems[] = (new ReferenceVersionField($referenceClass))
                ->addFlags(new PrimaryKey(), new Required());
        }

        return $fieldItems;
    }
}
