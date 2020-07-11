<?php

namespace MoorlFoundation\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use MoorlFoundation\Core\Content\Article\ArticleCollection;
use MoorlFoundation\Core\Content\Article\ArticleEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use GuzzleHttp\Client;
use MoorlFoundation\MoorlFoundation;
use DateTimeImmutable;

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
        // If feed is turned of by plugin config, do nothing
        if (!$this->systemConfigService->get('MoorlFoundation.config.enableFeed')) {
            return new JsonResponse([
                'reason' => 'moori News Feed disabled by user'
            ]);
        }

        // Step 1: Collect all technical plugin names
        $pluginNames = [];

        $plugins = $this->pluginRepo->search(new Criteria(), $context)->getElements();

        foreach ($plugins as $plugin) {
            $pluginNames[] = $plugin->getName();
        }

        // Step 2: Get last 10 articles from feed
        $query = http_build_query([
            //'tags' => $pluginNames,
            'invisible' => 1,
            'seoUrl' => 1,
            'limit' => 10,
            'language' => 'en'
        ]);

        $query = MoorlFoundation::FEED_URL . "?" . $query;

        $client = new Client([
            'timeout'  => 2.0,
        ]);

        $response = $client->request('GET', $query, ['headers' => ['Accept' => 'application/json', 'Content-type' => 'application/json']]);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK

        if ($code != 200 || $reason != 'OK') {
            return new JsonResponse([
                'code' => $code,
                'reason' => $reason
            ]);
        }

        $articles = json_decode($response->getBody(), true);

        // Step 3: Add new articles to database, update the others
        $articleCollection = $this->articleRepo->search(new Criteria(), $context)->getEntities();
        $articleArray = [];

        foreach ($articles as $article) {
            $isOld = $articleCollection->has($article['id']);

            //if (!$isOld) {
                $articleArray[] = [
                    'id' => $article['id'],
                    'mediaUrl' => $article['media']['url'],
                    'articleUrl' => $article['seoUrl'],
                    'author' => $article['author'],
                    'title' => $article['title'],
                    'teaser' => $article['teaser'],
                    'content' => $article['content'],
                    'date' => $article['date'],
                ];
            //}
        }

        if (count($articleArray) > 0) {
            $this->articleRepo->upsert($articleArray, $context);
        }

        // Step 4: Return new articles and add them to notifications
        return new JsonResponse([
            'code' => $code,
            'reason' => $reason,
            'articles' => $articleArray
        ]);
    }
}
