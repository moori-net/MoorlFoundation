const MoorlFoundation = function MoorlFoundation() {

    this.colorScheme = [
        {
            value: 'primary',
            label: 'moorl-cms.elements.general.config.label.btnPrimary'
        },
        {
            value: 'danger',
            label: 'moorl-cms.elements.general.config.label.btnDanger'
        },
        {
            value: 'success',
            label: 'moorl-cms.elements.general.config.label.btnSuccess'
        },
        {
            value: 'warning',
            label: 'moorl-cms.elements.general.config.label.btnWarning'
        },
        {
            value: 'info',
            label: 'moorl-cms.elements.general.config.label.btnInfo'
        },
        {
            value: 'dark',
            label: 'moorl-cms.elements.general.config.label.btnDark'
        },
        {
            value: 'light',
            label: 'moorl-cms.elements.general.config.label.btnLight'
        },
        {
            value: 'facebook',
            label: 'moorl-cms.elements.general.config.label.btnFacebook'
        },
        {
            value: 'twitter',
            label: 'moorl-cms.elements.general.config.label.btnTwitter'
        },
        {
            value: 'google',
            label: 'moorl-cms.elements.general.config.label.btnGoogle'
        },
        {
            value: 'shopware',
            label: 'moorl-cms.elements.general.config.label.btnShopware'
        }
    ];

    this.btnClass = [
        {
            value: 'btn-primary',
            label: 'moorl-cms.elements.general.config.label.btnPrimary'
        },
        {
            value: 'btn-outline-primary',
            label: 'moorl-cms.elements.general.config.label.btnPrimaryOutline'
        },
        {
            value: 'btn-danger',
            label: 'moorl-cms.elements.general.config.label.btnDanger'
        },
        {
            value: 'btn-outline-danger',
            label: 'moorl-cms.elements.general.config.label.btnDangerOutline'
        },
        {
            value: 'btn-success',
            label: 'moorl-cms.elements.general.config.label.btnSuccess'
        },
        {
            value: 'btn-outline-success',
            label: 'moorl-cms.elements.general.config.label.btnSuccessOutline'
        },
        {
            value: 'btn-warning',
            label: 'moorl-cms.elements.general.config.label.btnWarning'
        },
        {
            value: 'btn-outline-warning',
            label: 'moorl-cms.elements.general.config.label.btnWarningOutline'
        },
        {
            value: 'btn-info',
            label: 'moorl-cms.elements.general.config.label.btnInfo'
        },
        {
            value: 'btn-outline-info',
            label: 'moorl-cms.elements.general.config.label.btnInfoOutline'
        },
        {
            value: 'btn-dark',
            label: 'moorl-cms.elements.general.config.label.btnDark'
        },
        {
            value: 'btn-outline-dark',
            label: 'moorl-cms.elements.general.config.label.btnDarkOutline'
        },
        {
            value: 'btn-link',
            label: 'moorl-cms.elements.general.config.label.btnLink'
        }
    ];
    this.verticalAlign = [
        {
            value: 'flex-start',
            label: 'moorl-cms.elements.general.config.label.top'
        },
        {
            value: 'center',
            label: 'moorl-cms.elements.general.config.label.center'
        },
        {
            value: 'flex-end',
            label: 'moorl-cms.elements.general.config.label.bottom'
        }
    ];
    this.horizontalAlign = [
        {
            value: 'flex-start',
            label: 'moorl-cms.elements.general.config.label.left'
        },
        {
            value: 'center',
            label: 'moorl-cms.elements.general.config.label.center'
        },
        {
            value: 'flex-end',
            label: 'moorl-cms.elements.general.config.label.right'
        }
    ];
    this.textAlign = [
        {
            value: 'left',
            label: 'moorl-cms.elements.general.config.label.left'
        },
        {
            value: 'center',
            label: 'moorl-cms.elements.general.config.label.center'
        },
        {
            value: 'right',
            label: 'moorl-cms.elements.general.config.label.right'
        }
    ];
    this.verticalTextAlign = [
        {
            value: 'top',
            label: 'moorl-cms.elements.general.config.label.top'
        },
        {
            value: 'center',
            label: 'moorl-cms.elements.general.config.label.center'
        },
        {
            value: 'bottom',
            label: 'moorl-cms.elements.general.config.label.bottom'
        }
    ];
    this.animateCss = [
        {
            optgroup: '-',
            options: [
                'none'
            ]
        },
        {
            optgroup: 'Attention Seekers',
            options: [
                'bounce',
                'flash',
                'pulse',
                'rubberBand',
                'shake',
                'swing',
                'tada',
                'wobble',
                'jello',
                'heartBeat',
            ]
        },
        {
            optgroup: 'Bouncing Entrances',
            options: [
                'bounceIn',
                'bounceInDown',
                'bounceInLeft',
                'bounceInRight',
                'bounceInUp',
            ]
        },
        {
            optgroup: 'Bouncing Exits',
            options: [
                'bounceOut',
                'bounceOutDown',
                'bounceOutLeft',
                'bounceOutRight',
                'bounceOutUp',
            ]
        },
        {
            optgroup: 'Fading Entrances',
            options: [
                'fadeIn',
                'fadeInDown',
                'fadeInDownBig',
                'fadeInLeft',
                'fadeInLeftBig',
                'fadeInRight',
                'fadeInRightBig',
                'fadeInUp',
                'fadeInUpBig',
            ]
        },
        {
            optgroup: 'Fading Exits',
            options: [
                'fadeOut',
                'fadeOutDown',
                'fadeOutDownBig',
                'fadeOutLeft',
                'fadeOutLeftBig',
                'fadeOutRight',
                'fadeOutRightBig',
                'fadeOutUp',
                'fadeOutUpBig',
            ]
        },
        {
            optgroup: 'Flippers',
            options: [
                'flip',
                'flipInX',
                'flipInY',
                'flipOutX',
                'flipOutY',
            ]
        },
        {
            optgroup: 'Lightspeed',
            options: [
                'lightSpeedIn',
                'lightSpeedOut',
            ]
        },
        {
            optgroup: 'Rotating Entrances',
            options: [
                'rotateIn',
                'rotateInDownLeft',
                'rotateInDownRight',
                'rotateInUpLeft',
                'rotateInUpRight',
            ]
        },
        {
            optgroup: 'Rotating Exits',
            options: [
                'rotateOut',
                'rotateOutDownLeft',
                'rotateOutDownRight',
                'rotateOutUpLeft',
                'rotateOutUpRight',
            ]
        },
        {
            optgroup: 'Sliding Entrances',
            options: [
                'slideInUp',
                'slideInDown',
                'slideInLeft',
                'slideInRight',
            ]
        },
        {
            optgroup: 'Sliding Exits',
            options: [
                'slideOutUp',
                'slideOutDown',
                'slideOutLeft',
                'slideOutRight',
            ]
        },
        {
            optgroup: 'Zoom Entrances',
            options: [
                'zoomIn',
                'zoomInDown',
                'zoomInLeft',
                'zoomInRight',
                'zoomInUp',
            ]
        },
        {
            optgroup: 'Zoom Exits',
            options: [
                'zoomOut',
                'zoomOutDown',
                'zoomOutLeft',
                'zoomOutRight',
                'zoomOutUp',
            ]
        },
        {
            optgroup: 'Specials',
            options: [
                'hinge',
                'jackInTheBox',
                'rollIn',
                'rollOut',
            ]
        }
    ];
    this.iconClass = [
        {
            "value": "fab fa-elementor",
            "label": "Elementor"
        },
        {
            "value": "fas fa-eject",
            "label": "eject"
        },
        {
            "value": "fas fa-egg",
            "label": "Egg"
        },
        {
            "value": "fas fa-edit",
            "label": "Edit"
        },
        {
            "value": "far fa-edit",
            "label": "Edit"
        },
        {
            "value": "fab fa-edge",
            "label": "Edge Browser"
        },
        {
            "value": "fab fa-ebay",
            "label": "eBay"
        },
        {
            "value": "fab fa-earlybirds",
            "label": "Earlybirds"
        },
        {
            "value": "fab fa-dyalog",
            "label": "Dyalog"
        },
        {
            "value": "fas fa-dungeon",
            "label": "Dungeon"
        },
        {
            "value": "fas fa-dumpster-fire",
            "label": "Dumpster Fire"
        },
        {
            "value": "fas fa-dumpster",
            "label": "Dumpster"
        },
        {
            "value": "fas fa-dumbbell",
            "label": "Dumbbell"
        },
        {
            "value": "fab fa-drupal",
            "label": "Drupal Logo"
        },
        {
            "value": "fas fa-drumstick-bite",
            "label": "Drumstick with Bite Taken Out"
        },
        {
            "value": "fas fa-drum-steelpan",
            "label": "Drum Steelpan"
        },
        {
            "value": "fas fa-drum",
            "label": "Drum"
        },
        {
            "value": "fab fa-dropbox",
            "label": "Dropbox"
        },
        {
            "value": "fab fa-dribbble-square",
            "label": "Dribbble Square"
        },
        {
            "value": "fab fa-dribbble",
            "label": "Dribbble"
        },
        {
            "value": "fas fa-draw-polygon",
            "label": "Draw Polygon"
        },
        {
            "value": "fas fa-dragon",
            "label": "Dragon"
        },
        {
            "value": "fas fa-drafting-compass",
            "label": "Drafting Compass"
        },
        {
            "value": "fab fa-draft2digital",
            "label": "Draft2digital"
        },
        {
            "value": "fas fa-download",
            "label": "Download"
        },
        {
            "value": "fas fa-dove",
            "label": "Dove"
        },
        {
            "value": "fas fa-dot-circle",
            "label": "Dot Circle"
        },
        {
            "value": "far fa-dot-circle",
            "label": "Dot Circle"
        },
        {
            "value": "fas fa-door-open",
            "label": "Door Open"
        },
        {
            "value": "fas fa-door-closed",
            "label": "Door Closed"
        },
        {
            "value": "fas fa-donate",
            "label": "Donate"
        },
        {
            "value": "fas fa-dolly-flatbed",
            "label": "Dolly Flatbed"
        },
        {
            "value": "fas fa-dolly",
            "label": "Dolly"
        },
        {
            "value": "fas fa-dollar-sign",
            "label": "Dollar Sign"
        },
        {
            "value": "fas fa-dog",
            "label": "Dog"
        },
        {
            "value": "fab fa-docker",
            "label": "Docker"
        },
        {
            "value": "fab fa-dochub",
            "label": "DocHub"
        },
        {
            "value": "fas fa-dna",
            "label": "DNA"
        },
        {
            "value": "fas fa-dizzy",
            "label": "Dizzy Face"
        },
        {
            "value": "far fa-dizzy",
            "label": "Dizzy Face"
        },
        {
            "value": "fas fa-divide",
            "label": "Divide"
        },
        {
            "value": "fab fa-discourse",
            "label": "Discourse"
        },
        {
            "value": "fab fa-discord",
            "label": "Discord"
        },
        {
            "value": "fas fa-directions",
            "label": "Directions"
        },
        {
            "value": "fas fa-digital-tachograph",
            "label": "Digital Tachograph"
        },
        {
            "value": "fab fa-digital-ocean",
            "label": "Digital Ocean"
        },
        {
            "value": "fab fa-digg",
            "label": "Digg Logo"
        },
        {
            "value": "fas fa-dice-two",
            "label": "Dice Two"
        },
        {
            "value": "fas fa-dice-three",
            "label": "Dice Three"
        },
        {
            "value": "fas fa-dice-six",
            "label": "Dice Six"
        },
        {
            "value": "fas fa-dice-one",
            "label": "Dice One"
        },
        {
            "value": "fas fa-dice-four",
            "label": "Dice Four"
        },
        {
            "value": "fas fa-dice-five",
            "label": "Dice Five"
        },
        {
            "value": "fas fa-dice-d6",
            "label": "Dice D6"
        },
        {
            "value": "fas fa-dice-d20",
            "label": "Dice D20"
        },
        {
            "value": "fas fa-dice",
            "label": "Dice"
        },
        {
            "value": "fab fa-diaspora",
            "label": "Diaspora"
        },
        {
            "value": "fas fa-diagnoses",
            "label": "Diagnoses"
        },
        {
            "value": "fab fa-dhl",
            "label": "DHL"
        },
        {
            "value": "fas fa-dharmachakra",
            "label": "Dharmachakra"
        },
        {
            "value": "fab fa-deviantart",
            "label": "deviantART"
        },
        {
            "value": "fab fa-dev",
            "label": "DEV"
        },
        {
            "value": "fas fa-desktop",
            "label": "Desktop"
        },
        {
            "value": "fab fa-deskpro",
            "label": "Deskpro"
        },
        {
            "value": "fab fa-deploydog",
            "label": "deploy.dog"
        },
        {
            "value": "fas fa-democrat",
            "label": "Democrat"
        },
        {
            "value": "fab fa-delicious",
            "label": "Delicious"
        },
        {
            "value": "fas fa-deaf",
            "label": "Deaf"
        },
        {
            "value": "fas fa-database",
            "label": "Database"
        },
        {
            "value": "fab fa-dashcube",
            "label": "DashCube"
        },
        {
            "value": "fab fa-dailymotion",
            "label": "dailymotion"
        },
        {
            "value": "fab fa-d-and-d-beyond",
            "label": "D&D Beyond"
        },
        {
            "value": "fab fa-d-and-d",
            "label": "Dungeons & Dragons"
        },
        {
            "value": "fab fa-cuttlefish",
            "label": "Cuttlefish"
        },
        {
            "value": "fas fa-cut",
            "label": "Cut"
        },
        {
            "value": "fas fa-cubes",
            "label": "Cubes"
        },
        {
            "value": "fas fa-cube",
            "label": "Cube"
        },
        {
            "value": "fab fa-css3-alt",
            "label": "Alternate CSS3 Logo"
        },
        {
            "value": "fab fa-css3",
            "label": "CSS 3 Logo"
        },
        {
            "value": "fas fa-crutch",
            "label": "Crutch"
        },
        {
            "value": "fas fa-crown",
            "label": "Crown"
        },
        {
            "value": "fas fa-crow",
            "label": "Crow"
        },
        {
            "value": "fas fa-crosshairs",
            "label": "Crosshairs"
        },
        {
            "value": "fas fa-cross",
            "label": "Cross"
        },
        {
            "value": "fas fa-crop-alt",
            "label": "Alternate Crop"
        },
        {
            "value": "fas fa-crop",
            "label": "crop"
        },
        {
            "value": "fab fa-critical-role",
            "label": "Critical Role"
        },
        {
            "value": "fas fa-credit-card",
            "label": "Credit Card"
        },
        {
            "value": "far fa-credit-card",
            "label": "Credit Card"
        },
        {
            "value": "fab fa-creative-commons-zero",
            "label": "Creative Commons CC0"
        },
        {
            "value": "fab fa-creative-commons-share",
            "label": "Creative Commons Share"
        },
        {
            "value": "fab fa-creative-commons-sampling-plus",
            "label": "Creative Commons Sampling +"
        },
        {
            "value": "fab fa-creative-commons-sampling",
            "label": "Creative Commons Sampling"
        },
        {
            "value": "fab fa-creative-commons-sa",
            "label": "Creative Commons Share Alike"
        },
        {
            "value": "fab fa-creative-commons-remix",
            "label": "Creative Commons Remix"
        },
        {
            "value": "fab fa-creative-commons-pd-alt",
            "label": "Alternate Creative Commons Public Domain"
        },
        {
            "value": "fab fa-creative-commons-pd",
            "label": "Creative Commons Public Domain"
        },
        {
            "value": "fab fa-creative-commons-nd",
            "label": "Creative Commons No Derivative Works"
        },
        {
            "value": "fab fa-creative-commons-nc-jp",
            "label": "Creative Commons Noncommercial (Yen Sign)"
        },
        {
            "value": "fab fa-creative-commons-nc-eu",
            "label": "Creative Commons Noncommercial (Euro Sign)"
        },
        {
            "value": "fab fa-creative-commons-nc",
            "label": "Creative Commons Noncommercial"
        },
        {
            "value": "fab fa-creative-commons-by",
            "label": "Creative Commons Attribution"
        },
        {
            "value": "fab fa-creative-commons",
            "label": "Creative Commons"
        },
        {
            "value": "fab fa-cpanel",
            "label": "cPanel"
        },
        {
            "value": "fas fa-couch",
            "label": "Couch"
        },
        {
            "value": "fab fa-cotton-bureau",
            "label": "Cotton Bureau"
        },
        {
            "value": "fas fa-copyright",
            "label": "Copyright"
        },
        {
            "value": "far fa-copyright",
            "label": "Copyright"
        },
        {
            "value": "fas fa-copy",
            "label": "Copy"
        },
        {
            "value": "far fa-copy",
            "label": "Copy"
        },
        {
            "value": "fas fa-cookie-bite",
            "label": "Cookie Bite"
        },
        {
            "value": "fas fa-cookie",
            "label": "Cookie"
        },
        {
            "value": "fab fa-contao",
            "label": "Contao"
        },
        {
            "value": "fab fa-connectdevelop",
            "label": "Connect Develop"
        },
        {
            "value": "fab fa-confluence",
            "label": "Confluence"
        },
        {
            "value": "fas fa-concierge-bell",
            "label": "Concierge Bell"
        },
        {
            "value": "fas fa-compress-arrows-alt",
            "label": "Alternate Compress Arrows"
        },
        {
            "value": "fas fa-compress-alt",
            "label": "Alternate Compress"
        },
        {
            "value": "fas fa-compress",
            "label": "Compress"
        },
        {
            "value": "fas fa-compass",
            "label": "Compass"
        },
        {
            "value": "far fa-compass",
            "label": "Compass"
        },
        {
            "value": "fas fa-compact-disc",
            "label": "Compact Disc"
        },
        {
            "value": "fas fa-comments-dollar",
            "label": "Comments Dollar"
        },
        {
            "value": "fas fa-comments",
            "label": "comments"
        },
        {
            "value": "far fa-comments",
            "label": "comments"
        },
        {
            "value": "fas fa-comment-slash",
            "label": "Comment Slash"
        },
        {
            "value": "fas fa-comment-medical",
            "label": "Alternate Medical Chat"
        },
        {
            "value": "fas fa-comment-dots",
            "label": "Comment Dots"
        },
        {
            "value": "far fa-comment-dots",
            "label": "Comment Dots"
        },
        {
            "value": "fas fa-comment-dollar",
            "label": "Comment Dollar"
        },
        {
            "value": "fas fa-comment-alt",
            "label": "Alternate Comment"
        },
        {
            "value": "far fa-comment-alt",
            "label": "Alternate Comment"
        },
        {
            "value": "fas fa-comment",
            "label": "comment"
        },
        {
            "value": "far fa-comment",
            "label": "comment"
        },
        {
            "value": "fas fa-columns",
            "label": "Columns"
        },
        {
            "value": "fas fa-coins",
            "label": "Coins"
        },
        {
            "value": "fas fa-cogs",
            "label": "cogs"
        },
        {
            "value": "fas fa-cog",
            "label": "cog"
        },
        {
            "value": "fas fa-coffee",
            "label": "Coffee"
        },
        {
            "value": "fab fa-codiepie",
            "label": "Codie Pie"
        },
        {
            "value": "fab fa-codepen",
            "label": "Codepen"
        },
        {
            "value": "fas fa-code-branch",
            "label": "Code Branch"
        },
        {
            "value": "fas fa-code",
            "label": "Code"
        },
        {
            "value": "fas fa-cocktail",
            "label": "Cocktail"
        },
        {
            "value": "fab fa-cloudversify",
            "label": "cloudversify"
        },
        {
            "value": "fab fa-cloudsmith",
            "label": "Cloudsmith"
        },
        {
            "value": "fab fa-cloudscale",
            "label": "cloudscale.ch"
        },
        {
            "value": "fas fa-cloud-upload-alt",
            "label": "Alternate Cloud Upload"
        },
        {
            "value": "fas fa-cloud-sun-rain",
            "label": "Cloud with Sun and Rain"
        },
        {
            "value": "fas fa-cloud-sun",
            "label": "Cloud with Sun"
        },
        {
            "value": "fas fa-cloud-showers-heavy",
            "label": "Cloud with Heavy Showers"
        },
        {
            "value": "fas fa-cloud-rain",
            "label": "Cloud with Rain"
        },
        {
            "value": "fas fa-cloud-moon-rain",
            "label": "Cloud with Moon and Rain"
        },
        {
            "value": "fas fa-cloud-moon",
            "label": "Cloud with Moon"
        },
        {
            "value": "fas fa-cloud-meatball",
            "label": "Cloud with (a chance of) Meatball"
        },
        {
            "value": "fas fa-cloud-download-alt",
            "label": "Alternate Cloud Download"
        },
        {
            "value": "fas fa-cloud",
            "label": "Cloud"
        },
        {
            "value": "fas fa-closed-captioning",
            "label": "Closed Captioning"
        },
        {
            "value": "far fa-closed-captioning",
            "label": "Closed Captioning"
        },
        {
            "value": "fas fa-clone",
            "label": "Clone"
        },
        {
            "value": "far fa-clone",
            "label": "Clone"
        },
        {
            "value": "fas fa-clock",
            "label": "Clock"
        },
        {
            "value": "far fa-clock",
            "label": "Clock"
        },
        {
            "value": "fas fa-clipboard-list",
            "label": "Clipboard List"
        },
        {
            "value": "fas fa-clipboard-check",
            "label": "Clipboard with Check"
        },
        {
            "value": "fas fa-clipboard",
            "label": "Clipboard"
        },
        {
            "value": "far fa-clipboard",
            "label": "Clipboard"
        },
        {
            "value": "fas fa-clinic-medical",
            "label": "Medical Clinic"
        },
        {
            "value": "fas fa-city",
            "label": "City"
        },
        {
            "value": "fas fa-circle-notch",
            "label": "Circle Notched"
        },
        {
            "value": "fas fa-circle",
            "label": "Circle"
        },
        {
            "value": "far fa-circle",
            "label": "Circle"
        },
        {
            "value": "fas fa-church",
            "label": "Church"
        },
        {
            "value": "fab fa-chromecast",
            "label": "Chromecast"
        },
        {
            "value": "fab fa-chrome",
            "label": "Chrome"
        },
        {
            "value": "fas fa-child",
            "label": "Child"
        },
        {
            "value": "fas fa-chevron-up",
            "label": "chevron-up"
        },
        {
            "value": "fas fa-chevron-right",
            "label": "chevron-right"
        },
        {
            "value": "fas fa-chevron-left",
            "label": "chevron-left"
        },
        {
            "value": "fas fa-chevron-down",
            "label": "chevron-down"
        },
        {
            "value": "fas fa-chevron-circle-up",
            "label": "Chevron Circle Up"
        },
        {
            "value": "fas fa-chevron-circle-right",
            "label": "Chevron Circle Right"
        },
        {
            "value": "fas fa-chevron-circle-left",
            "label": "Chevron Circle Left"
        },
        {
            "value": "fas fa-chevron-circle-down",
            "label": "Chevron Circle Down"
        },
        {
            "value": "fas fa-chess-rook",
            "label": "Chess Rook"
        },
        {
            "value": "fas fa-chess-queen",
            "label": "Chess Queen"
        },
        {
            "value": "fas fa-chess-pawn",
            "label": "Chess Pawn"
        },
        {
            "value": "fas fa-chess-knight",
            "label": "Chess Knight"
        },
        {
            "value": "fas fa-chess-king",
            "label": "Chess King"
        },
        {
            "value": "fas fa-chess-board",
            "label": "Chess Board"
        },
        {
            "value": "fas fa-chess-bishop",
            "label": "Chess Bishop"
        },
        {
            "value": "fas fa-chess",
            "label": "Chess"
        },
        {
            "value": "fas fa-cheese",
            "label": "Cheese"
        },
        {
            "value": "fas fa-check-square",
            "label": "Check Square"
        },
        {
            "value": "far fa-check-square",
            "label": "Check Square"
        },
        {
            "value": "fas fa-check-double",
            "label": "Double Check"
        },
        {
            "value": "fas fa-check-circle",
            "label": "Check Circle"
        },
        {
            "value": "far fa-check-circle",
            "label": "Check Circle"
        },
        {
            "value": "fas fa-check",
            "label": "Check"
        },
        {
            "value": "fas fa-chart-pie",
            "label": "Pie Chart"
        },
        {
            "value": "fas fa-chart-line",
            "label": "Line Chart"
        },
        {
            "value": "fas fa-chart-bar",
            "label": "Bar Chart"
        },
        {
            "value": "far fa-chart-bar",
            "label": "Bar Chart"
        },
        {
            "value": "fas fa-chart-area",
            "label": "Area Chart"
        },
        {
            "value": "fas fa-charging-station",
            "label": "Charging Station"
        },
        {
            "value": "fas fa-chalkboard-teacher",
            "label": "Chalkboard Teacher"
        },
        {
            "value": "fas fa-chalkboard",
            "label": "Chalkboard"
        },
        {
            "value": "fas fa-chair",
            "label": "Chair"
        },
        {
            "value": "fas fa-certificate",
            "label": "certificate"
        },
        {
            "value": "fab fa-centos",
            "label": "Centos"
        },
        {
            "value": "fab fa-centercode",
            "label": "Centercode"
        },
        {
            "value": "fab fa-cc-visa",
            "label": "Visa Credit Card"
        },
        {
            "value": "fab fa-cc-stripe",
            "label": "Stripe Credit Card"
        },
        {
            "value": "fab fa-cc-paypal",
            "label": "Paypal Credit Card"
        },
        {
            "value": "fab fa-cc-mastercard",
            "label": "MasterCard Credit Card"
        },
        {
            "value": "fab fa-cc-jcb",
            "label": "JCB Credit Card"
        },
        {
            "value": "fab fa-cc-discover",
            "label": "Discover Credit Card"
        },
        {
            "value": "fab fa-cc-diners-club",
            "label": "Diner's Club Credit Card"
        },
        {
            "value": "fab fa-cc-apple-pay",
            "label": "Apple Pay Credit Card"
        },
        {
            "value": "fab fa-cc-amex",
            "label": "American Express Credit Card"
        },
        {
            "value": "fab fa-cc-amazon-pay",
            "label": "Amazon Pay Credit Card"
        },
        {
            "value": "fas fa-cat",
            "label": "Cat"
        },
        {
            "value": "fas fa-cash-register",
            "label": "Cash Register"
        },
        {
            "value": "fas fa-cart-plus",
            "label": "Add to Shopping Cart"
        },
        {
            "value": "fas fa-cart-arrow-down",
            "label": "Shopping Cart Arrow Down"
        },
        {
            "value": "fas fa-carrot",
            "label": "Carrot"
        },
        {
            "value": "fas fa-caret-up",
            "label": "Caret Up"
        },
        {
            "value": "fas fa-caret-square-up",
            "label": "Caret Square Up"
        },
        {
            "value": "far fa-caret-square-up",
            "label": "Caret Square Up"
        },
        {
            "value": "fas fa-caret-square-right",
            "label": "Caret Square Right"
        },
        {
            "value": "far fa-caret-square-right",
            "label": "Caret Square Right"
        },
        {
            "value": "fas fa-caret-square-left",
            "label": "Caret Square Left"
        },
        {
            "value": "far fa-caret-square-left",
            "label": "Caret Square Left"
        },
        {
            "value": "fas fa-caret-square-down",
            "label": "Caret Square Down"
        },
        {
            "value": "far fa-caret-square-down",
            "label": "Caret Square Down"
        },
        {
            "value": "fas fa-caret-right",
            "label": "Caret Right"
        },
        {
            "value": "fas fa-caret-left",
            "label": "Caret Left"
        },
        {
            "value": "fas fa-caret-down",
            "label": "Caret Down"
        },
        {
            "value": "fas fa-caravan",
            "label": "Caravan"
        },
        {
            "value": "fas fa-car-side",
            "label": "Car Side"
        },
        {
            "value": "fas fa-car-crash",
            "label": "Car Crash"
        },
        {
            "value": "fas fa-car-battery",
            "label": "Car Battery"
        },
        {
            "value": "fas fa-car-alt",
            "label": "Alternate Car"
        },
        {
            "value": "fas fa-car",
            "label": "Car"
        },
        {
            "value": "fas fa-capsules",
            "label": "Capsules"
        },
        {
            "value": "fas fa-cannabis",
            "label": "Cannabis"
        },
        {
            "value": "fas fa-candy-cane",
            "label": "Candy Cane"
        },
        {
            "value": "fab fa-canadian-maple-leaf",
            "label": "Canadian Maple Leaf"
        },
        {
            "value": "fas fa-campground",
            "label": "Campground"
        },
        {
            "value": "fas fa-camera-retro",
            "label": "Retro Camera"
        },
        {
            "value": "fas fa-camera",
            "label": "camera"
        },
        {
            "value": "fas fa-calendar-week",
            "label": "Calendar with Week Focus"
        },
        {
            "value": "fas fa-calendar-times",
            "label": "Calendar Times"
        },
        {
            "value": "far fa-calendar-times",
            "label": "Calendar Times"
        },
        {
            "value": "fas fa-calendar-plus",
            "label": "Calendar Plus"
        },
        {
            "value": "far fa-calendar-plus",
            "label": "Calendar Plus"
        },
        {
            "value": "fas fa-calendar-minus",
            "label": "Calendar Minus"
        },
        {
            "value": "far fa-calendar-minus",
            "label": "Calendar Minus"
        },
        {
            "value": "fas fa-calendar-day",
            "label": "Calendar with Day Focus"
        },
        {
            "value": "fas fa-calendar-check",
            "label": "Calendar Check"
        },
        {
            "value": "far fa-calendar-check",
            "label": "Calendar Check"
        },
        {
            "value": "fas fa-calendar-alt",
            "label": "Alternate Calendar"
        },
        {
            "value": "far fa-calendar-alt",
            "label": "Alternate Calendar"
        },
        {
            "value": "fas fa-calendar",
            "label": "Calendar"
        },
        {
            "value": "far fa-calendar",
            "label": "Calendar"
        },
        {
            "value": "fas fa-calculator",
            "label": "Calculator"
        },
        {
            "value": "fab fa-buysellads",
            "label": "BuySellAds"
        },
        {
            "value": "fab fa-buy-n-large",
            "label": "Buy n Large"
        },
        {
            "value": "fas fa-business-time",
            "label": "Business Time"
        },
        {
            "value": "fas fa-bus-alt",
            "label": "Bus Alt"
        },
        {
            "value": "fas fa-bus",
            "label": "Bus"
        },
        {
            "value": "fab fa-buromobelexperte",
            "label": "Büromöbel-Experte GmbH & Co. KG."
        },
        {
            "value": "fas fa-burn",
            "label": "Burn"
        },
        {
            "value": "fas fa-bullseye",
            "label": "Bullseye"
        },
        {
            "value": "fas fa-bullhorn",
            "label": "bullhorn"
        },
        {
            "value": "fas fa-building",
            "label": "Building"
        },
        {
            "value": "far fa-building",
            "label": "Building"
        },
        {
            "value": "fas fa-bug",
            "label": "Bug"
        },
        {
            "value": "fab fa-buffer",
            "label": "Buffer"
        },
        {
            "value": "fab fa-btc",
            "label": "BTC"
        },
        {
            "value": "fas fa-brush",
            "label": "Brush"
        },
        {
            "value": "fas fa-broom",
            "label": "Broom"
        },
        {
            "value": "fas fa-broadcast-tower",
            "label": "Broadcast Tower"
        },
        {
            "value": "fas fa-briefcase-medical",
            "label": "Medical Briefcase"
        },
        {
            "value": "fas fa-briefcase",
            "label": "Briefcase"
        },
        {
            "value": "fas fa-bread-slice",
            "label": "Bread Slice"
        },
        {
            "value": "fas fa-brain",
            "label": "Brain"
        },
        {
            "value": "fas fa-braille",
            "label": "Braille"
        },
        {
            "value": "fas fa-boxes",
            "label": "Boxes"
        },
        {
            "value": "fas fa-box-open",
            "label": "Box Open"
        },
        {
            "value": "fas fa-box",
            "label": "Box"
        },
        {
            "value": "fas fa-bowling-ball",
            "label": "Bowling Ball"
        },
        {
            "value": "fas fa-border-style",
            "label": "Border Style"
        },
        {
            "value": "fas fa-border-none",
            "label": "Border None"
        },
        {
            "value": "fas fa-border-all",
            "label": "Border All"
        },
        {
            "value": "fab fa-bootstrap",
            "label": "Bootstrap"
        },
        {
            "value": "fas fa-bookmark",
            "label": "bookmark"
        },
        {
            "value": "far fa-bookmark",
            "label": "bookmark"
        },
        {
            "value": "fas fa-book-reader",
            "label": "Book Reader"
        },
        {
            "value": "fas fa-book-open",
            "label": "Book Open"
        },
        {
            "value": "fas fa-book-medical",
            "label": "Medical Book"
        },
        {
            "value": "fas fa-book-dead",
            "label": "Book of the Dead"
        },
        {
            "value": "fas fa-book",
            "label": "book"
        },
        {
            "value": "fas fa-bong",
            "label": "Bong"
        },
        {
            "value": "fas fa-bone",
            "label": "Bone"
        },
        {
            "value": "fas fa-bomb",
            "label": "Bomb"
        },
        {
            "value": "fas fa-bolt",
            "label": "Lightning Bolt"
        },
        {
            "value": "fas fa-bold",
            "label": "bold"
        },
        {
            "value": "fab fa-bluetooth-b",
            "label": "Bluetooth"
        },
        {
            "value": "fab fa-bluetooth",
            "label": "Bluetooth"
        },
        {
            "value": "fab fa-blogger-b",
            "label": "Blogger B"
        },
        {
            "value": "fab fa-blogger",
            "label": "Blogger"
        },
        {
            "value": "fas fa-blog",
            "label": "Blog"
        },
        {
            "value": "fas fa-blind",
            "label": "Blind"
        },
        {
            "value": "fas fa-blender-phone",
            "label": "Blender Phone"
        },
        {
            "value": "fas fa-blender",
            "label": "Blender"
        },
        {
            "value": "fab fa-blackberry",
            "label": "BlackBerry"
        },
        {
            "value": "fab fa-black-tie",
            "label": "Font Awesome Black Tie"
        },
        {
            "value": "fab fa-bity",
            "label": "Bity"
        },
        {
            "value": "fab fa-bitcoin",
            "label": "Bitcoin"
        },
        {
            "value": "fab fa-bitbucket",
            "label": "Bitbucket"
        },
        {
            "value": "fas fa-birthday-cake",
            "label": "Birthday Cake"
        },
        {
            "value": "fas fa-biohazard",
            "label": "Biohazard"
        },
        {
            "value": "fas fa-binoculars",
            "label": "Binoculars"
        },
        {
            "value": "fab fa-bimobject",
            "label": "BIMobject"
        },
        {
            "value": "fas fa-biking",
            "label": "Biking"
        },
        {
            "value": "fas fa-bicycle",
            "label": "Bicycle"
        },
        {
            "value": "fas fa-bible",
            "label": "Bible"
        },
        {
            "value": "fas fa-bezier-curve",
            "label": "Bezier Curve"
        },
        {
            "value": "fas fa-bell-slash",
            "label": "Bell Slash"
        },
        {
            "value": "far fa-bell-slash",
            "label": "Bell Slash"
        },
        {
            "value": "fas fa-bell",
            "label": "bell"
        },
        {
            "value": "far fa-bell",
            "label": "bell"
        },
        {
            "value": "fab fa-behance-square",
            "label": "Behance Square"
        },
        {
            "value": "fab fa-behance",
            "label": "Behance"
        },
        {
            "value": "fas fa-beer",
            "label": "beer"
        },
        {
            "value": "fas fa-bed",
            "label": "Bed"
        },
        {
            "value": "fab fa-battle-net",
            "label": "Battle.net"
        },
        {
            "value": "fas fa-battery-three-quarters",
            "label": "Battery 3/4 Full"
        },
        {
            "value": "fas fa-battery-quarter",
            "label": "Battery 1/4 Full"
        },
        {
            "value": "fas fa-battery-half",
            "label": "Battery 1/2 Full"
        },
        {
            "value": "fas fa-battery-full",
            "label": "Battery Full"
        },
        {
            "value": "fas fa-battery-empty",
            "label": "Battery Empty"
        },
        {
            "value": "fas fa-bath",
            "label": "Bath"
        },
        {
            "value": "fas fa-basketball-ball",
            "label": "Basketball Ball"
        },
        {
            "value": "fas fa-baseball-ball",
            "label": "Baseball Ball"
        },
        {
            "value": "fas fa-bars",
            "label": "Bars"
        },
        {
            "value": "fas fa-barcode",
            "label": "barcode"
        },
        {
            "value": "fab fa-bandcamp",
            "label": "Bandcamp"
        },
        {
            "value": "fas fa-band-aid",
            "label": "Band-Aid"
        },
        {
            "value": "fas fa-ban",
            "label": "ban"
        },
        {
            "value": "fas fa-balance-scale-right",
            "label": "Balance Scale (Right-Weighted)"
        },
        {
            "value": "fas fa-balance-scale-left",
            "label": "Balance Scale (Left-Weighted)"
        },
        {
            "value": "fas fa-balance-scale",
            "label": "Balance Scale"
        },
        {
            "value": "fas fa-bahai",
            "label": "Bahá'í"
        },
        {
            "value": "fas fa-bacon",
            "label": "Bacon"
        },
        {
            "value": "fas fa-backward",
            "label": "backward"
        },
        {
            "value": "fas fa-backspace",
            "label": "Backspace"
        },
        {
            "value": "fas fa-baby-carriage",
            "label": "Baby Carriage"
        },
        {
            "value": "fas fa-baby",
            "label": "Baby"
        },
        {
            "value": "fab fa-aws",
            "label": "Amazon Web Services (AWS)"
        },
        {
            "value": "fas fa-award",
            "label": "Award"
        },
        {
            "value": "fab fa-aviato",
            "label": "Aviato"
        },
        {
            "value": "fab fa-avianex",
            "label": "avianex"
        },
        {
            "value": "fab fa-autoprefixer",
            "label": "Autoprefixer"
        },
        {
            "value": "fas fa-audio-description",
            "label": "Audio Description"
        },
        {
            "value": "fab fa-audible",
            "label": "Audible"
        },
        {
            "value": "fas fa-atom",
            "label": "Atom"
        },
        {
            "value": "fab fa-atlassian",
            "label": "Atlassian"
        },
        {
            "value": "fas fa-atlas",
            "label": "Atlas"
        },
        {
            "value": "fas fa-at",
            "label": "At"
        },
        {
            "value": "fab fa-asymmetrik",
            "label": "Asymmetrik, Ltd."
        },
        {
            "value": "fas fa-asterisk",
            "label": "asterisk"
        },
        {
            "value": "fas fa-assistive-listening-systems",
            "label": "Assistive Listening Systems"
        },
        {
            "value": "fab fa-artstation",
            "label": "Artstation"
        },
        {
            "value": "fas fa-arrows-alt-v",
            "label": "Alternate Arrows Vertical"
        },
        {
            "value": "fas fa-arrows-alt-h",
            "label": "Alternate Arrows Horizontal"
        },
        {
            "value": "fas fa-arrows-alt",
            "label": "Alternate Arrows"
        },
        {
            "value": "fas fa-arrow-up",
            "label": "arrow-up"
        },
        {
            "value": "fas fa-arrow-right",
            "label": "arrow-right"
        },
        {
            "value": "fas fa-arrow-left",
            "label": "arrow-left"
        },
        {
            "value": "fas fa-arrow-down",
            "label": "arrow-down"
        },
        {
            "value": "fas fa-arrow-circle-up",
            "label": "Arrow Circle Up"
        },
        {
            "value": "fas fa-arrow-circle-right",
            "label": "Arrow Circle Right"
        },
        {
            "value": "fas fa-arrow-circle-left",
            "label": "Arrow Circle Left"
        },
        {
            "value": "fas fa-arrow-circle-down",
            "label": "Arrow Circle Down"
        },
        {
            "value": "fas fa-arrow-alt-circle-up",
            "label": "Alternate Arrow Circle Up"
        },
        {
            "value": "far fa-arrow-alt-circle-up",
            "label": "Alternate Arrow Circle Up"
        },
        {
            "value": "fas fa-arrow-alt-circle-right",
            "label": "Alternate Arrow Circle Right"
        },
        {
            "value": "far fa-arrow-alt-circle-right",
            "label": "Alternate Arrow Circle Right"
        },
        {
            "value": "fas fa-arrow-alt-circle-left",
            "label": "Alternate Arrow Circle Left"
        },
        {
            "value": "far fa-arrow-alt-circle-left",
            "label": "Alternate Arrow Circle Left"
        },
        {
            "value": "fas fa-arrow-alt-circle-down",
            "label": "Alternate Arrow Circle Down"
        },
        {
            "value": "far fa-arrow-alt-circle-down",
            "label": "Alternate Arrow Circle Down"
        },
        {
            "value": "fas fa-archway",
            "label": "Archway"
        },
        {
            "value": "fas fa-archive",
            "label": "Archive"
        },
        {
            "value": "fab fa-apple-pay",
            "label": "Apple Pay"
        },
        {
            "value": "fas fa-apple-alt",
            "label": "Fruit Apple"
        },
        {
            "value": "fab fa-apple",
            "label": "Apple"
        },
        {
            "value": "fab fa-apper",
            "label": "Apper Systems AB"
        },
        {
            "value": "fab fa-app-store-ios",
            "label": "iOS App Store"
        },
        {
            "value": "fab fa-app-store",
            "label": "App Store"
        },
        {
            "value": "fas fa-ankh",
            "label": "Ankh"
        },
        {
            "value": "fab fa-angular",
            "label": "Angular"
        },
        {
            "value": "fab fa-angrycreative",
            "label": "Angry Creative"
        },
        {
            "value": "fas fa-angry",
            "label": "Angry Face"
        },
        {
            "value": "far fa-angry",
            "label": "Angry Face"
        },
        {
            "value": "fas fa-angle-up",
            "label": "angle-up"
        },
        {
            "value": "fas fa-angle-right",
            "label": "angle-right"
        },
        {
            "value": "fas fa-angle-left",
            "label": "angle-left"
        },
        {
            "value": "fas fa-angle-down",
            "label": "angle-down"
        },
        {
            "value": "fas fa-angle-double-up",
            "label": "Angle Double Up"
        },
        {
            "value": "fas fa-angle-double-right",
            "label": "Angle Double Right"
        },
        {
            "value": "fas fa-angle-double-left",
            "label": "Angle Double Left"
        },
        {
            "value": "fas fa-angle-double-down",
            "label": "Angle Double Down"
        },
        {
            "value": "fab fa-angellist",
            "label": "AngelList"
        },
        {
            "value": "fab fa-android",
            "label": "Android"
        },
        {
            "value": "fas fa-anchor",
            "label": "Anchor"
        },
        {
            "value": "fab fa-amilia",
            "label": "Amilia"
        },
        {
            "value": "fas fa-american-sign-language-interpreting",
            "label": "American Sign Language Interpreting"
        },
        {
            "value": "fas fa-ambulance",
            "label": "ambulance"
        },
        {
            "value": "fab fa-amazon-pay",
            "label": "Amazon Pay"
        },
        {
            "value": "fab fa-amazon",
            "label": "Amazon"
        },
        {
            "value": "fas fa-allergies",
            "label": "Allergies"
        },
        {
            "value": "fab fa-alipay",
            "label": "Alipay"
        },
        {
            "value": "fas fa-align-right",
            "label": "align-right"
        },
        {
            "value": "fas fa-align-left",
            "label": "align-left"
        },
        {
            "value": "fas fa-align-justify",
            "label": "align-justify"
        },
        {
            "value": "fas fa-align-center",
            "label": "align-center"
        },
        {
            "value": "fab fa-algolia",
            "label": "Algolia"
        },
        {
            "value": "fab fa-airbnb",
            "label": "Airbnb"
        },
        {
            "value": "fas fa-air-freshener",
            "label": "Air Freshener"
        },
        {
            "value": "fab fa-affiliatetheme",
            "label": "affiliatetheme"
        },
        {
            "value": "fab fa-adversal",
            "label": "Adversal"
        },
        {
            "value": "fab fa-adobe",
            "label": "Adobe"
        },
        {
            "value": "fab fa-adn",
            "label": "App.net"
        },
        {
            "value": "fas fa-adjust",
            "label": "adjust"
        },
        {
            "value": "fas fa-address-card",
            "label": "Address Card"
        },
        {
            "value": "far fa-address-card",
            "label": "Address Card"
        },
        {
            "value": "fas fa-address-book",
            "label": "Address Book"
        },
        {
            "value": "far fa-address-book",
            "label": "Address Book"
        },
        {
            "value": "fas fa-ad",
            "label": "Ad"
        },
        {
            "value": "fab fa-acquisitions-incorporated",
            "label": "Acquisitions Incorporated"
        },
        {
            "value": "fab fa-accusoft",
            "label": "Accusoft"
        },
        {
            "value": "fab fa-accessible-icon",
            "label": "Accessible Icon"
        },
        {
            "value": "fab fa-500px",
            "label": "500px"
        }
    ];
    this._iconClass = function() {
        let fonts = [];
        FontAwesome.hits.forEach(function(item){
            if (item && item.name && item.styles) {
                let name = "fa-" + item.name;
                item.styles.forEach(function (style) {
                    if (item.membership.free.includes(style)) {
                        fonts.push({
                            value: 'fa' + style.charAt(0) + ' ' + name,
                            label: item.label
                        });
                    }
                });
            }
        });
        return fonts;
    };

};

MoorlFoundation.prototype = {};

const MoorlFoundationInstance = new MoorlFoundation();

window.MoorlFoundation = MoorlFoundationInstance;
exports.default = MoorlFoundationInstance;
module.exports = exports.default;
