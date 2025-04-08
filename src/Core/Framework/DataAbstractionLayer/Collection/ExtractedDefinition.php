<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Framework\DataAbstractionLayer\Exception\DefinitionNotFoundException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

final class ExtractedDefinition
{
    private const SEP = '_';
    private const ID = 'id';
    private static array $cache = [];
    protected string $entityName;
    protected string $propertyName;
    protected string $collectionName;
    protected string $fkStorageName;
    protected string $fkPropertyName;
    protected string $referenceField;
    protected string $extensionPropertyName;
    protected string $extensionCollectionName;

    public function __construct(
        string $class,
        bool $entityName = true,
        bool $propertyName = false,
        bool $collectionName = false,
        bool $fkStorageName = false,
        bool $fkPropertyName = false,
        bool $referenceField = false,
        bool $extensionPropertyName = false,
        bool $extensionCollectionName = false,
        ?string $replace = null,
        ?string $append = null,
        bool $debug = false
    )
    {
        if (!defined("{$class}::ENTITY_NAME")) {
            throw new DefinitionNotFoundException($class);
        }

        $entity = $class::ENTITY_NAME;

        if ($debug || $entityName || $referenceField) {
            $this->entityName = $replace ? str_replace($replace, "", $entity) : $entity;
        }

        if ($debug || $propertyName) {
            $this->propertyName = self::tryGetPluginConstant(
                "{$class}::PROPERTY_NAME",
                fn() => self::kebabCaseToCamelCase($entity)
            );
        }

        if ($debug || ($propertyName && $collectionName)) {
            $this->collectionName = self::tryGetPluginConstant(
                "{$class}::COLLECTION_NAME",
                fn() => self::kebabCaseToCamelCase(self::getPluralName($this->propertyName))
            );
        }

        if ($debug || $extensionPropertyName || $extensionCollectionName) {
            if ($debug || $extensionPropertyName) {
                $extensionPrefix = explode(self::SEP, $entity)[0];
                if (str_starts_with($this->propertyName ?? "", $extensionPrefix)) {
                    $extensionPrefix = "";
                }

                $this->extensionPropertyName = self::tryGetPluginConstant(
                    "{$class}::EXTENSION_PROPERTY_NAME",
                    fn() => self::kebabCaseToCamelCase(
                        $this->propertyName ? $extensionPrefix . self::SEP . $this->propertyName : $entity
                    )
                );
            }

            if ($debug || ($extensionPropertyName && $extensionCollectionName)) {
                $this->extensionCollectionName = self::tryGetPluginConstant(
                    "{$class}::EXTENSION_COLLECTION_NAME",
                    fn() => self::kebabCaseToCamelCase(
                        self::getPluralName($this->extensionPropertyName)
                    )
                );
            }
        }

        if ($debug || $fkStorageName) {
            $this->fkStorageName = $entity . self::SEP . self::ID;
        }

        if ($debug || ($propertyName && $fkPropertyName)) {
            $this->fkPropertyName = self::kebabCaseToCamelCase($this->propertyName . self::SEP . self::ID);
        }

        if ($debug || $referenceField) {
            $this->referenceField = $this->entityName . $append . self::SEP . self::ID;
        }

        if ($debug) {
            dd($this);
        }
    }

    public static function get(string $class, ?string $replace = null, ?string $append = null, bool $debug = false): self
    {
        if (isset(self::$cache[$class])) {
            return self::$cache[$class];
        }

        return self::$cache[$class] = new self(
            $class,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            $replace,
            $append,
            $debug
        );
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

    public function getReferenceField(): string
    {
        return $this->referenceField;
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