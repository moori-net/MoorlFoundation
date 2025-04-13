const MoorlFoundation = function MoorlFoundation() {
    this.listingLayout = [];
    this.itemLayout = [];
    this.displayMode = [];
    this.mode = [];
    this.navigationArrows = [];
    this.navigationDots = [];
    this.colorScheme = [];
    this.btnClass = [];
    this.verticalAlign = [];
    this.horizontalAlign = [];
    this.textAlign = [];
    this.verticalTextAlign = [];
    this.animateCss = [];
    this.iconClass = [];
};

MoorlFoundation.prototype = {};

const MoorlFoundationInstance = new MoorlFoundation();

window.MoorlFoundation = MoorlFoundationInstance;
exports.default = MoorlFoundationInstance;
module.exports = exports.default;
