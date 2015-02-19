Ext.define('App.core.SingleModelEditor', {
    extend: 'App.core.Module',
    mixins: {
        modelLoader: 'Ext.ux.index.mixins.ModelLoaderWithStore'
    },

    form: null,

    grid: null,
    toolbar: null,

    tabs: [],

    addRecord: function () {
        var me = this;

        me.form.renew();
    },

    copyRecord: function () {
        var me = this,
            selectModel = me.grid.getSelectionModel(),
            selections;

        if (selectModel.getCount()) {
            selections = selectModel.getSelection();
            me.form.copy(selections[0]);
        }
    },

    deleteRecord: function () {
        var me = this,
            selectModel = me.grid.getSelectionModel(),
            selections,
            i, count;

        count = selectModel.getCount();

        if (count) {
            Ext.Msg.show({
                title: 'Удаление записи',
                msg: 'Вы действительно хотите удалить '+me.accusativeRecordTitle.toLocaleLowerCase(),
                width: 300,
                buttons: Ext.Msg.YESNO,
                icon: Ext.window.MessageBox.QUESTION,
                fn: function (button) {
                    if (button == 'yes') {
                        selections = selectModel.getSelection();
                        me.store.remove(selections);
                        me.store.sync({
                            failure: function () {
                                me.store.reload();
                            }
                        });
                    }
                }
            });
        }
    },

    createToolbar: function () {
        var me = this,
          buttons = [];

        if (me.userRights > 1) {
            buttons[buttons.length] = {
                xtype: 'button',
                text: 'Добавить',
                icon: $assetUrl('/images/buttons/plus.png'),
                scope: this,
                itemId: 'add',
                handler: function () {
                    me.addRecord();
                }
            };
            buttons[buttons.length] = { xtype: 'tbspacer' };

            if (me.userRights > 2) {
                buttons[buttons.length] = {
                    xtype: 'button',
                    icon: $assetUrl('/images/buttons/del.png'),
                    scope: this,
                    itemId: 'del',
                    disabled: true,
                    handler: function () {
                        me.deleteRecord();
                    }
                };
            }

            buttons[buttons.length] = {
                itemId: 'copy',
                disabled: true,
                icon: $assetUrl('/images/buttons/copy.png'),
                handler: function () {
                    me.copyRecord();
                }
            };

            me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
                height: 58,
                style: "background: #f0f0f0",
                defaults: {
                    scale: 'medium'
                },
                items: buttons
            });
        }

        return me.toolbar;
    },

    selectionChange: function (selected) {
        var me = this;

        if (selected.length == 1) {
            me.form.loadRecord(selected[0]);

            if (me.userRights > 1) {
                me.toolbar.getComponent('copy').setDisabled(false);
            }
        } else {
            if (me.userRights > 1) {
                me.toolbar.getComponent('copy').setDisabled(true);
            }
        }

        if (selected.length) {
            if (me.userRights > 2) {
                me.toolbar.getComponent('del').setDisabled(false);
            }
        } else {
            if (me.userRights > 2) {
                me.toolbar.getComponent('del').setDisabled(true);
            }
        }
    },

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
                sortable: me.sortable,
                selModel: Ext.create('Ext.selection.CheckboxModel', {
                    //mode: "MULTI"
                }),
                listeners: {
                    selectionchange: function (grid, selected, eOpts) {
                        me.selectionChange(selected);
                    }
                }
            };

            if (me.createToolbar()) {
                gridConfig['tbar'] = me.toolbar;
            }

            me.grid = Ext.create('Ext.ux.index.grid.ListGrid', gridConfig);
        }
    },

    createForm: function () {
        var me = this;

        me.form = Ext.create('Ext.ux.index.form.Form', {
            modelClassName: me.modelClassName,
            tabs: me.tabs,
            listeners: {
                afterinsert: function (form, record) {
                    me.store.add(record);
                    me.store.sync({
                        failure: function () {
                            me.store.reload();
                            // Возвращяем режим записи
                            me.form.mode = 'insert';
                        }
                    });
                },
                afterupdate: function (form, record) {
                    me.store.sync({
                        failure: function () {
                            me.store.reload();
                        }
                    });
                }
            },
            userRights: me.userRights
        });
    },

    init: function () {
        var me = this;
        if (me.userRights > 0) {
            if (me.getDataAction.length) {
                me.createActions();
                me.createModelClass();
                if (me.modelClassName) {

                    me.createForm();

                    me.createStore();

                    me.store.on('update', function (store, record) {
                        me.form.loadRecord(record);
                    });

                    me.createListGrid();

                    me._mainPanel = Ext.create('Ext.Panel', {
                        layout: 'border',
                        items: [
                            me.grid,
                            {
                                xtype: 'panel',
                                layout: 'fit',
                                region: 'center',
                                items: [
                                    me.form
                                ]
                            }
                        ]
                    });
                    //me.store.reload();
                    me.callParent();
                } else {
                    me.fireEvent('initfail');
                }
            } else {
                me.fireEvent('initfail');
            }
        } else {
            me.fireEvent('initfail');
        }
    }
});