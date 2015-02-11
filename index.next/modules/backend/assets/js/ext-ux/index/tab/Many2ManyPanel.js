Ext.define('Ext.ux.index.tab.Many2ManyPanel', {
    extend: 'Ext.Panel',
    alias: ['widget.uxindextabitempanelmany2many'],
    mixins: {
        modelLoader: 'Ext.ux.index.mixins.ModelLoaderWithStore'
    },
    model: null,
    grid: null,
    toolbar: null,
    parentForm: null,
    title: 'tabPanel',
    editorWindow: null,
    // тип панели
    //      button - таблица c модальным окном и кнопками Добавить, Удалить
    //      checkbox - таблица c выводом всех элементов из зависимой модели с возможностью выбора
    typeGrid: 'button',
    runAction: [],
    modelName: '',
    linkModelRunAction: null,

    afterParentLoad: function (record) {
        var me = this;
        if (me.userRights >= 1) {
            me.store.getProxy().setExtraParam('masterId', record.get('id'));
            me.store.load();
        }
    },

    createListGrid: function () {
        var me = this,
            gridConfig;

        if (me.userRights > 0) {

            if(me.typeGrid == 'button') {
                gridConfig = {
                    modelClassName: me.modelClassName,
                    getDataAction: me.getDataAction,
                    saveAction: me.saveAction,
                    deleteAction: me.deleteAction,
                    store: me.store,
                    tbar: me.toolbar,
                    sortable: me.sortable,
                    selModel: Ext.create('Ext.selection.CheckboxModel', {
                        mode: "MULTI"
                    }),
                    listeners: {
                        selectionchange: function (grid, selected, eOpts) {
                            if (selected.length > 0) {
                                me.toolbar.items.items[1].enable();
                            } else {
                                me.toolbar.items.items[1].disable();
                            }
                        }
                    }
                };
            } else {
                gridConfig = {
                    modelClassName: me.modelClassName,
                    getDataAction: me.getDataAction,
                    saveAction: me.saveAction,
                    deleteAction: me.deleteAction,
                    store: me.store,
                    sortable: me.sortable
                };
            }

            me.grid = Ext.create('Ext.ux.index.grid.ListGrid', gridConfig);
        }
    },

    /*********************** для tabGrid == 'button' ***********************/

    addRecord: function () {
        var me = this;
        if (me.linkModelRunAction && me.linkModelName) {
            IndexNextApp.getApplication().loadModule({
                runAction: me.linkModelRunAction,
                listeners: {
                    select: function (record) {
                        var newRec = Ext.create(me.modelClassName);

                        for (var i = 0; i < me.fields.length; i++) {
                            newRec.set(me.fields[i].name, Ext.JSON.encode({
                                id: record.get('id'),
                                value: record.get(me.fields[i].relativeModel.identifyFieldName)
                            }));
                        }

                        me.store.add(newRec);
                        me.store.sync({
                            failure: function () {
                                me.store.reload();
                            }
                        });
                    }
                },
                modal: true,
                modelName: me.linkModelName
            });
        }
    },

    deleteRecords: function (records) {
        var me = this,
            recs = records;
        Ext.Msg.show({
            title: 'Удаление записи',
            msg: 'Вы действительно хотите удалить '+me.accusativeRecordTitle.toLocaleLowerCase(),
            width: 300,
            buttons: Ext.Msg.YESNO,
            icon: Ext.window.MessageBox.QUESTION,
            fn: function (button) {
                if (button == 'yes') {
                    me.store.remove(recs);
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
            if (me.userRights > 1 && me.typeGrid == 'button') {
                me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
                    height: 58,
                    defaults: {
                        scale: 'medium'
                    },
                    items: [
                        {
                            text: 'Добавить',
                            icon: $assetUrl('/images/buttons/plus.png'),
                            handler: function () {
                                me.addRecord();
                            }
                        },
                        {
                            icon: $assetUrl('/images/buttons/del.png'),
                            disabled: true,
                            handler: function () {
                                me.deleteRecords(me.grid.getSelectionModel().getSelection());
                            }
                        }
                    ]
                });
            }
        }
    },

    /**************************************************************************/

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

                    me.items = [me.grid];
                    me.activeItem = 0;
                }
            }
            if (me.parentForm) {
                me.parentForm.on('afterload', function (form, record) {
                    me.afterParentLoad(record);
                });
            }
            me.runAction = [me.modelName];
        }

        me.callParent();
    }
});