<?php

namespace MoorlFoundation\Administration\Controller;

use MoorlFoundation\Core\Service\DataService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingsController
 * @package Appflix\DewaShop\Administration\Controller
 * @RouteScope(scopes={"api"})
 */
class SettingsController
{
    private DataService $dataService;

    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }

    /**
     * @Route("/api/moorl-foundation/settings/demo-data/options", name="api.moorl-foundation.settings.demo-data.options", methods={"GET"})
     */
    public function demoDataOptions(): JsonResponse
    {
        return new JsonResponse(
            $this->dataService->getOptions()
        );
    }

    /**
     * @Route("/api/moorl-foundation/settings/demo-data/install/{pluginName}/{salesChannelId}", name="api.moorl-foundation.settings.demo-data.install", methods={"GET"})
     */
    public function demoDataInstall(?string $pluginName = null, ?string $salesChannelId = null): JsonResponse
    {
        if ($salesChannelId && !in_array($salesChannelId, ['undefined','null'])) {
            $this->dataService->setSalesChannelId($salesChannelId);
        }

        $this->dataService->remove($pluginName, 'demo');
        $this->dataService->install($pluginName, 'demo');

        return new JsonResponse([]);
    }

    /**
     * @Route("/api/moorl-foundation/settings/demo-data/remove/{pluginName}/{salesChannelId}", name="api.moorl-foundation.settings.demo-data.remove", methods={"GET"})
     */
    public function demoDataRemove(?string $pluginName = null, ?string $salesChannelId = null): JsonResponse
    {
        if ($salesChannelId && !in_array($salesChannelId, ['undefined','null'])) {
            $this->dataService->setSalesChannelId($salesChannelId);
        }

        $this->dataService->remove($pluginName, 'demo');

        return new JsonResponse([]);
    }
}
