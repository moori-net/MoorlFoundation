<?php

namespace MoorlFoundation\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use GuzzleHttp\Client;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class ApiController extends AbstractController
{
    private $systemConfigService;
    private $articleRepo;
    private $pluginRepo;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepositoryInterface $articleRepo,
        EntityRepositoryInterface $pluginRepo
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->articleRepo = $articleRepo;
        $this->pluginRepo = $pluginRepo;
    }

    /**
     * @Route("/api/v{version}/moorl-foundation/feed", name="api.moorl-foundation.feed", methods={"GET"}, requirements={"version"="\d+"})
     */
    public function feed(Request $request, Context $context): JsonResponse
    {
        $lastUpdate = $this->systemConfigService->get('MoorlFoundation.config.feedLastUpdate') ?: 0;

        $updateInterval = $this->systemConfigService->get('MoorlFoundation.config.feedUpdateInterval') ?: 3600;
        $nextUpdate = $lastUpdate + $updateInterval;
        $feeds = $this->systemConfigService->get('MoorlFoundation.config.feedUrls');
        $enabled = $this->systemConfigService->get('MoorlFoundation.config.enableFeed');

        if (!$enabled || !$feeds || $nextUpdate > time()) {
            return new JsonResponse([
                'reason' => 'News Feed disabled or empty'
            ]);
        }

        if ($this->systemConfigService->get('MoorlFoundation.config.pluginFeed')) {
            $pluginNames = [];

            $plugins = $this->pluginRepo->search(new Criteria(), $context)->getElements();

            foreach ($plugins as $plugin) {
                $pluginNames[] = $plugin->getName();
            }
        } else {
            $pluginNames = null;
        }

        $language = $this->systemConfigService->get('MoorlFoundation.config.feedLanguage');

        if (!$language) {
            $language = $request->getDefaultLocale();
        }

        $articleCollection = $this->articleRepo->search(new Criteria(), $context)->getEntities();
        $articleArray = [];

        $client = new Client([
            'timeout' => 3.0,
        ]);

        $query = '/moorl-magazine/api/article?' . http_build_query([
                'tags' => $pluginNames,
                'invisible' => 1,
                'seoUrl' => 1,
                'limit' => 50,
                'language' => $language,
                'timestamp' => $lastUpdate
            ]);

        $feeds = explode("\n", $feeds);
        $feeds = array_map('trim', $feeds);

        foreach ($feeds as $feed) {
            $response = $client->request('GET', $feed . $query, ['headers' => ['Accept' => 'application/json', 'Content-type' => 'application/json']]);

            $code = $response->getStatusCode(); // 200
            $reason = $response->getReasonPhrase(); // OK

            if ($code != 200 || $reason != 'OK') {
                return new JsonResponse([
                    'code' => $code,
                    'reason' => $reason,
                    'feed' => $feed
                ]);
            }

            $articles = json_decode($response->getBody(), true);

            foreach ($articles as $article) {
                $hasSeen = $articleCollection->has($article['id']);

                $articleArray[] = [
                    'id' => $article['id'],
                    'mediaUrl' => $article['media']['url'],
                    'articleUrl' => $article['seoUrl'],
                    'author' => $article['author'],
                    'title' => $article['translated']['title'],
                    'teaser' => $article['translated']['teaser'],
                    'content' => $article['translated']['content'],
                    'date' => $article['date'],
                    'hasSeen' => $hasSeen,
                ];
            }
        }

        if (count($articleArray) > 0) {
            $this->articleRepo->upsert($articleArray, $context);

            $this->systemConfigService->set('MoorlFoundation.config.feedLastUpdate', time());
        }

        return new JsonResponse([
            'code' => $code,
            'reason' => $reason,
            'articles' => $articleArray
        ]);
    }
}
