Ext.define('Ext.ux.index.mixins.ModelLoaderWithStore', {
    modelTitle: '',
    modelName: '',
    modelClassName: '',
    fields: [],
    getDataAction: [],
    saveAction: [],
    deleteAction: [],
    sortable: false,
    store: null,
    // Права общие пользователя на справочник:
    // 0 - нет прав вобще, просто валимся с ошибкой
    // 1 - Только чтение, все поля должны быть readonly и скрываем кнопки "Добавить", "Удалить", "Сохранить"  и пр.
    // 2 - Возможна запись, скрываем кнопку "Удалить"
    // 3 - Полный доступ
    userRights: 0,

    recordTitle: '',
    accusativeRecordTitle: '',

    getFields: function () {
        var me = this,
            fieldIndex,
            fieldConf,
            fields = [];

        for (var i = 0; i < this.fields.length; i++) {
            fieldIndex = fields.length;
            fieldConf = {
                name: me.fields[i].name,
                type: me.fields[i].type,
                title: me.fields[i].title,
                group: me.fields[i].group,
                identify: me.fields[i].identify,
                required: me.fields[i].required,
                settings: me.fields[i].settings
            };

            if (me.fields[i].relativeModel != undefined && me.fields[i].relativeModel.name != undefined && me.fields[i].relativeModel.moduleName != undefined) {
                fieldConf['relativeModel'] = me.fields[i].relativeModel;
            }

            fields[fieldIndex] = fieldConf;
        }

        return fields;
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
                modelClassDefinition['fields'] = me.getFields();
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
                actionMethods: {read: 'POST', update: 'POST'},
                api: {
                    create: $url(me.saveAction[0], me.saveAction[1], me.saveAction[2], {
                        modelName: me.modelClassName.replace('Model', ''),
                        add: 1
                    }),
                    read: $url(me.getDataAction[0], me.getDataAction[1], me.getDataAction[2], {modelName: me.modelClassName.replace('Model', '')}),
                    update: $url(me.saveAction[0], me.saveAction[1], me.saveAction[2], {modelName: me.modelClassName.replace('Model', '')}),
                    destroy: $url(me.deleteAction[0], me.deleteAction[1], me.deleteAction[2], {modelName: me.modelClassName.replace('Model', '')})
                },
                extraParams: {
                    params: Ext.JSON.encode(me.params)
                }
            },
            pageSize: me.pageSize,
            autoLoad: me.userRights >= 1,
            remoteSort: true
        });
    },

    createActions: function () {
        var me = this;
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
    }
});