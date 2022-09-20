<?php declare(strict_types=1);

namespace MoorlFoundation\Administration\Controller;

use MoorlFoundation\Core\Service\DataService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SettingsController
 * @Route(defaults={"_routeScope"={"api"}})
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
     * @Route("/api/moorl-foundation/settings/demo-data/install", name="api.moorl-foundation.settings.demo-data.install", methods={"POST"})
     */
    public function demoDataInstall(Request $request): JsonResponse
    {
        if ($request->get('salesChannelId') && !in_array($request->get('salesChannelId'), ['undefined','null'])) {
            $this->dataService->setSalesChannelId($request->get('salesChannelId'));
        }

        $this->dataService->remove($request->get('pluginName'), 'demo', $request->get('name'));
        $this->dataService->install($request->get('pluginName'), 'demo', $request->get('name'));

        return new JsonResponse([]);
    }

    /**
     * @Route("/api/moorl-foundation/settings/demo-data/remove", name="api.moorl-foundation.settings.demo-data.remove", methods={"POST"})
     */
    public function demoDataRemove(Request $request): JsonResponse
    {
        if ($request->get('salesChannelId') && !in_array($request->get('salesChannelId'), ['undefined','null'])) {
            $this->dataService->setSalesChannelId($request->get('salesChannelId'));
        }

        $this->dataService->remove($request->get('pluginName'), 'demo', $request->get('name'));

        return new JsonResponse([]);
    }
}
