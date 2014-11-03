Ext.define('App.core.SingleModelEditor', {
    extend: 'App.core.Module',
    form: null,
    modelName: '',
    modelClassName: '',
    model: null,
    fields: [],
    grid: null,
    getDataAction: [],
    saveAction: [],
    deleteAction: [],
    store: null,
    toolbar: null,

    /**
     * Функция создает базовый класс модели, по набору полей в свойстве fields
     */
    createModelClass: function () {
        var me = this,
            fieldIndex,
            modelClassDefinition;
        me.modelClassName = me.modelName + "Model";

        if (!Ext.ClassManager.isCreated(me.modelClassName)) {
            if (me.fields.length) {
                me.modelClassName = me.modelName + "Model";
                modelClassDefinition = {
                    extend: 'Ext.data.Model',
                    fields: [{
                        name: 'id',
                        type: 'int',
                        defaultValue: 0
                    }]
                };
                for (var i = 0; i < this.fields.length; i++) {
                    fieldIndex = modelClassDefinition.fields.length;
                    modelClassDefinition.fields[fieldIndex] = {
                        name: me.fields[i].name,
                        type: me.fields[i].type,
                        title: me.fields[i].title,
                        group: me.fields[i].group,
                        identify: me.fields[i].identify
                    };
                }
                Ext.define(me.modelClassName, modelClassDefinition);
            }
        }
    },

    createStore: function () {
        var me = this;
        me.store = new Ext.data.JsonStore ({
            model: me.modelClassName,
            proxy: {
                type: 'ajax',
                reader: {
                    type: 'json',
                    root: 'data',
                    idProperty: 'id',
                    successProperty: 'success'
                },
                writer: {
                    type: 'json',
                    encode: true,
                    root: 'data',
                    dateFormat: 'Y-m-d',
                    dateTimeFormat: 'Y-m-d H:i:s'
                },
                actionMethods: {read: 'GET', update: 'POST'},
                api: {
                    create  : $url(me.saveAction[0], me.saveAction[1], me.saveAction[2], {modelName: me.modelClassName.replace('Model', ''), add: 1}),
                    read    : $url(me.getDataAction[0], me.getDataAction[1], me.getDataAction[2], {modelName: me.modelClassName.replace('Model', '')}),
                    update  : $url(me.saveAction[0], me.saveAction[1], me.saveAction[2], {modelName: me.modelClassName.replace('Model', '')}),
                    destroy : $url(me.deleteAction[0], me.deleteAction[1], me.deleteAction[2], {modelName: me.modelClassName.replace('Model', '')})
                }
            },
            pageSize: me.pageSize,
            autoLoad: true,
            autoSync: true,
            listeners: {
                update: function (store, record, operation) {
                    // Загружаем в форму запись
                    me.form.loadRecord(record);
                }
            }
        });
    },
    
    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            height: 58,
            style: "background: #f0f0f0",
            defaults: {
                scale: 'medium'
            },
            items: [
                {
                    xtype: 'button',
                    text: 'Добавить',
                    icon: $themeUrl('/images/buttons/plus.png'),
                    scope: this,
                    handler: function () {
                    }
                }, '-', {
                    xtype: 'button',
                    icon: $themeUrl('/images/buttons/del.png'),
                    scope: this,
                    handler: function () {
                    }
                }, {
                    icon: $themeUrl('/images/buttons/copy.png')
                }
            ]
        });
        return me.toolbar;
    },

    createListGrid: function () {
        var me = this;

        me.grid = Ext.create('Ext.ux.index.grid.ListGrid', {
            modelClassName: me.modelClassName,
            getDataAction: me.getDataAction,
            saveAction: me.saveAction,
            deleteAction: me.deleteAction,
            store: me.store,
            selModel: Ext.create('Ext.selection.CheckboxModel', {
                mode: "MULTI"
            }),
            tbar: me.createToolbar()
        });
    },

    init: function () {
        var me = this;
        if (me.getDataAction.length) {
            if (!me.saveAction.length) {
                me.saveAction[0] = me.getDataAction[0];
                me.saveAction[1] = me.getDataAction[1];
                me.saveAction[2] = 'save';
            }

            if (!me.deleteAction.length) {
                me.deleteAction[0] = me.getDataAction[0];
                me.deleteAction[1] = me.getDataAction[1];
                me.deleteAction[2] = 'delete';
            }

            me.createModelClass();
            if (me.modelClassName) {
                me.model = Ext.create(me.modelClassName, {});
                me.form = Ext.create('Ext.ux.index.form.Form', {
                    model: me.model,
                    listeners: {
                        afterinsert: function () {
                            me.store.add(me.model);
                        }
                    }
                });

                me.createStore();

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
    }
});