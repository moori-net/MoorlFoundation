<?php declare(strict_types=1);

namespace MoorlFoundation\Administration\Controller;

use MoorlFoundation\Core\Service\ClientService;
use MoorlFoundation\Core\Service\DataService;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route(defaults: ['_routeScope' => ['api']])]
class SettingsController
{
    public function __construct(
        private readonly DataService $dataService,
        private readonly ?ClientService $clientService = null
    )
    {
    }

    #[Route(path: '/api/moorl-foundation/settings/client/test/{clientId}', name: 'api.moorl-foundation.settings.client.test', methods: ['GET'])]
    public function clientTest(string $clientId, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->clientService->test($clientId, $context)
        );
    }

    #[Route(path: '/api/moorl-foundation/settings/client/options', name: 'api.moorl-foundation.settings.client.options', methods: ['GET'])]
    public function clientOptions(): JsonResponse
    {
        return new JsonResponse(
            $this->clientService->getOptions()
        );
    }

    #[Route(path: '/api/moorl-foundation/settings/demo-data/options', name: 'api.moorl-foundation.settings.demo-data.options', methods: ['GET'])]
    public function demoDataOptions(): JsonResponse
    {
        return new JsonResponse(
            $this->dataService->getOptions()
        );
    }

    #[Route(path: '/api/moorl-foundation/settings/demo-data/install', name: 'api.moorl-foundation.settings.demo-data.install', methods: ['POST'])]
    public function demoDataInstall(Request $request): JsonResponse
    {
        if ($request->request->get('salesChannelId') && !in_array($request->request->get('salesChannelId'), ['undefined','null'])) {
            $this->dataService->setSalesChannelId($request->request->get('salesChannelId'));
        }

        $this->dataService->remove(
            $request->request->get('pluginName'),
            $request->request->get('type', 'demo'),
            $request->request->get('name')
        );
        $this->dataService->install(
            $request->request->get('pluginName'),
            $request->request->get('type', 'demo'),
            $request->request->get('name')
        );

        return new JsonResponse([]);
    }

    #[Route(path: '/api/moorl-foundation/settings/demo-data/remove', name: 'api.moorl-foundation.settings.demo-data.remove', methods: ['POST'])]
    public function demoDataRemove(Request $request): JsonResponse
    {
        if ($request->request->get('salesChannelId') && !in_array($request->request->get('salesChannelId'), ['undefined','null'])) {
            $this->dataService->setSalesChannelId($request->request->get('salesChannelId'));
        }

        $this->dataService->remove($request->request->get('pluginName'), 'demo', $request->request->get('name'));

        return new JsonResponse([]);
    }
}
