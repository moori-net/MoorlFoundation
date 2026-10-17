<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldMultiEntityCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\UniqueKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;

$loader = require dirname(__DIR__, 8) . '/vendor/autoload.php';
$loader->addPsr4('MoorlFoundation\\', dirname(__DIR__, 5) . '/src/');

class FieldMultiEntityCollectionTest extends TestCase
{
    #[DataProvider('foreignKeyFlags')]
    public function testReferenceVersionInheritsForeignKeyFlags(array $flags): void
    {
        [$foreignKey, $version, $association] = FieldMultiEntityCollection::getManyToOneFieldItems([
            [OrderLineItemDefinition::class, $flags, [new RestrictDelete()]],
        ]);

        self::assertInstanceOf(FkField::class, $foreignKey);
        self::assertInstanceOf(ReferenceVersionField::class, $version);
        self::assertSame('order_line_item_version_id', $version->getStorageName());
        self::assertSame(OrderLineItemDefinition::class, $version->getVersionReferenceClass());
        self::assertEquals($foreignKey->getFlags(), $version->getFlags());
        self::assertSame($foreignKey->is(Required::class), $version->is(Required::class));
        self::assertSame($foreignKey->is(PrimaryKey::class), $version->is(PrimaryKey::class));
        foreach ($flags as $flag) {
            self::assertSame($flag, $version->getFlag($flag::class));
        }
        self::assertTrue($association->is(RestrictDelete::class));
        self::assertFalse($version->is(RestrictDelete::class));
    }

    public static function foreignKeyFlags(): array
    {
        return [
            'optional unique reference' => [[new UniqueKey('order_unit')]],
            'required reference without implicit primary key' => [[new Required(), new UniqueKey('order_unit')]],
            'explicit primary key' => [[new PrimaryKey(), new Required()]],
            'default flags' => [[]],
        ];
    }

    public function testUnversionedReferenceDoesNotCreateAReferenceVersion(): void
    {
        $fields = FieldMultiEntityCollection::getManyToOneFieldItems([
            [CustomerDefinition::class, [new Required()], []],
        ]);

        self::assertCount(2, $fields);
        foreach ($fields as $field) {
            self::assertNotInstanceOf(ReferenceVersionField::class, $field);
        }
    }
}
