<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityBreadcrumb;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\Language\LanguageEntity;
class EntityBreadcrumbUpdater
{
    public function __construct(
        private readonly DefinitionInstanceRegistry $registry,
        private readonly Connection $connection
    )
    {
    }

    public function update(array $ids, string $entityName, Context $context): void
    {
        if (empty($ids)) {
            return;
        }

        $definition = $this->registry->getByEntityName($entityName);
        $versionId = Uuid::fromHexToBytes($context->getVersionId());

        $query = $this->connection->createQueryBuilder();
        $query->select($entityName.'.path');
        $query->from($entityName);
        $query->where($entityName. '.id IN (:ids)');
        if ($definition->isVersionAware()) {
            $query->andWhere($entityName . '.version_id = :version');
        }
        $query->setParameter('version', $versionId);
        $query->setParameter('ids', Uuid::fromHexToBytesList($ids), ArrayParameterType::STRING);

        $paths = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);

        $all = $ids;
        foreach ($paths as $path) {
            $path = explode('|', (string) $path);
            foreach ($path as $id) {
                $all[] = $id;
            }
        }

        $all = array_filter(array_values(array_keys(array_flip($all))));

        $languageRepository = $this->registry->getRepository(LanguageDefinition::ENTITY_NAME);
        $languages = $languageRepository->search(new Criteria(), $context);

        /** @var LanguageEntity $language */
        foreach ($languages as $language) {
            $context = new Context(
                new SystemSource(),
                [],
                Defaults::CURRENCY,
                array_filter([$language->getId(), $language->getParentId(), Defaults::LANGUAGE_SYSTEM]),
                Defaults::LIVE_VERSION
            );

            $this->updateLanguage($ids, $entityName, $context, $all);
        }
    }

    private function updateLanguage(array $ids, string $entityName, Context $context, array $all): void
    {
        $definition = $this->registry->getByEntityName($entityName);
        $versionId = Uuid::fromHexToBytes($context->getVersionId());
        $languageId = Uuid::fromHexToBytes($context->getLanguageId());

        $entityRepository = $this->registry->getRepository($entityName);
        $entities = $entityRepository->search(new Criteria($all), $context)->getEntities();

        $sql = "INSERT INTO `#entity#_translation` (`#entity#_id`, ";
        if ($definition->isVersionAware()) {
            $sql .= "`#entity#_version_id`, ";
        }
        if ($definition->getField('breadcrumbPlain')) {
            $sql .= "`breadcrumb_plain`, ";
        }
        $sql .= "`language_id`, `breadcrumb`, `created_at`) VALUES (:entityId, ";
        if ($definition->isVersionAware()) {
            $sql .= ":versionId, ";
        }
        if ($definition->getField('breadcrumbPlain')) {
            $sql .= ":breadcrumbPlain, ";
        }
        $sql .= ":languageId, :breadcrumb, DATE(NOW())) ON DUPLICATE KEY UPDATE `breadcrumb` = :breadcrumb";
        if ($definition->getField('breadcrumbPlain')) {
            $sql .= ", `breadcrumb_plain` = :breadcrumbPlain";
        }
        $sql .= ";";

        $sql = str_replace(
            ['#entity#'],
            [$entityName],
            $sql
        );

        $update = $this->connection->prepare($sql);
        $update = new RetryableQuery($this->connection, $update);

        foreach ($ids as $id) {
            $path = $this->buildBreadcrumb($id, $entities);

            $update->execute([
                'entityId' => Uuid::fromHexToBytes($id),
                'versionId' => $versionId,
                'languageId' => $languageId,
                'breadcrumb' => json_encode($path),
                'breadcrumbPlain' => implode('|', $path)
            ]);
        }
    }

    private function buildBreadcrumb(string $id, EntityCollection $entities): array
    {
        $entity = $entities->get($id);
        if (!$entity) {
            throw new CategoryNotFoundException($id);
        }

        $breadcrumb = [];
        if ($entity->getParentId()) {
            $breadcrumb = $this->buildBreadcrumb($entity->getParentId(), $entities);
        }

        $breadcrumb[$entity->getId()] = $entity->getTranslation('name');

        return $breadcrumb;
    }
}
