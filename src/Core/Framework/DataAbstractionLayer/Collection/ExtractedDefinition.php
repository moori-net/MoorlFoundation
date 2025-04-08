<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\DefinitionNotFoundException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

final class ExtractedDefinition
{
    private const SEP = '_';
    private const ID = 'id';

    private static array $cache = [];
    private static array $versionDefinitions = [
        CategoryDefinition::class,
        ProductDefinition::class,
        OrderDefinition::class,
        CmsBlockDefinition::class,
        CmsSlotDefinition::class
    ];

    protected string $entityName;
    protected string $propertyName;
    protected string $collectionName;
    protected string $fkStorageName;
    protected string $fkPropertyName;
    protected string $extensionPropertyName;
    protected string $extensionCollectionName;

    public function __construct(string $class,)
    {
        $entity = self::tryGetPluginConstant(
            "{$class}::ENTITY_NAME",
            fn() => throw new DefinitionNotFoundException($class)
        );

        $this->entityName = $entity;

        $this->propertyName = self::tryGetPluginConstant(
            "{$class}::PROPERTY_NAME",
            fn() => self::kebabCaseToCamelCase($entity)
        );

        $this->collectionName = self::tryGetPluginConstant(
            "{$class}::COLLECTION_NAME",
            fn() => self::kebabCaseToCamelCase(self::getPluralName($this->propertyName))
        );

        $extensionPrefix = explode(self::SEP, $entity)[0];
        if (str_starts_with($this->propertyName, $extensionPrefix)) {
            $extensionPrefix = "";
        }

        $this->extensionPropertyName = self::tryGetPluginConstant(
            "{$class}::EXTENSION_PROPERTY_NAME",
            fn() => self::kebabCaseToCamelCase(
                $this->propertyName ? $extensionPrefix . self::SEP . $this->propertyName : $entity
            )
        );

        $this->extensionCollectionName = self::tryGetPluginConstant(
            "{$class}::EXTENSION_COLLECTION_NAME",
            fn() => self::kebabCaseToCamelCase(
                self::getPluralName($this->extensionPropertyName)
            )
        );

        $this->fkStorageName = $entity . self::SEP . self::ID;

        $this->fkPropertyName = self::kebabCaseToCamelCase($this->propertyName . self::SEP . self::ID);
    }

    public static function hasClass(string $class, array $instances): bool
    {
        foreach ($instances as $instance) {
            if ($class === $instance::class) {
                return true;
            }
        }

        return false;
    }

    public static function addVersionDefinition(string $class): void
    {
        if (self::isVersionDefinition($class)) {
            return;
        }

        self::$versionDefinitions[] = $class;
    }

    public static function isVersionDefinition(string $class): bool
    {
        return in_array($class, self::$versionDefinitions);
    }

    public static function get(string $class): self
    {
        if (isset(self::$cache[$class])) {
            return self::$cache[$class];
        }

        return self::$cache[$class] = new self($class);
    }

    public function getExtensionPropertyName(): string
    {
        return $this->extensionPropertyName;
    }

    public function getExtensionCollectionName(): string
    {
        return $this->extensionCollectionName;
    }

    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getFkStorageName(): string
    {
        return $this->fkStorageName;
    }

    public function getFkPropertyName(): string
    {
        return $this->fkPropertyName;
    }

    private static function tryGetPluginConstant(string $constantName, callable $onNotDefined): string
    {
        return (string) defined($constantName) ? constant($constantName) : $onNotDefined();
    }

    private static function getPluralName(string $string): string
    {
        return $string[-1] === 'y' ? substr($string, 0, -1) . 'ies' : $string . 's';
    }

    private static function kebabCaseToCamelCase(string $string): string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->denormalize(str_replace('-', '_', $string));
    }
}