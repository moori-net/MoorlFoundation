import Plugin from 'src/plugin-system/plugin.class';
import Dropzone from 'dropzone';

export default class MoorlCustomerUploadPlugin extends Plugin {
    static options = {};

    init() {
        const dropzone = new Dropzone(this.el.querySelector('.dropzone'), {
            url: this.options.url,
            params: (files, xhr, chunk) => {
                return Object.fromEntries(
                    Object.entries(this.options.params).filter(
                        ([_, v]) => v != null
                    )
                );
            },
            disablePreviews: true,
            dictDefaultMessage: this.options.dictDefaultMessage,
        });

        const imageContainer = this.el.querySelector(
            '.moorl-customer-upload-image'
        );
        const filesContainer = this.el.querySelector(
            '.moorl-customer-upload-files'
        );

        dropzone.on('success', (file, response, xhr) => {
            if (imageContainer) {
                imageContainer.innerHTML = response;
            } else if (filesContainer) {
                const responseEl = Dropzone.createElement(response.trim());
                const duplicateEl = document.getElementById(responseEl.id);

                if (duplicateEl) {
                    //filesContainer.replaceChild(responseEl, duplicateEl);
                } else {
                    filesContainer.append(responseEl);
                }
            } else {
                window.location.reload();
            }
        });
    }
}
