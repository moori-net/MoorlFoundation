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
    public static function getFieldItems(string $localClass, string $mediaReferenceClass): array
    {
        $localEd = ExtractedDefinition::get(class: $localClass);
        $mediaReferenceEd = ExtractedDefinition::get(class: $mediaReferenceClass);

        return [
            (new FkField($mediaReferenceEd->getFkStorageName(), 'coverId', $mediaReferenceClass))
                ->addFlags(new ApiAware(), new NoConstraint()),
            (new ManyToOneAssociationField('cover', $mediaReferenceEd->getFkStorageName(), $mediaReferenceClass))
                ->addFlags(new ApiAware()),
            (new OneToManyAssociationField('media', $mediaReferenceClass, $localEd->getFkStorageName()))
                ->addFlags(new ApiAware(), new CascadeDelete()),
        ];
    }

    public static function getMediaFieldItems(string $localClass, string $referenceClass): array
    {
        $localEd = ExtractedDefinition::get(class: $localClass);
        $referenceEd = ExtractedDefinition::get(class: $referenceClass);

        $fieldItems = [
            (new IdField('id', 'id'))
                ->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))
                ->addFlags(new ApiAware(), new Required()),
            (new FkField($referenceEd->getFkStorageName(), $referenceEd->getFkPropertyName(), $referenceClass))
                ->addFlags(new ApiAware(), new Required()),
            (new IntField('position', 'position'))
                ->addFlags(new ApiAware(), new Required()),
            (new CustomFields())
                ->addFlags(new ApiAware()),
            (new ManyToOneAssociationField($referenceEd->getPropertyName(), $referenceEd->getFkStorageName(), $referenceClass))
                ->addFlags(new CascadeDelete()),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id', true))
                ->addFlags(new ApiAware(), new CascadeDelete()),
            (new OneToManyAssociationField('coverItems', $referenceClass, $localEd->getFkStorageName()))
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
