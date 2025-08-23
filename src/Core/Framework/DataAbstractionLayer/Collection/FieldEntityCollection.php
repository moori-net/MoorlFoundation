<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldEntityCollection extends FieldCollection
{
    public static function getFieldItems(string $localClass, string $translationReferenceClass = null): array
    {
        $fieldItems = [
            (new IdField('id', 'id'))
                ->addFlags(new ApiAware(), new PrimaryKey(), new Required())
        ];

        if (ExtractedDefinition::isVersionDefinition($localClass)) {
            $fieldItems[] = new VersionField();
        }

        if ($translationReferenceClass) {
            $localEd = ExtractedDefinition::get(class: $localClass);

            $fieldItems[] = (new TranslationsAssociationField($translationReferenceClass, $localEd->getFkStorageName()))
                ->addFlags(new Required());
        }

        return $fieldItems;
    }
}
