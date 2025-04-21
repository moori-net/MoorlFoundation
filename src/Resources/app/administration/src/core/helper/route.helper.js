class RouteHelper {
    constructor({entity, router}) {
        this.entity = entity;
        this.router = router;

        this._init();
    }

    static getRouterLinkByEntity(entity, target = 'detail') {
        const listingRoute = Shopware.Store.get('moorlProxy').getByEntity(entity)?.listingRoute;
        if (listingRoute === undefined) {
            return null;
        }

        let parts = listingRoute.split(".");
        parts.pop();
        parts.push(target);

        return parts.join(".");
    }

    _init() {}
}

export default RouteHelper;
