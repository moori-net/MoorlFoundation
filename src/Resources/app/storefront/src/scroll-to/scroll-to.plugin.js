const Plugin = window.PluginBaseClass;

export default class MoorlScrollTo extends Plugin {
    static options = {
        offsetTop: window.moorlOffsetTop ?? 30
    };

    init() {
        const params = new URLSearchParams(window.location.search);
        const targetId = params.get('scrollTo');

        if (!targetId) {
            return;
        }

        const scrollToTarget = () => {
            const targetElement = document.getElementById(targetId);

            if (!targetElement) {
                return;
            }

            const elementPosition = targetElement.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.scrollY - this.options.offsetTop;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        };

        scrollToTarget();
        window.setTimeout(scrollToTarget, 300);
    }
}
