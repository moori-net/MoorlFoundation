<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldThingMetaCollection extends FieldCollection
{
    public static function getFieldItems(
        bool $flag = true,
        bool $metaAuthor = false,
        bool $metaMedia = false,
    ): array
    {
        if (!$flag) return [];

        return array_merge(
            [
                (new TranslatedField('metaTitle'))->addFlags(new EditField(EditField::TEXT)),
                (new TranslatedField('metaDescription'))->addFlags(new EditField(EditField::TEXTAREA)),
                (new TranslatedField('metaKeywords'))->addFlags(new EditField(EditField::TEXTAREA))
            ],
            $metaAuthor ? [
                (new TranslatedField('metaAuthor'))->addFlags(new EditField(EditField::TEXT))
            ] : [],
            $metaMedia ? [
                (new TranslatedField('metaMediaId'))->addFlags(),
                new ManyToOneAssociationField('metaMedia', 'meta_media_id', MediaDefinition::class),
            ] : [],
        );
    }

    public static function getTranslatedFieldItems(
        bool $flag = true,
        bool $metaAuthor = false,
        bool $metaMedia = false,
    ): array
    {
        if (!$flag) return [];

        return array_merge(
            [
                new LongTextField('meta_keywords', 'metaKeywords'),
                new LongTextField('meta_title', 'metaTitle'),
                new LongTextField('meta_description', 'metaDescription'),
            ],
            $metaAuthor ? [
                new LongTextField('meta_author', 'metaAuthor'),
            ] : [],
            $metaMedia ? [
                new FkField('meta_media_id', 'metaMediaId', MediaDefinition::class),
            ] : [],
        );
    }
}
