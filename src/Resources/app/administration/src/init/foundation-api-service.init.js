const { Application } = Shopware;

import FoundationApiService from '../../src/core/service/api/foundation-api.service';

Application.addServiceProvider('foundationApiService', (container) => {
    const initContainer = Application.getContainer('init');

    return new FoundationApiService(initContainer.httpClient, container.loginService);
});
