Ext.define('App.core.ModalSelectWindow', {
    extend: 'App.core.ModalModule',
    toolbar: null,
    mixins: {
        modelLoader: 'Ext.ux.index.mixins.ModelLoaderWithStore'
    },

    grid: null,

    windowWidth: 600,

    windowHeight: 500,

    createListGrid: function () {
        var me = this,
            gridConfig;

        if (me.userRights > 0) {
            gridConfig = {
                modelClassName: me.modelClassName,
                getDataAction: me.getDataAction,
                saveAction: me.saveAction,
                deleteAction: me.deleteAction,
                store: me.store,
                selModel: Ext.create('Ext.selection.CheckboxModel', {
                    mode: "SINGLE"
                }),
                listeners: {
                    selectionchange: function (grid, selected, eOpts) {
                    }
                }
            };

            me.grid = Ext.create('Ext.ux.index.grid.ListGrid', gridConfig);
        }
    },

    init: function () {
        var me = this;
        if (me.userRights > 0) {
            if (me.getDataAction.length) {
                me.createActions();
                me.createModelClass();
                if (me.modelClassName) {
                    me.createStore();
                    me.createListGrid();
                    me._mainWindow = Ext.create('Ext.Window', {
                        title: me.modelTitle,
                        modal: true,
                        resizable: true,
                        closeAction: 'hide',
                        width: me.windowWidth,
                        height: me.windowHeight,
                        layout: 'fit',
                        items: me.grid,
                        buttons: [
                            {
                                text: 'Выбрать',
                                handler: function () {
                                    var selected;
                                    if (me.grid.getSelectionModel().getCount()) {
                                        me.fireEvent('select', me.grid.getSelectionModel().getSelection()[0]);
                                        me._mainWindow.hide();
                                    } else {
                                        IndexNextApp.getApplication().showErrorMessage('', 'Вы не выбрали запись');
                                    }
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