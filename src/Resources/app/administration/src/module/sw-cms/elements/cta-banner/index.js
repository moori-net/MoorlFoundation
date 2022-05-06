const Application = Shopware.Application;
import './component';
import './config';
import './preview';

const Criteria = Shopware.Data.Criteria;
const criteria = new Criteria();
criteria.addAssociation('media');

const productCriteria = new Criteria();
productCriteria.addAssociation('cover.media');

Application.getContainer('service').cmsService.registerCmsElement({
    name: 'appflix-cta-banner',
    label: 'sw-cms.elements.appflix-cta-banner.title',
    component: 'sw-cms-el-appflix-cta-banner',
    configComponent: 'sw-cms-el-config-appflix-cta-banner',
    previewComponent: 'sw-cms-el-preview-appflix-cta-banner',
    defaultConfig: {
        elementType: {
            source: 'static',
            value: 'custom',
        },
        elementClickable: {
            source: 'static',
            value: false,
        },
        elementUrl: {
            source: 'static',
            value: null,
        },
        elementNewTab: {
            source: 'static',
            value: false,
        },
        animateIn: {
            source: 'static',
            value: 'none',
        },
        animateOut: {
            source: 'static',
            value: 'none',
        },
        animateHover: {
            source: 'static',
            value: 'none',
        },
        category: {
            source: 'static',
            value: null,
            entity: {
                name: 'category',
                criteria: criteria
            }
        },
        product: {
            source: 'static',
            value: null,
            entity: {
                name: 'product',
                criteria: productCriteria
            }
        },
        title: {
            source: 'static',
            value: 'Lorem ipsum dolor'
        },
        content: {
            source: 'static',
            value: `<p>Doe Industries</p>
<h2>New York</h2>
<h3>Address & Contact</h3>
<p>Doe Street 300<br>1337 NY Eastside</p>
<p><a class="btn btn-primary sw-button sw-button--primary" href="/">Contact <i class="fa fa-arrow-right"></i></a></p>
<h3>Opening Hours</h3>
<p>Monday to Friday: 10:00 - 20:00<br>Saturday: 09:30 - 20:00</p>
<p><a class="btn btn-primary sw-button sw-button--primary" href="/">More Information <i class="fa fa-arrow-right"></i></a></p>`.trim()
        },
        enableScss: {
            source: 'static',
            value: false,
        },
        scss: {
            source: 'static',
            value: '',
        },
        contentLength: {
            source: 'static',
            value: 100
        },
        quote: {
            source: 'static',
            value: 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt'
        },
        height: {
            source: 'static',
            value: '300px'
        },
        btnActive: {
            source: 'static',
            value: true,
        },
        btnClass: {
            source: 'static',
            value: 'btn-primary'
        },
        btnText: {
            source: 'static',
            value: 'Shop now'
        },
        btnUrl: {
            source: 'static',
            value: null
        },
        btnNewTab: {
            source: 'static',
            value: null
        },
        iconType: {
            source: 'static',
            value: 'none',
        },
        iconPosition: {
            source: 'static',
            value: 'left',
        },
        iconClass: {
            source: 'static',
            value: 'fas fa-check',
        },
        iconFontSize: {
            source: 'static',
            value: '30px',
        },
        iconSvg: {
            source: 'static',
            value: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z"/></svg>',
        },
        iconMedia: {
            source: 'static',
            value: null,
            entity: {
                name: 'media'
            }
        },
        iconMarginRight: {
            source: 'static',
            value: '15px',
        },
        iconMarginBottom: {
            source: 'static',
            value: '15px',
        },
        elementBackground: {
            source: 'static',
            value: 'none',
        },
        backgroundFixed: {
            source: 'static',
            value: false,
        },
        backgroundVerticalAlign: {
            source: 'static',
            value: 'center'
        },
        backgroundHorizontalAlign: {
            source: 'static',
            value: 'center'
        },
        backgroundDisplayMode: {
            source: 'static',
            value: 'cover'
        },
        backgroundSizeX: {
            source: 'static',
            value: '300px'
        },
        backgroundSizeY: {
            source: 'static',
            value: '300px'
        },
        mediaActive: {
            source: 'static',
            value: true
        },
        mediaHover: {
            source: 'static',
            value: 'zoom'
        },
        media: {
            source: 'static',
            value: null,
            entity: {
                name: 'media'
            }
        },
        videoActive: {
            source: 'static',
            value: false
        },
        videoDisplayMode: {
            source: 'static',
            value: 'cover'
        },
        videoAutoplay: {
            source: 'static',
            value: true
        },
        videoMute: {
            source: 'static',
            value: true
        },
        videoLoop: {
            source: 'static',
            value: true
        },
        boxVerticalAlign: {
            source: 'static',
            value: 'center'
        },
        boxHorizontalAlign: {
            source: 'static',
            value: 'center'
        },
        boxTextAlign: {
            source: 'static',
            value: 'left'
        },
        boxWidth: {
            source: 'static',
            value: 'auto'
        },
        boxHeight: {
            source: 'static',
            value: 'auto'
        },
        boxMargin: {
            source: 'static',
            value: '20px'
        },
        boxPadding: {
            source: 'static',
            value: '15px'
        },
        boxColor: {
            source: 'static',
            value: '#000000'
        },
        boxBackground: {
            source: 'static',
            value: 'rgba(255,255,255,0.7)'
        },
        boxMaxWidth: {
            source: 'static',
            value: false
        },
        boxBorderRadius: {
            source: 'static',
            value: '0px'
        },
    },
    defaultData: {
        category: {
            name: 'Lorem Ipsum dolor',
            description: `Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
                          sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
                          sed diam voluptua.`.trim(),
            media: {
                url: '/administration/static/img/cms/preview_glasses_large.jpg',
                alt: 'Lorem Ipsum dolor'
            }
        },
        product: {
            name: 'Demo Product',
            description: `Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
                          sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
                          sed diam voluptua.`.trim(),
            cover: {
                media: {
                    url: '/administration/static/img/cms/preview_glasses_large.jpg',
                    alt: 'Add to cart'
                }
            }
        }
    }
});
