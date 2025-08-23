<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelEventSubscriber implements EventSubscriberInterface
{
    final public const RADIUS_PERSIST_COOKIE = 'radius-persist';

    public function __construct(private readonly SystemConfigService $systemConfigService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            KernelEvents::RESPONSE => 'onResponse'
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($this->systemConfigService->getBool('MoorlFoundation.config.filterRadiusPersist')) {
            $radiusPersistCookie = $request->cookies->get(self::RADIUS_PERSIST_COOKIE);
            if ($radiusPersistCookie !== null) {
                $radiusPersistCookie = json_decode($radiusPersistCookie, true);
                foreach ($radiusPersistCookie as $k => $v) {
                    $request->query->set($k, $v);
                }
            }
        }
    }

    public function onResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($this->systemConfigService->getBool('MoorlFoundation.config.filterRadiusPersist')) {
            $radiusPersist = $request->query->get(self::RADIUS_PERSIST_COOKIE);
            if ($radiusPersist === 'true') {
                $cookie = Cookie::create(self::RADIUS_PERSIST_COOKIE, json_encode([
                    'distance' => $request->query->get('distance', 0),
                    'location' => $request->query->get('location', '')
                ]));
                $cookie->setSecureDefault($request->isSecure());
                $cookie = $cookie->withHttpOnly(false);
                $cookie = $cookie->withExpires(time() + 60 * 60 * 24 * 30);
                $response->headers->setCookie($cookie);
            } elseif ($radiusPersist === 'false') {
                $response->headers->clearCookie(self::RADIUS_PERSIST_COOKIE);
            }
        }
    }
}
