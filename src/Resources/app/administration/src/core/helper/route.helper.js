export default class RouteHelper {
    constructor({entity, router}) {
        this.entity = entity;
        this.router = router;

        this._init();
    }

    static getRouterLinkByEntity(entity, target = 'detail') {
        const listPath = MoorlFoundation.ModuleHelper.getByEntity(entity)?.listPath;
        if (listPath === undefined) {
            return null;
        }

        let parts = listPath.split(".");
        parts.pop();
        parts.push(target);

        return parts.join(".");
    }
}
