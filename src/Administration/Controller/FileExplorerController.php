<?php declare(strict_types=1);

namespace MoorlFoundation\Administration\Controller;

use MoorlFoundation\Core\Service\ClientService;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
            $this->clientService->listContents($request->request->get('clientId'), $request->request->get('directory'), $context)
        );
    }

    /**
     * @Route("/api/moorl-foundation/file-explorer/create-dir", name="api.moorl-foundation.file-explorer.create-dir", methods={"POST"})
     */
    public function createDir(Request $request, Context $context): JsonResponse
    {
        $this->clientService->createDir($request->request->get('clientId'), $request->request->get('dirname'), $context);

        return new JsonResponse([]);
    }

    /**
     * @Route("/api/moorl-foundation/file-explorer/read", name="api.moorl-foundation.file-explorer.read", methods={"POST"})
     */
    public function read(Request $request, Context $context): Response
    {
        $clientId = $request->request->get('clientId');
        $path = $request->request->get('path');

        $contents = $this->clientService->read($clientId, $path, $context);

        $response = new Response($contents);
        $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, basename($path));
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', $this->clientService->getMimetype($clientId, $path, $context));
        $response->headers->set('Content-Length', $this->clientService->getSize($clientId, $path, $context));
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    /**
     * @Route("/api/moorl-foundation/file-explorer/read-stream", name="api.moorl-foundation.file-explorer.read-stream", methods={"POST"})
     */
    public function readStream(Request $request, Context $context): Response
    {
        $clientId = $request->request->get('clientId');
        $path = $request->request->get('path');

        $stream = $this->clientService->readStream($clientId, $path, $context);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($stream) {
            while (!feof($stream)) {
                print fgets($stream, 1024);
                flush();
            }
            fclose($stream);
        });

        $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, basename($path));
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Length', $this->clientService->getSize($clientId, $path, $context));
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }
}
