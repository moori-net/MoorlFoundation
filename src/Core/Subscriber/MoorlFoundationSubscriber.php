<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Subscriber;

use Shopware\Core\Content\Media\Event\MediaFileExtensionWhitelistEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MoorlFoundationSubscriber implements EventSubscriberInterface
{
    private SystemConfigService $systemConfigService;

    public function __construct(
        SystemConfigService $systemConfigService
    )
    {
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MediaFileExtensionWhitelistEvent::class => 'onMediaFileExtensionWhitelist'
        ];
    }

    public function onMediaFileExtensionWhitelist(MediaFileExtensionWhitelistEvent $event)
    {
        $whitelist = $event->getWhitelist();
        $whitelistConfig = $this->systemConfigService->get('MoorlFoundation.config.fileExtensions');
        if ($whitelistConfig) {
            $whitelistConfig = explode(",", $whitelistConfig);
            $whitelistConfig = array_map('trim', $whitelistConfig);
            if (is_array($whitelistConfig)) {
                $whitelist = array_merge($whitelist, $whitelistConfig);
            }
        }
        $event->setWhitelist($whitelist);
    }
}
