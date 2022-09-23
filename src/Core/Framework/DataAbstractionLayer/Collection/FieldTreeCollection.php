<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Framework\DataAbstractionLayer\Field\ChildCountField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TreeLevelField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TreePathField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldTreeCollection extends FieldCollection
{
    public static function getFieldItems(string $referenceClass): array
    {
        return [
            new ParentFkField( $referenceClass),
            new FkField('after_id', 'afterId', $referenceClass),
            new ChildCountField(),
            new TreeLevelField('level', 'level'),
            new TreePathField('path', 'path'),
            new ChildrenAssociationField($referenceClass),
        ];
    }
}
