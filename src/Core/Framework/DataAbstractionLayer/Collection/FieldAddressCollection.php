<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Country\Aggregate\CountryState\CountryStateDefinition;
use Shopware\Core\System\Country\CountryDefinition;

class FieldAddressCollection extends FieldCollection
{
    use FieldCollectionMergeTrait;

    public static function merge(FieldCollection $collection): void
    {
        foreach (new self() as $field) {
            $collection->add($field);
        }
    }

    public function __construct()
    {
        return new parent([
            (new StringField('street', 'street'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField('text')),
            (new StringField('street_number', 'streetNumber'))->addFlags(new EditField('text')),
            (new StringField('zipcode', 'zipcode'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField('text')),
            (new StringField('city', 'city'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField('text')),
            (new StringField('additional_address_line1', 'additionalAddressLine1'))->addFlags(new EditField('text')),
            (new StringField('additional_address_line2', 'additionalAddressLine2'))->addFlags(new EditField('text')),
            new FkField('country_id', 'countryId', CountryDefinition::class),
            new FkField('country_state_id', 'countryStateId', CountryStateDefinition::class),
            (new ManyToOneAssociationField('country', 'country_id', CountryDefinition::class))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('countryState', 'country_state_id', CountryStateDefinition::class))->addFlags(new EditField(), new LabelProperty('name')),
        ]);
    }
}
