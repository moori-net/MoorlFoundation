import './component';
import './config';
import './preview';

const Criteria = Shopware.Data.Criteria;
const criteria = new Criteria();
criteria.addAssociation('options.group');
criteria.addAssociation('cover');

Shopware.Service('cmsService').registerCmsElement({
    hidden: true,
    plugin: 'MoorlFoundation',
    icon: 'default-shopping-cart',
    name: 'moorl-product-buy-list',
    label: 'sw-cms.elements.moorl-product-buy-list.name',
    component: 'sw-cms-el-moorl-product-buy-list',
    previewComponent: 'sw-cms-el-preview-moorl-product-buy-list',
    configComponent: 'sw-cms-el-config-moorl-product-buy-list',
    defaultConfig: {
        layout: {
            source: 'static',
            value: 'default'
        },
        products: {
            source: 'static',
            value: [],
            required: true,
            entity: {
                name: 'product',
                criteria: criteria
            }
        },
        enablePrices: {
            source: 'static',
            value: true
        },
        enableVariantSwitch: {
            source: 'static',
            value: true
        },
        enableAddToCartAll: {
            source: 'static',
            value: true
        },
        enableAddToCartSingle: {
            source: 'static',
            value: true
        },
        enableDirectUrl: {
            source: 'static',
            value: false
        }
    },
    collect: function collect(elem) {
        const context = Object.assign(
            {},
            Shopware.Context.api,
            { inheritance: true },
        );

        const criteriaList = {};

        Object.keys(elem.config).forEach((configKey) => {
            if (elem.config[configKey].source === 'mapped') {
                return;
            }

            if (elem.config[configKey].source === 'product_stream') {
                return;
            }

            const entity = elem.config[configKey].entity;

            if (entity && elem.config[configKey].value) {
                const entityKey = entity.name;
                const entityData = {
                    value: [...elem.config[configKey].value],
                    key: configKey,
                    searchCriteria: entity.criteria ? entity.criteria : new Criteria(),
                    ...entity,
                };

                entityData.searchCriteria.setIds(entityData.value);
                entityData.context = context;

                criteriaList[`entity-${entityKey}`] = entityData;
            }
        });

        return criteriaList;
    },
});
