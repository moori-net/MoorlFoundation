<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Salutation\SalutationDefinition;

class FieldPersonCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            new FkField('salutation_id', 'salutationId', SalutationDefinition::class),
            (new ManyToOneAssociationField('salutation', 'salutation_id', SalutationDefinition::class))->addFlags(new SetNullOnDelete(), new EditField(), new LabelProperty('displayName')),
            (new StringField('title', 'title'))->addFlags(new EditField(EditField::TEXT)),
            (new StringField('first_name', 'firstName'))->addFlags(new EditField(EditField::TEXT)),
            (new StringField('last_name', 'lastName'))->addFlags(new EditField('text')),
            (new StringField('company', 'company'))->addFlags(new EditField('text')),
        ];
    }
}
