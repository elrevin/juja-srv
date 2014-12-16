Ext.define('App.core.GridModalSelectWindow', {
    extend: 'App.core.CustomModalSelectWindow',
    createItemsPanel: function () {
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

            me.itemsPanel = Ext.create('Ext.ux.index.grid.ListGrid', gridConfig);
        }
    },

    selectButtonClick: function () {
        var me = this;
        if (me.grid.getSelectionModel().getCount()) {
            me.fireEvent('select', me.grid.getSelectionModel().getSelection()[0]);
            me._mainWindow.hide();
        } else {
            IndexNextApp.getApplication().showErrorMessage('', 'Вы не выбрали запись');
        }
    }
});