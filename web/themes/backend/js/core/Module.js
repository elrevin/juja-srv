Ext.define('App.core.Module', {
    mixins: {
        observable: 'Ext.util.Observable'
    },
    _mainPanel: null,
    getMainPanel: function () {
        return this._mainPanel;
    },
    canDestroy: function () {
        return true;
    },
    constructor: function (config) {
        var me = this;
        this.mixins.observable.constructor.call(this, config);
        Ext.apply(me, config);
        me.addEvents('ready', 'initfail');
    },
    init: function () {
        IndexNextApp.getApplication().getMainPanel().add(this._mainPanel);
        this.fireEvent('ready');
    }
});