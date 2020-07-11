const { Application, Component, Mixin } = Shopware;

const initContainer = Application.getContainer('init');
const httpClient = initContainer.httpClient;

Component.override('sw-admin-menu', {
    mixins: [
        Mixin.getByName('notification')
    ],

    created() {
        httpClient.get("/moorl-foundation/feed").then((response) => {
            console.log(response);

            if (response.data.articles) {
                const that = this;

                response.data.articles.forEach(function (article) {
                    that.createNotificationInfo({
                        title: article.title,
                        message: article.teaser
                    });
                });
            }
        });
    }
});