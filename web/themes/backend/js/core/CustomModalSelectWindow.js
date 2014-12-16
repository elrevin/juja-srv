Ext.define('App.core.CustomModalSelectWindow', {
    extend: 'App.core.ModalModule',
    toolbar: null,
    mixins: {
        modelLoader: 'Ext.ux.index.mixins.ModelLoaderWithStore'
    },
    windowWidth: 600,

    windowHeight: 500,

    itemsPanel: null,

    createItemsPanel: function () {},

    selectButtonClick: function () {},

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
                        resizable: true,
                        closeAction: 'hide',
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