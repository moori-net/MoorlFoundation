<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Computed;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;

class AiChatContextService
{
    private const MAX_ITEM_SNAPSHOT_BYTES = 100000;
    private const MAX_CONTEXT_BYTES = 20000;

    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly LoggerInterface $clientLogger
    )
    {
    }

    public function build(array $context, array $item): ?array
    {
        if (!is_string($context['entity'] ?? null) || $context['entity'] === '' || !is_string($context['labelProperty'] ?? null) || $item === []) {
            $this->logRejectedContext('invalid_context_shape', $context);
            return null;
        }

        try {
            $serializedContext = json_encode($context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException) {
            $this->logRejectedContext('context_not_json_serializable', $context);
            return null;
        }

        if (strlen($serializedContext) > self::MAX_CONTEXT_BYTES) {
            $this->logRejectedContext('context_too_large', $context);
            return null;
        }

        $entityFields = $this->getEntityFields($context['entity']);
        if ($entityFields === null) {
            return null;
        }

        $allowedFields = $this->normalizeFields($context, $entityFields);

        if ($allowedFields === null || $allowedFields === []) {
            return null;
        }

        $itemSnapshot = $this->createItemSnapshot($item, $allowedFields);

        try {
            $itemJson = json_encode($itemSnapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException) {
            $this->logRejectedContext('item_not_json_serializable', $context, $entityFields);
            return null;
        }

        if (strlen($itemJson) > self::MAX_ITEM_SNAPSHOT_BYTES) {
            $this->logRejectedContext('item_too_large', $context, $entityFields);
            return null;
        }

        try {
            $contextJson = json_encode([
                'entity' => $context['entity'],
                'labelProperty' => $context['labelProperty'],
                'fields' => $allowedFields,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException) {
            $this->logRejectedContext('normalized_context_not_json_serializable', $context, $entityFields);
            return null;
        }

        return [
            'instructions' => sprintf(
                "You assist with the following Shopware entity. The item snapshot is read-only context. Only propose changes for fields declared in the entity context. Do not propose nested values, associations, translations, custom fields, or additional fields.\n\nEntity context:\n%s\n\nCurrent item snapshot:\n%s",
                $contextJson,
                $itemJson
            ),
            'allowedFields' => $allowedFields,
            'requestOptions' => [
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'moorl_ai_chat_response',
                        'strict' => true,
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'message' => ['type' => 'string'],
                                'changes' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'field' => ['type' => 'string', 'enum' => array_keys($allowedFields)],
                                            'value' => [
                                                'anyOf' => array_map(
                                                    static fn (string $type): array => ['type' => $type],
                                                    array_values(array_unique($allowedFields))
                                                ),
                                            ],
                                        ],
                                        'required' => ['field', 'value'],
                                        'additionalProperties' => false,
                                    ],
                                ],
                            ],
                            'required' => ['message', 'changes'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getEntityFields(string $entity): ?array
    {
        try {
            $definition = $this->definitionInstanceRegistry->getByEntityName($entity);
        } catch (\Throwable $exception) {
            $this->clientLogger->error('AI chat entity definition could not be resolved.', [
                'entity' => $entity,
                'exceptionClass' => $exception::class,
                'exceptionMessage' => $exception->getMessage(),
            ]);

            return null;
        }

        $fields = [];

        foreach ($definition->getFields() as $field) {
            $property = $field->getPropertyName();

            if (in_array($property, ['id', 'versionId', 'createdAt', 'updatedAt', 'translations', 'customFields'], true) || $field->is(Computed::class) || $field->is(WriteProtected::class)) {
                continue;
            }

            $type = $this->getFieldType($field);

            if ($type !== null) {
                $fields[$property] = $type;
            }
        }

        return $fields;
    }

    private function getFieldType(Field $field): ?string
    {
        return match (true) {
            $field instanceof StringField, $field instanceof LongTextField, $field instanceof DateField, $field instanceof DateTimeField, $field instanceof TranslatedField => 'string',
            $field instanceof IntField => 'integer',
            $field instanceof FloatField => 'number',
            $field instanceof BoolField => 'boolean',
            default => null,
        };
    }

    private function normalizeFields(array $context, array $entityFields): ?array
    {
        $fields = $context['fields'] ?? null;

        if (!is_array($fields) || $fields === []) {
            $this->logRejectedContext('fields_missing', $context, $entityFields);
            return null;
        }

        $typeMap = [
            'string' => 'string',
            'text' => 'string',
            'html' => 'string',
            'date' => 'string',
            'datetime' => 'string',
            'time' => 'string',
            'int' => 'integer',
            'float' => 'number',
            'bool' => 'boolean',
        ];
        $normalizedFields = [];

        foreach ($fields as $name => $field) {
            if (!is_string($name) || !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $name)) {
                $this->logRejectedContext('invalid_field_name', $context, $entityFields, is_string($name) ? $name : null);
                return null;
            }

            if (!is_array($field) || !is_string($field['type'] ?? null) || !is_bool($field['required'] ?? null)) {
                $this->logRejectedContext('invalid_field_metadata', $context, $entityFields, $name);
                return null;
            }

            if (!isset($typeMap[$field['type']])) {
                $this->logRejectedContext('unsupported_browser_field_type', $context, $entityFields, $name, $field['type']);
                return null;
            }

            if (!isset($entityFields[$name])) {
                $this->logRejectedContext('field_missing_from_entity_definition', $context, $entityFields, $name, $field['type']);
                return null;
            }

            if ($typeMap[$field['type']] !== $entityFields[$name]) {
                $this->logRejectedContext('field_type_mismatch', $context, $entityFields, $name, $field['type']);
                return null;
            }

            $normalizedFields[$name] = $entityFields[$name];
        }

        return $normalizedFields;
    }

    private function createItemSnapshot(array $item, array $allowedFields): array
    {
        $snapshot = [];

        foreach (array_keys($allowedFields) as $field) {
            if (!array_key_exists($field, $item)) {
                continue;
            }

            $value = $item[$field];

            if (is_string($value) || is_int($value) || is_float($value) || is_bool($value) || $value === null) {
                $snapshot[$field] = $value;
            }
        }

        return $snapshot;
    }

    private function logRejectedContext(
        string $reason,
        array $context,
        array $entityFields = [],
        ?string $field = null,
        ?string $browserFieldType = null
    ): void {
        $browserFields = is_array($context['fields'] ?? null)
            ? array_values(array_filter(array_slice(array_keys($context['fields']), 0, 100), 'is_string'))
            : [];

        $this->clientLogger->warning('AI chat context was rejected.', array_filter([
            'reason' => $reason,
            'entity' => is_string($context['entity'] ?? null) ? $context['entity'] : null,
            'labelProperty' => is_string($context['labelProperty'] ?? null) ? $context['labelProperty'] : null,
            'field' => $field,
            'browserFieldType' => $browserFieldType,
            'browserFields' => $browserFields,
            'entityFields' => array_keys($entityFields),
        ], static fn (mixed $value): bool => $value !== null));
    }
}
