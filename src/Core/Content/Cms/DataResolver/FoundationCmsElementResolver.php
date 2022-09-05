<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\DataResolver;

use MoorlFoundation\Core\Framework\Plugin\Exception\TypePatternException;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Framework\Struct\Struct;

class FoundationCmsElementResolver extends AbstractCmsElementResolver
{
    public function getType(): string
    {
        throw new TypePatternException(self::class);
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $data = $this->getStruct();
        $slot->setData($data);

        foreach ($slot->getFieldConfig() as $key => $config) {
            if (!$config->getValue()) {
                continue;
            }

            $content = null;
            if ($config->isMapped() && $resolverContext instanceof EntityResolverContext) {
                $content = $this->resolveEntityValue($resolverContext->getEntity(), $config->getValue());
            } else if ($config->isStatic()) {
                if ($resolverContext instanceof EntityResolverContext) {
                    $content = $config->getValue();
                    //$content = (string) $this->resolveEntityValues($resolverContext, $config->getValue());
                } else {
                    $content = $config->getValue();
                }
            }
            $data->__set($key, $content);
        }
    }

    public function getStruct(): Struct
    {
        throw new TypePatternException(self::class);
    }
}
