export default class RouteHelper {
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
