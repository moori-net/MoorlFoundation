<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Cache;

use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CustomerGroupCacheKeyGenerator extends EntityCacheKeyGenerator
{
    public function __construct(
        private readonly EntityCacheKeyGenerator $decorated,
        private readonly SystemConfigService $systemConfigService
    )
    {
    }

    public function getDecorated(): EntityCacheKeyGenerator
    {
        return $this->decorated;
    }

    public function getSalesChannelContextHash(SalesChannelContext $context, array $areas = []): string
    {
        $hash = $this->decorated->getSalesChannelContextHash($context, $areas);

        if (!$this->systemConfigService->getBool('MoorlFoundation.config.cmpCustomerGroupCacheKeyGenerator', $context->getSalesChannelId())) {
            return $hash;
        }

        $parts = [
            $hash,
            $context->getCurrentCustomerGroup()->getId()
        ];

        return md5(json_encode($parts));
    }
}
