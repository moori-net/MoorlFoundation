import './sw-cms-section.scss';

Shopware.Component.override('sw-cms-section', {
    computed: {
        isSideBarType() {
            console.log(this.section.type.includes('sidebar'));

            return this.section.type.includes('sidebar');
        }
    }
});
