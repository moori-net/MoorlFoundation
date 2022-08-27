<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Subscriber;

use MoorlFoundation\Core\Service\TranslationService;
use Shopware\Core\Content\Media\Event\MediaFileExtensionWhitelistEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MoorlFoundationSubscriber implements EventSubscriberInterface
{
    private SystemConfigService $systemConfigService;
    private TranslationService $translationService;

    public function __construct(
        SystemConfigService $systemConfigService,
        TranslationService $translationService
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->translationService = $translationService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MediaFileExtensionWhitelistEvent::class => 'onMediaFileExtensionWhitelist',
            'product.written' => 'onEntityWrittenEvent',
            'category.written' => 'onEntityWrittenEvent',
            'property_group.written' => 'onEntityWrittenEvent',
            'property_group_option.written' => 'onEntityWrittenEvent',
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

    public function onEntityWrittenEvent(EntityWrittenEvent $event): void
    {
        $this->translationService->translate($event->getEntityName(), $event->getWriteResults(), $event->getContext());
    }
}
