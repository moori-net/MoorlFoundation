<?php declare(strict_types=1);

namespace MoorlFoundation;

use MoorlFoundation\Core\Content\EmbeddedMedia\EmbeddedMediaProductDefinition;
use MoorlFoundation\Core\PluginLifecycleHelper;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MoorlFoundation extends Plugin
{
    final public const NAME = 'MoorlFoundation';
    final public const DATA_CREATED_AT = '2001-11-11 11:11:11.111';
    final public const SHOPWARE_TABLES = [];
    final public const INHERITANCES = [
        ProductDefinition::ENTITY_NAME => [
            EmbeddedMediaProductDefinition::EXTENSION_COLLECTION_NAME
        ]
    ];
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
        'moorl_media_language',
        'moorl_media_product',
    ];

    public function __getMigrationNamespace(): string
    {
        return $this->getNamespace() . '\Migration_6_7';
    }

    public function install(InstallContext $installContext): void
    {
        //$installContext->setAutoMigrate(false); //??

        // Migrations are done before install() is called :(
        //PluginLifecycleHelper::migrationSkipper($this, 1744278006, $this->container);

        parent::install($installContext);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

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
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        PluginLifecycleHelper::uninstall(self::class, $this->container);
    }
}
