Ext.define('Ext.ux.index.tab.DetailPanel', {
    extend: 'Ext.Panel',
    alias: ['widget.uxindextabitempanel'],
    mixins: {
        modelLoader: 'Ext.ux.index.mixins.ModelLoaderWithStore'
    },
    model: null,
    grid: null,
    toolbar: null,
    parentForm: null,
    title: 'tabPanel',
    editorWindow: null,

    afterParentLoad: function (record) {
        var me = this;
        if (me.userRights >= 1) {
            me.store.getProxy().setExtraParam('parentId', record.get('id'));
            me.store.load();
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
                tbar: me.toolbar,
                sortable: me.sortable,
                selModel: Ext.create('Ext.selection.CheckboxModel', {
                    mode: "SINGLE"
                }),
                listeners: {
                    selectionchange: function (grid, selected, eOpts) {

                    }
                }
            };


            if (me.userRights > 1) {
                gridConfig['columns'] = [
                    {
                        xtype: 'actioncolumn',
                        width: 40,
                        sortable: false,
                        menuDisabled: true,
                        items: [{
                            icon: $themeUrl('/images/buttons/edit.png'),
                            tooltip: 'Изменить',
                            scope: this,
                            handler: function (view, rowIndex, colIndex, item, e, record, row) {
                                me.editRecord(record);
                            }
                        }, {
                            icon: $themeUrl('/images/buttons/del.png'),
                            tooltip: 'Удалить',
                            scope: this,
                            handler: function (view, rowIndex, colIndex, item, e, record, row) {
                                me.deleteRecord(record);
                            }
                        }]
                    }
                ];
            }

            me.grid = Ext.create('Ext.ux.index.grid.ListGrid', gridConfig);
        }
    },

    createEditorWindow: function () {
        var me = this;
        me.editorWindow = Ext.create('Ext.ux.index.window.EditorWindow', {
            model: me.model,
            userRights: me.userRights,
            recordTitle: me.recordTitle,
            accusativeRecordTitle: me.accusativeRecordTitle,
            store: me.store,
            listeners: {
                afterinsert: function () {
                    me.store.add(me.model);
                    me.store.sync({
                        failure: function () {
                            me.store.reload();
                            // Возвращяем режим записи
                            me.editorWindow.setMode('insert');
                            me.editorWindow.hide();
                        },
                        success: function () {
                            me.editorWindow.hide();
                        }
                    });
                },
                afterupdate: function () {
                    me.store.sync({
                        failure: function () {
                            me.store.reload();
                        },
                        success: function () {
                            me.editorWindow.hide();
                        }
                    });
                }
            }
        });
    },

    editRecord: function (record) {
        var me = this;
        me.model = record;
        me.editorWindow.loadRecord(record);
        me.editorWindow.show();
    },

    addRecord: function () {
        var me = this;
        me.model = Ext.create(me.modelClassName, {});
        me.editorWindow.loadRecord(me.model);
        me.editorWindow.setMode('insert');
        me.editorWindow.show();
    },

    deleteRecord: function (record) {
        var me = this,
            rec = record;
        Ext.Msg.show({
            title: 'Удаление записи',
            msg: 'Вы действительно хотите удалить '+me.accusativeRecordTitle.toLocaleLowerCase(),
            width: 300,
            buttons: Ext.Msg.YESNO,
            icon: Ext.window.MessageBox.QUESTION,
            fn: function (button) {
                if (button == 'yes') {
                    me.store.remove(rec);
                    me.store.sync({
                        failure: function () {
                            me.store.reload();
                        }
                    });
                }
            }
        });
    },

    createToolbar: function () {
        var me = this;
        if (!me.toolbar) {
            if (me.userRights > 1) {
                me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
                    height: 58,
                    //cls: 'in2-editor-form-toolbar',
                    defaults: {
                        scale: 'medium'
                    },
                    items: [
                        {
                            text: 'Добавить',
                            icon: $themeUrl('/images/buttons/plus.png'),
                            handler: function () {
                                me.addRecord();
                            }
                        }
                    ]
                });
            }
        }
    },

    initComponent: function () {
        var me = this;

        if (me.userRights > 0) {
            if (me.getDataAction.length) {
                if (!me.saveAction.length) {
                    me.saveAction[0] = me.getDataAction[0];
                    me.saveAction[1] = me.getDataAction[1];
                    me.saveAction[2] = 'save-record';
                }

                if (!me.deleteAction.length) {
                    me.deleteAction[0] = me.getDataAction[0];
                    me.deleteAction[1] = me.getDataAction[1];
                    me.deleteAction[2] = 'delete-record';
                }

                me.layout = "fit";

                me.createModelClass();

                if (me.modelClassName) {
                    me.model = Ext.create(me.modelClassName, {});

                    me.createStore();

                    me.createToolbar();

                    me.createListGrid();

                    me.createEditorWindow();

                    me.items = [me.grid];

                    me.activeItem = 0;
                }
            }
            if (me.parentForm) {
                me.parentForm.on('afterload', function (form, record) {
                    me.afterParentLoad(record);
                });
            }
        }

        me.callParent();
    }
});