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
    public static function getFieldItems(string $class, string $translationReferenceClass = null): array
    {
        $fieldItems = [
            (new IdField('id', 'id'))
                ->addFlags(new ApiAware(), new PrimaryKey(), new Required())
        ];

        if (ExtractedDefinition::isVersionDefinition($class)) {
            $fieldItems[] = new VersionField();
        }

        if ($translationReferenceClass) {
            $ed = ExtractedDefinition::get(class: $class);

            $fieldItems[] = (new TranslationsAssociationField($translationReferenceClass, $ed->getReferenceField()))
                ->addFlags(new Required());
        }

        return $fieldItems;
    }
}
