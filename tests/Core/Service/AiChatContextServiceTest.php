<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Core\Service;

require_once dirname(__DIR__, 6) . '/vendor/autoload.php';

$contextServiceFile = dirname(__DIR__, 3) . '/src/Core/Service/AiChatContextService.php';
if (file_exists($contextServiceFile)) {
    require_once $contextServiceFile;
}

use MoorlFoundation\Core\Service\AiChatContextService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class AiChatContextServiceTest extends TestCase
{
    public function testBuildsInstructionsAndStrictSchemaForAllowedScalarFields(): void
    {
        self::assertTrue(class_exists(AiChatContextService::class));

        $registry = $this->createMock(DefinitionInstanceRegistry::class);
        $definition = $this->createDefinition($registry);
        $registry->expects(self::once())->method('getByEntityName')->with('moorl_example')->willReturn($definition);

        $result = (new AiChatContextService($registry, new NullLogger()))->build([
            'entity' => 'moorl_example',
            'labelProperty' => 'title',
            'fields' => [
                'title' => ['type' => 'string', 'required' => true],
                'sortOrder' => ['type' => 'int', 'required' => false],
                'weight' => ['type' => 'float', 'required' => false],
                'enabled' => ['type' => 'bool', 'required' => false],
            ],
        ], [
            'title' => 'Unkorrigierter Text',
            'enabled' => false,
            'nestedReadOnlyData' => ['id' => 'association-id'],
        ]);

        self::assertSame([
            'title' => 'string',
            'sortOrder' => 'integer',
            'weight' => 'number',
            'enabled' => 'boolean',
        ], $result['allowedFields']);
        self::assertStringContainsString('moorl_example', $result['instructions']);
        self::assertStringNotContainsString('nestedReadOnlyData', $result['instructions']);
        self::assertSame([
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
                                'field' => ['type' => 'string', 'enum' => ['title', 'sortOrder', 'weight', 'enabled']],
                                'value' => [
                                    'anyOf' => [
                                        ['type' => 'string'],
                                        ['type' => 'integer'],
                                        ['type' => 'number'],
                                        ['type' => 'boolean'],
                                    ],
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
        ], $result['requestOptions']['text']['format']);
    }

    public function testRejectsInvalidContextAndOversizedItemSnapshots(): void
    {
        self::assertTrue(class_exists(AiChatContextService::class));

        $registry = $this->createMock(DefinitionInstanceRegistry::class);
        $definition = $this->createDefinition($registry);
        $registry->method('getByEntityName')->with('moorl_example')->willReturn($definition);
        $service = new AiChatContextService($registry, new NullLogger());

        foreach ([
            [[], []],
            [[
                'entity' => 'moorl_example',
                'labelProperty' => 'title',
                'fields' => [],
            ], ['title' => 'Draft']],
            [[
                'entity' => 'moorl_example',
                'labelProperty' => 'title',
                'fields' => ['customFields' => ['type' => 'json_object', 'required' => false]],
            ], ['title' => 'Draft']],
            [[
                'entity' => 'nonexistent_entity',
                'labelProperty' => 'madeUp',
                'fields' => ['madeUp' => ['type' => 'string', 'required' => false]],
            ], ['madeUp' => 'Draft']],
            [[
                'entity' => 'moorl_example',
                'labelProperty' => 'title',
                'fields' => ['createdAt' => ['type' => 'datetime', 'required' => false]],
            ], ['createdAt' => '2026-09-29T00:00:00+00:00']],
            [[
                'entity' => 'moorl_example',
                'labelProperty' => 'title',
                'fields' => ['title' => ['type' => 'string', 'required' => true]],
            ], ['title' => str_repeat('a', 100001)]],
            [[
                'entity' => str_repeat('a', 200001),
                'labelProperty' => 'title',
                'fields' => ['title' => ['type' => 'string', 'required' => true]],
            ], ['title' => 'Draft']],
        ] as [$context, $item]) {
            self::assertNull($service->build($context, $item));
        }
    }

    public function testAcceptsTranslatedTextFields(): void
    {
        $registry = $this->createMock(DefinitionInstanceRegistry::class);
        $definition = new class extends EntityDefinition {
            public function getEntityName(): string
            {
                return 'studygood_test_question';
            }

            protected function defineFields(): FieldCollection
            {
                return new FieldCollection([
                    new TranslatedField('content'),
                    new IntField('points', 'points'),
                ]);
            }
        };
        $definition->compile($registry);
        $registry->expects(self::once())->method('getByEntityName')->with('studygood_test_question')->willReturn($definition);

        $result = (new AiChatContextService($registry, new NullLogger()))->build([
            'entity' => 'studygood_test_question',
            'labelProperty' => 'content',
            'fields' => [
                'content' => ['type' => 'string', 'required' => true],
                'points' => ['type' => 'int', 'required' => false],
            ],
        ], [
            'content' => 'Bitte korrigieren.',
            'points' => 1,
        ]);

        self::assertNotNull($result);
        self::assertSame(['content' => 'string', 'points' => 'integer'], $result['allowedFields']);
    }

    public function testBuildsDirectFieldSnapshotFromAnOversizedItem(): void
    {
        $registry = $this->createMock(DefinitionInstanceRegistry::class);
        $definition = $this->createDefinition($registry);
        $registry->expects(self::once())->method('getByEntityName')->with('moorl_example')->willReturn($definition);

        $result = (new AiChatContextService($registry, new NullLogger()))->build([
            'entity' => 'moorl_example',
            'labelProperty' => 'title',
            'fields' => [
                'title' => ['type' => 'string', 'required' => true],
            ],
        ], [
            'title' => 'Artikelentwurf',
            'media' => ['metadata' => str_repeat('x', 100001)],
        ]);

        self::assertNotNull($result);
        self::assertStringContainsString('"title":"Artikelentwurf"', $result['instructions']);
        self::assertStringNotContainsString('"media"', $result['instructions']);
    }

    public function testLogsRejectedBrowserFieldMetadataWithoutItemContent(): void
    {
        $registry = $this->createMock(DefinitionInstanceRegistry::class);
        $definition = $this->createDefinition($registry);
        $registry->expects(self::once())->method('getByEntityName')->with('moorl_example')->willReturn($definition);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('warning')
            ->with(
                'AI chat context was rejected.',
                self::callback(static fn (array $context): bool =>
                    $context['reason'] === 'field_missing_from_entity_definition'
                    && $context['entity'] === 'moorl_example'
                    && $context['field'] === 'unknown'
                    && $context['browserFields'] === ['title', 'unknown']
                    && $context['entityFields'] === ['title', 'sortOrder', 'weight', 'enabled']
                    && !array_key_exists('item', $context)
                    && !array_key_exists('itemContent', $context)
                )
            );

        $result = (new AiChatContextService($registry, $logger))->build([
            'entity' => 'moorl_example',
            'labelProperty' => 'title',
            'fields' => [
                'title' => ['type' => 'string', 'required' => true],
                'unknown' => ['type' => 'string', 'required' => false],
            ],
        ], ['title' => 'Nicht ins Log schreiben']);

        self::assertNull($result);
    }

    private function createDefinition(DefinitionInstanceRegistry $registry): EntityDefinition
    {
        $definition = new class extends EntityDefinition {
            public function getEntityName(): string
            {
                return 'moorl_example';
            }

            protected function defineFields(): FieldCollection
            {
                return new FieldCollection([
                    new StringField('title', 'title'),
                    new IntField('sort_order', 'sortOrder'),
                    new FloatField('weight', 'weight'),
                    new BoolField('enabled', 'enabled'),
                ]);
            }
        };
        $definition->compile($registry);

        return $definition;
    }
}
