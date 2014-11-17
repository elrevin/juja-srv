Ext.define('Ext.ux.index.tab.DetailPanel', {
    extend: 'Ext.Panel',
    alias: ['widget.uxindextabitempanel'],
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
    form: null,
    title: 'tabPanel',
    userRights: 0,

    afterParentLoad: function (record) {
        console.log(record.get('id'));
    },

    /**
     * Функция создает базовый класс модели, по набору полей в свойстве fields
     */
    createModelClass: function () {
        var me = this,
          fieldIndex,
          modelClassDefinition,
          fieldConf;
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
                    fieldConf = {
                        name: me.fields[i].name,
                        type: me.fields[i].type,
                        title: me.fields[i].title,
                        group: me.fields[i].group,
                        identify: me.fields[i].identify,
                        required: me.fields[i].required
                    };

                    if (fieldConf.type == 'pointer' && me.fields[i].relativeModel != undefined && me.fields[i].relativeModel.name != undefined && me.fields[i].relativeModel.moduleName != undefined) {
                        fieldConf['relativeModel'] = me.fields[i].relativeModel;
                    }

                    modelClassDefinition.fields[fieldIndex] = fieldConf;
                }
                Ext.define(me.modelClassName, modelClassDefinition);
            }
        }
    },

    createStore: function () {
        var me = this;
        me.store = new Ext.data.JsonStore({
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
                    create: $url(me.saveAction[0], me.saveAction[1], me.saveAction[2], {
                        modelName: me.modelClassName.replace('Model', ''),
                        add: 1
                    }),
                    read: $url(me.getDataAction[0], me.getDataAction[1], me.getDataAction[2], {modelName: me.modelClassName.replace('Model', '')}),
                    update: $url(me.saveAction[0], me.saveAction[1], me.saveAction[2], {modelName: me.modelClassName.replace('Model', '')}),
                    destroy: $url(me.deleteAction[0], me.deleteAction[1], me.deleteAction[2], {modelName: me.modelClassName.replace('Model', '')})
                }
            },
            pageSize: me.pageSize,
            autoLoad: me.userRights >= 1,
            //autoSync: true,
            listeners: {
                update: function (store, record, operation) {
                    // Загружаем в форму запись
                    //me.form.loadRecord(record);
                }
            }
        });
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
                selModel: Ext.create('Ext.selection.CheckboxModel', {
                    //mode: "MULTI"
                }),
                //tbar: me.createToolbar(),
                listeners: {
                    selectionchange: function (grid, selected, eOpts) {
                        //if (selected.length) {
                        //    me.form.loadRecord(selected[0]);
                        //}
                    }
                }
            };

            //if (me.createToolbar()) {
            //    gridConfig['tbar'] = me.toolbar;
            //}

            me.grid = Ext.create('Ext.ux.index.grid.ListGrid', gridConfig);
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

                me.layout = 'fit';

                me.createModelClass();

                if (me.modelClassName) {
                    me.model = Ext.create(me.modelClassName, {});

                    me.createStore();

                    me.createListGrid();

                    me.items = [me.grid];
                }
            }
            if (me.form) {
                me.form.on('afterload', function (form, record) {
                    me.afterParentLoad(record);
                });
            }
        }

        me.callParent();
    }
});