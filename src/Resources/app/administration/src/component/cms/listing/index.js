import './component';
import './config';
import './preview';

const MoorlFoundationCmsListing = {
    options: {
        listingType: [
            'grid',
            'list',
            'slider'
        ],
        itemType: [
            'overlay',
            'imageFirst',
            'contentFirst'
        ],
        mediaType: [
            'standard',
            'cover',
            'contain'
        ],
    },
    defaultConfig: {
        listingType: {
            source: 'static',
            value: 'grid'
        },
        itemType: {
            source: 'static',
            value: 'overlay'
        },
        mediaType: {
            source: 'static',
            value: 'cover'
        },
        limit: {
            source: 'static',
            value: 12
        },
        gapSize: {
            source: 'static',
            value: '10px'
        },
        itemWidth: {
            source: 'static',
            value: '10px'
        },
        itemHeight: {
            source: 'static',
            value: '340px'
        }
    }
};
