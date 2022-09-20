<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldCompanyCollection extends FieldCollection
{
    use FieldCollectionMergeTrait;

    public function __construct()
    {
        return new parent(self::getFieldItems());
    }

    public static function getFieldItems(): array
    {
        return [
            (new StringField('executive_director', 'executiveDirector'))->addFlags(new EditField('text')),
            (new StringField('place_of_fulfillment', 'placeOfFulfillment'))->addFlags(new EditField('text')),
            (new StringField('place_of_jurisdiction', 'placeOfJurisdiction'))->addFlags(new EditField('text')),
            (new StringField('bank_bic', 'bankBic'))->addFlags(new EditField('text')),
            (new StringField('bank_iban', 'bankIban'))->addFlags(new EditField('text')),
            (new StringField('bank_name', 'bankName'))->addFlags(new EditField('text')),
            (new StringField('tax_office', 'taxOffice'))->addFlags(new EditField('text')),
            (new StringField('tax_number', 'taxNumber'))->addFlags(new EditField('text')),
            (new StringField('vat_id', 'vatId'))->addFlags(new EditField('text')),
        ];
    }
}
