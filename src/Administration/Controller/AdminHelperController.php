<?php declare(strict_types=1);

namespace MoorlFoundation\Administration\Controller;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class AdminHelperController
{
    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly Connection $connection
    )
    {
    }

    #[Route(path: '/api/moorl-foundation/admin-helper/layout-adopt-children/{categoryId}', name: 'api.moorl-foundation.admin-helper.layout-adopt-children', methods: ['GET'])]
    public function layoutAdoptChildren(string $categoryId, Context $context): JsonResponse
    {
        $categoryRepository = $this->definitionInstanceRegistry->getRepository(CategoryDefinition::ENTITY_NAME);

        $criteria = new Criteria([$categoryId]);

        /** @var CategoryEntity $currentCategory */
        $currentCategory = $categoryRepository->search($criteria, $context)->get($categoryId);

        $cmsPageId = $currentCategory->getCmsPageId();

        $sql = <<<SQL
UPDATE `category` SET `cms_page_id` = UNHEX(:cms_page_id) WHERE `path` LIKE :category_id;
SQL;

        $affectedRows = $this->connection->executeStatement($sql, [
            'cms_page_id' => $cmsPageId,
            'category_id' => "%" . $currentCategory->getId() . "%",
        ]);

        return new JsonResponse([
            'affected_rows' => $affectedRows
        ]);
    }

    #[Route(path: '/api/moorl-foundation/admin-helper/layout-adopt-siblings/{categoryId}', name: 'api.moorl-foundation.admin-helper.layout-adopt-siblings', methods: ['GET'])]
    public function layoutAdoptSiblings(string $categoryId, Context $context): JsonResponse
    {
        $categoryRepository = $this->definitionInstanceRegistry->getRepository(CategoryDefinition::ENTITY_NAME);

        $criteria = new Criteria([$categoryId]);

        /** @var CategoryEntity $currentCategory */
        $currentCategory = $categoryRepository->search($criteria, $context)->get($categoryId);

        $cmsPageId = $currentCategory->getCmsPageId();

        $sql = <<<SQL
UPDATE `category` SET `cms_page_id` = UNHEX(:cms_page_id) WHERE `parent_id` = UNHEX(:parent_id);
SQL;

        $affectedRows = $this->connection->executeStatement($sql, [
            'cms_page_id' => $cmsPageId,
            'parent_id' => $currentCategory->getParentId(),
        ]);

        return new JsonResponse([
            'affected_rows' => $affectedRows
        ]);
    }
}
