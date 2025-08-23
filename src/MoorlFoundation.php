<?php declare(strict_types=1);

namespace MoorlFoundation;

use MoorlFoundation\Core\PluginLifecycleHelper;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class MoorlFoundation extends Plugin
{
    final public const NAME = 'MoorlFoundation';
    final public const DATA_CREATED_AT = '2001-11-11 11:11:11.111';
    final public const SHOPWARE_TABLES = [
        'import_export_profile',
        'category',
        'category_translation',
        'cms_page',
        'cms_page_translation',
        'cms_block',
        'cms_slot',
        'cms_slot_translation',
        'media_default_folder',
    ];
    final public const INHERITANCES = [];
    final public const PLUGIN_TABLES = [
        'moorl_cms_element_config',
        'moorl_location',
        'moorl_location_cache',
        'moorl_sorting',
        'moorl_sorting_translation',
        'moorl_marker',
        'moorl_client',
        'moorl_image_map',
        'moorl_image_map_translation',
        'moorl_image_map_item',
        'moorl_image_map_item_translation',
        'moorl_media',
        'moorl_media_translation',
        'moorl_media_video',
    ];

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        PluginLifecycleHelper::update(self::class, $this->container);
    }

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        PluginLifecycleHelper::update(self::class, $this->container);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if (!$uninstallContext->keepUserData()) {
            PluginLifecycleHelper::uninstall(self::class, $this->container);
        }

        parent::uninstall($uninstallContext);
    }
}
