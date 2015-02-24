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
    //      check - таблица c выводом всех элементов из зависимой модели с возможностью выбора
    slaveModelAddMethod: 'button',
    runAction: [],
    modelName: '',
    linkModelRunAction: null,
    masterId: 0,

    afterParentLoad: function (record) {
        var me = this;
        if (me.userRights >= 1) {
            me.store.getProxy().setExtraParam('masterId', record.get('id'));
            me.masterId = record.get('id');
            me.store.load();
        }
    },
    getFields: function () {
        var me = this,
            fields;
        fields = me.mixins.modelLoader.getFields.call(me);
        if(me.slaveModelAddMethod == 'check')
            fields[fields.length] = {
                name: 'check',
                type: 'bool',
                title: '',
                group: '',
                identify: false,
                required: false
            };
        return fields;
    },

    createListGrid: function () {
        var me = this,
            gridConfig;

        if (me.userRights > 0) {

            if(me.slaveModelAddMethod == 'button') {
                gridConfig = {
                    modelClassName: me.modelClassName,
                    getDataAction: me.getDataAction,
                    saveAction: me.saveAction,
                    deleteAction: me.deleteAction,
                    store: me.store,
                    tbar: me.toolbar,
                    sortable: me.sortable,
                    columns: [],
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
                    sortable: me.sortable,
                    columns: []
                };
                gridConfig['addColumns'] = [
                    {
                        xtype: 'checkcolumn',
                        dataIndex: 'check',
                        text: "Выбрать",
                        scope: this,
                        listeners: {
                            checkchange: function (col, rowIndex, checked) {

                                rec = me.store.getAt(rowIndex);

                                if(checked) {

                                    var newRec = Ext.create(me.modelClassName);
                                    var name = me.fields[0].name;
                                    newRec.getProxy().setExtraParam('masterId', me.masterId);
                                    newRec.set(name, Ext.JSON.encode({
                                        id: rec.data[name].id,
                                        value: rec.data[name].value
                                    }));
                                    newRec.save({
                                        callback: function () {
                                            me.store.reload();
                                        }
                                    });

                                } else {

                                    Ext.Ajax.request({
                                        url: $url(me.deleteAction[0], me.deleteAction[1], me.deleteAction[2], {modelName: me.modelClassName.replace('ModelClass', '')}),
                                        params: {
                                            data: Ext.JSON.encode({
                                                id: rec.get('id')
                                            })
                                        },
                                        success: function (response) {
                                            var data = Ext.JSON.decode(response.responseText);
                                            if (data.success) {
                                                me.store.reload();
                                            }
                                        }
                                    });

                                }

                            }
                        }
                    }
                ];
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
                modelName: me.linkModelName,
                columns: []
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
            if (me.userRights > 1 && me.slaveModelAddMethod == 'button') {
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
                if (!me.saveAction) {
                    me.saveAction = [];
                }
                if (!me.saveAction.length) {
                    me.saveAction[0] = me.getDataAction[0];
                    me.saveAction[1] = me.getDataAction[1];
                    me.saveAction[2] = 'save-record';
                }

                if (!me.deleteAction) {
                    me.deleteAction = [];
                }
                if (!me.deleteAction.length) {
                    me.deleteAction[0] = me.getDataAction[0];
                    me.deleteAction[1] = me.getDataAction[1];
                    me.deleteAction[2] = 'delete-record';
                }

                me.layout = "fit";

                me.createModelClass(true);

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