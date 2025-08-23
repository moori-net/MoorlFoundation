 
module.exports = function (params) {
    return {
        resolve: {
            modules: [
                `${params.basePath}/Resources/app/storefront/node_modules/`,
                `${params.basePath}/Resources/app/administration/node_modules/`,
            ],
            alias: {
                MoorlFoundation: `${params.basePath}/Resources/app/storefront/src/`,
            },
        },
    };
};
