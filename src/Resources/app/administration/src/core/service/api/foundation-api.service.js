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

    download(path, data) {
        const apiRoute = this.getApiBasePath() + path;

        if (typeof data !== 'undefined') {
            return this.httpClient.post(
                apiRoute,
                data,
                {
                    headers: this.getBasicHeaders({responseType: 'blob'}),
                    responseType: 'blob'
                }
            ).then((response) => {
                const filename = response.headers['content-disposition'].split('filename=')[1];
                const link = document.createElement('a');

                link.href = URL.createObjectURL(response.data);
                link.download = filename;
                link.dispatchEvent(new MouseEvent('click'));
                link.remove();
            }).catch((error) => {

            });
        } else {
            this.httpClient({
                method: 'get',
                url: apiRoute,
                headers: this.getBasicHeaders({responseType: 'blob'}),
                responseType: 'blob'
            }).then((response) => {
                if (response.data) {
                    const filename = response.headers['content-disposition'].split('filename=')[1];
                    const link = document.createElement('a');

                    link.href = URL.createObjectURL(response.data);
                    link.download = filename;
                    link.dispatchEvent(new MouseEvent('click'));
                    link.remove();
                }
            }).catch((error) => {

            });
        }
    }
}

export default FoundationApiService;