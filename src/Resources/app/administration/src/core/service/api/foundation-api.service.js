const ApiService = Shopware.Classes.ApiService;

class FoundationApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = '') {
        super(httpClient, loginService, apiEndpoint);
    }

    get(path) {
        const apiRoute = this.getApiBasePath() + path;
        return this.httpClient.get(
            apiRoute,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    post(path, data) {
        const apiRoute = this.getApiBasePath() + path;
        return this.httpClient.post(
            apiRoute,
            data,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}

export default FoundationApiService;