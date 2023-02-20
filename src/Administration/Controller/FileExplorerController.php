<?php declare(strict_types=1);

namespace MoorlFoundation\Administration\Controller;

use MoorlFoundation\Core\Service\ClientService;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class FileExplorerController
{
    private ClientService $clientService;

    public function __construct(
        ClientService $clientService
    )
    {
        $this->clientService = $clientService;
    }

    /**
     * @Route("/api/moorl-foundation/file-explorer/list-contents", name="api.moorl-foundation.file-explorer.list-contents", methods={"POST"})
     */
    public function listContents(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->clientService->listContents($request->get('clientId'), $request->get('directory'), $context)
        );
    }

    /**
     * @Route("/api/moorl-foundation/file-explorer/create-dir", name="api.moorl-foundation.file-explorer.create-dir", methods={"POST"})
     */
    public function createDir(Request $request, Context $context): JsonResponse
    {
        $this->clientService->createDir($request->get('clientId'), $request->get('dirname'), $context);

        return new JsonResponse([]);
    }
}
