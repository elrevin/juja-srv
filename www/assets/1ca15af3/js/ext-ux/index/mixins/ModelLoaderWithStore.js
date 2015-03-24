Ext.define('Ext.ux.index.mixins.ModelLoaderWithStore', {
    modelTitle: '',
    modelName: '',
    modelClassName: '',
    fields: null,
    getDataAction: null,
    saveAction: null,
    deleteAction: null,
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

    constructor: function (config) {
        var me = this;
        Ext.apply(me, config);
    },

    getProxyConfig: function () {
        var me = this,
            config = {
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
                        modelName: me.modelClassName.replace('ModelClass', ''),
                        add: 1
                    }),
                    read: $url(me.getDataAction[0], me.getDataAction[1], me.getDataAction[2], {modelName: me.modelClassName.replace('ModelClass', '')}),
                    update: $url(me.saveAction[0], me.saveAction[1], me.saveAction[2], {modelName: me.modelClassName.replace('ModelClass', '')}),
                    destroy: $url(me.deleteAction[0], me.deleteAction[1], me.deleteAction[2], {modelName: me.modelClassName.replace('ModelClass', '')})
                },
                extraParams: {
                    params: Ext.JSON.encode(me.params)
                }
            };
        return config;
    },

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
                settings: me.fields[i].settings,
                showCondition: me.fields[i].showCondition,
                filterCondition: me.fields[i].filterCondition,
                calc: me.fields[i].calc,
                selectOptions: me.fields[i].selectOptions,
                extra: me.fields[i].extra,
                readonly: me.fields[i].readonly
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
    createModelClass: function (withProxy) {
        var me = this,
            fieldIndex,
            modelClassDefinition,
            fieldConf;

        me.modelClassName = me.modelName + "ModelClass";

        if (!Ext.ClassManager.isCreated(me.modelClassName)) {
            if (me.fields.length) {
                modelClassDefinition = {
                    extend: 'Ext.data.Model',
                    fields: me.getFields(),
                    recordTitle: me.recordTitle,
                    accusativeRecordTitle: me.accusativeRecordTitle,
                    recursive: me.recursive,
                    getDataAction: me.getDataAction,
                    saveAction: me.saveAction,
                    deleteAction: me.deleteAction,
                    parentModelName: me.parentModelName
                };
                modelClassDefinition['fields'][modelClassDefinition['fields'].length] = {
                    name: 'id',
                    type: 'int',
                    defaultValue: 0
                };

                if (withProxy) {
                    modelClassDefinition['proxy'] = me.getProxyConfig();
                }

                Ext.define(me.modelClassName, modelClassDefinition);
            }
        }
    },

    createStore: function () {
        var me = this,
          pageSize = localStorageGet(me.modelClassName+"_pageSize", 50);
        me.store = new Ext.data.JsonStore({
            model: me.modelClassName,
            proxy: me.getProxyConfig(),
            pageSize: pageSize,
            autoLoad: me.userRights >= 1,
            remoteSort: true
        });
    },

    createActions: function () {
        var me = this;
        if (!me.saveAction || !me.saveAction.length) {
            me.saveAction = [];
            me.saveAction[0] = me.getDataAction[0];
            me.saveAction[1] = me.getDataAction[1];
            me.saveAction[2] = 'save-record';
        }

        if (!me.deleteAction || !me.deleteAction.length) {
            me.deleteAction = [];
            me.deleteAction[0] = me.getDataAction[0];
            me.deleteAction[1] = me.getDataAction[1];
            me.deleteAction[2] = 'delete-record';
        }
    }
});