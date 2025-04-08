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
    public static function getFieldItems(
        string $parentClass,
        string $translationReferenceClass = null,
        bool $isVersionAware = false
    ): array
    {
        $fieldItems = [
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required())
        ];

        if ($isVersionAware) {
            $fieldItems[] = new VersionField();
        }

        if ($translationReferenceClass) {
            $parentEd = ExtractedDefinition::get(class: $parentClass);
            $fieldItems[] = (new TranslationsAssociationField($translationReferenceClass, $parentEd->getReferenceField()))
                ->addFlags(new Required());
        }

        return $fieldItems;
    }
}
