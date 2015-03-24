Ext.define('App.core.CustomModalSelectWindow', {
    extend: 'App.core.ModalModule',
    toolbar: null,
    mixins: {
        modelLoader: 'Ext.ux.index.mixins.ModelLoaderWithStore'
    },
    windowWidth: 600,

    windowHeight: 500,

    windowResizable: true,

    itemsPanel: null,

    createItemsPanel: function () {},

    selectButtonClick: function () {},
    doSelect: function (rec) {
        var me = this;
        me.fireEvent('select', rec);
        me._mainWindow.close();
    },

    init: function () {
        var me = this;
        if (me.userRights > 0) {
            if (me.getDataAction.length) {
                me.createActions();
                me.createModelClass();
                if (me.modelClassName) {
                    me.createStore();
                    me.createItemsPanel();
                    me._mainWindow = Ext.create('Ext.Window', {
                        title: me.modelTitle,
                        modal: true,
                        resizable: me.windowResizable,
                        closeAction: 'destroy',
                        width: me.windowWidth,
                        height: me.windowHeight,
                        layout: 'fit',
                        items: me.itemsPanel,
                        buttons: [
                            {
                                text: 'Выбрать',
                                handler: function () {
                                    me.selectButtonClick();
                                }
                            }
                        ]
                    });

                    me.callParent();

                    me._mainWindow.show();
                }
            }
        }
    }

});