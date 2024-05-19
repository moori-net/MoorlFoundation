<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\CartCombinationDiscount;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void            add(CartCombinationDiscountTranslationEntity $entity)
 * @method void            set(string $key, CartCombinationDiscountTranslationEntity $entity)
 * @method CartCombinationDiscountTranslationEntity[]    getIterator()
 * @method CartCombinationDiscountTranslationEntity[]    getElements()
 * @method CartCombinationDiscountTranslationEntity|null get(string $key)
 * @method CartCombinationDiscountTranslationEntity|null first()
 * @method CartCombinationDiscountTranslationEntity|null last()
 */
class CartCombinationDiscountTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CartCombinationDiscountTranslationEntity::class;
    }
}
