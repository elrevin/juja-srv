Ext.define('App.core.ModalModule', {
    extend: 'App.core.Module',
    mixins: {
        observable: 'Ext.util.Observable'
    },
    _mainWindow: null,
    getMainWindow: function () {
        return this._mainWindow;
    },
    canDestroy: function () {
        return true;
    },
    constructor: function (config) {
        var me = this;
        me.callParent(arguments);
        me.addEvents('select');
    },
    init: function () {
        this.fireEvent('ready');
    }
});