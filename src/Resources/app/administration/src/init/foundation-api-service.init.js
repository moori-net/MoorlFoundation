import FoundationApiService from '../core/foundation-api.service';

Shopware.Application.addServiceProvider('foundationApiService', (container) => {
    const initContainer = Shopware.Application.getContainer('init');

    return new FoundationApiService(
        initContainer.httpClient,
        container.loginService
    );
});
