Ext.define('Classes.ClientsEditor', {
    extend: 'Ext.ux.index.editors.SingleListEditor',
    constructor: function (config) {
        var me = this;
        config.mainModel = Ext.apply({
            getFormConfig: function () {
                return me.getFormConfig.call(me);
            }
        }, config.mainModel);
        this.callParent(arguments);
    },

    initAdditions: function () {

        var self = this;

        this.industriesModel = Ext.define('ClientsIndustriesModel', {
            extend: 'Ext.data.Model',
            fields: ['id', 'industry_title', 'industry_id', 'checked'],
            idProperty: 'id'
        });

        this.industries = Ext.create('Ext.grid.Panel', {
            header: false,
            border: false,
            forceFit: true,
            tbar: [{
                text: 'Сохранить изменения',
                scope: this,
                //disabled: true,
                handler: function () {
                    self.industries.getStore().sync({
                        success: function (batch) {
                            self.industries.getStore().load();
                        }
                    });
                }
            }],
            columnLines: true,
            store: new Ext.data.JsonStore({
                model: 'ClientsIndustriesModel',
                proxy: {
                    type: 'ajax',
                    api: {
                        create: '/admin/clients/clientsadm/updateIndustries?ajax=1&answer_type=json&id=' + this.getMainModelRecordId(),
                        read: '/admin/clients/clientsadm/listIndustries?ajax=1&answer_type=json&id=' + this.getMainModelRecordId(),
                        update: '/admin/clients/clientsadm/updateIndustries?ajax=1&answer_type=json&id=' + this.getMainModelRecordId(),
                        destroy: '/admin/clients/clientsadm/updateIndustries?ajax=1&answer_type=json&id=' + this.getMainModelRecordId()
                    },
                    actionMethods: {read: 'GET', update: 'POST'},
                    reader: {
                        type: 'json',
                        successProperty: 'success',
                        root: 'data',
                        messageProperty: 'message'
                    },
                    writer: {
                        type: 'json',
                        encode: true,
                        root: 'data'
                    }
                },
                fields: ['id', {name: 'industry_title', type: 'string'}, {
                    name: 'checked',
                    type: 'boolean'
                }, {name: 'industry_id', type: 'integer'}],
                autoLoad: false,
                autoSync: false,
                listeners: {
                    exception: function (proxy, response, operation) {
                        Ext.MessageBox.show({
                            title: 'REMOTE EXCEPTION',
                            msg: operation.getError(),
                            icon: Ext.MessageBox.ERROR,
                            buttons: Ext.Msg.OK
                        });
                    }
                }
            }),
            columns: {
                items: [
                    {
                        text: 'Да/Нет',
                        xtype: 'checkcolumn',
                        dataIndex: 'checked',
                        width: 100
                    },
                    {
                        text: 'Название',
                        dataIndex: 'industry_title'
                    }
                ],
                defaults: {
                    sortable: false,
                    hideable: false
                }
            }
        });

    },

    reloadAdditions: function () {
        if (this.getMainModelRecordId()) {
            this.industries.getStore().load({
                params: {
                    project: this.getMainModelRecordId()
                }
            });
        }
    },

    getFormConfig: function () {
        this.initAdditions();
        this.reloadAdditions();
        return {
            formType: 'tab',
            formTitle: 'Основные свойства',
            tabs: [
                {
                    title: 'Отрасли',
                    layout: 'fit',
                    items: [
                        this.industries
                    ],
                    hidden: (this.getMainModelRecordId() ? false : true)
                }
            ]
        };
    }
});

function createEditor(editorType, editorName, config) {
    if (NextIndexApp.getApplication().removeModuleFromMainPanel()) {
        var config = Ext.apply({name: editorName}, config);
        var module = Ext.create('Classes.ClientsEditor', config);
        NextIndexApp.getApplication().addModule(module, true);
        return module;
    }
}

createEditor('singlelist','ClientsEditor', {"mainModel":{"id":"8","class":"Clients","title":"\u043a\u043b\u0438\u0435\u043d\u0442","titleAc":"\u043a\u043b\u0438\u0435\u043d\u0442\u0430","fields":[{"id":"48","priority":"0","id_model":"8","id_fields_group":"1","title":"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435","name":"title","type":"string","readonly":"0","multival":"0","id_relative_model":"","select_options":"","show_condition":"","identify_field":"1","settings":"","required":"0","default":"","allow_group_edit":"0","fields_group_title":"\u041e\u0441\u043d\u043e\u0432\u043d\u044b\u0435 \u0441\u0432\u043e\u0439\u0441\u0442\u0432\u0430","rel_model_name":"","rel_model_table_name":"","addField":false},{"id":"49","priority":"0","id_model":"8","id_fields_group":"1","title":"\u0421\u0430\u0439\u0442","name":"site","type":"url","readonly":"0","multival":"0","id_relative_model":"","select_options":"","show_condition":"","identify_field":"0","settings":"","required":"0","default":"","allow_group_edit":"0","fields_group_title":"\u041e\u0441\u043d\u043e\u0432\u043d\u044b\u0435 \u0441\u0432\u043e\u0439\u0441\u0442\u0432\u0430","rel_model_name":"","rel_model_table_name":"","addField":false},{"id":"50","priority":"0","id_model":"8","id_fields_group":"1","title":"\u041a\u0440\u0430\u0442\u043a\u043e\u0435 \u043e\u043f\u0438\u0441\u0430\u043d\u0438\u0435","name":"short_description","type":"text","readonly":"0","multival":"0","id_relative_model":"","select_options":"","show_condition":"","identify_field":"0","settings":"","required":"0","default":"","allow_group_edit":"0","fields_group_title":"\u041e\u0441\u043d\u043e\u0432\u043d\u044b\u0435 \u0441\u0432\u043e\u0439\u0441\u0442\u0432\u0430","rel_model_name":"","rel_model_table_name":"","addField":false},{"id":"51","priority":"0","id_model":"8","id_fields_group":"1","title":"\u041e\u043f\u0438\u0441\u0430\u043d\u0438\u0435","name":"description","type":"text","readonly":"0","multival":"0","id_relative_model":"","select_options":"","show_condition":"","identify_field":"0","settings":"","required":"0","default":"","allow_group_edit":"0","fields_group_title":"\u041e\u0441\u043d\u043e\u0432\u043d\u044b\u0435 \u0441\u0432\u043e\u0439\u0441\u0442\u0432\u0430","rel_model_name":"","rel_model_table_name":"","addField":false},{"id":"261","priority":"22","id_model":"8","id_fields_group":"1","title":"\u041b\u043e\u0433\u043e\u0442\u0438\u043f","name":"logo","type":"img","readonly":"0","multival":"0","id_relative_model":"","select_options":"","show_condition":"","identify_field":"0","settings":"","required":"0","default":"","allow_group_edit":"0","fields_group_title":"\u041e\u0441\u043d\u043e\u0432\u043d\u044b\u0435 \u0441\u0432\u043e\u0439\u0441\u0442\u0432\u0430","rel_model_name":"","rel_model_table_name":"","addField":false},{"id":"294","priority":"49","id_model":"8","id_fields_group":"1","title":"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435 \u0432 \u0440\u043e\u0434\u0438\u0442\u0435\u043b\u044c\u0441\u043a\u043e\u043c \u043f\u0430\u0434\u0435\u0436\u0435","name":"title_rod","type":"string","readonly":"0","multival":"0","id_relative_model":"","select_options":"","show_condition":"","identify_field":"0","settings":"","required":"0","default":"","allow_group_edit":"0","fields_group_title":"\u041e\u0441\u043d\u043e\u0432\u043d\u044b\u0435 \u0441\u0432\u043e\u0439\u0441\u0442\u0432\u0430","rel_model_name":"","rel_model_table_name":"","addField":false}],"canEdit":true,"canDelete":true,"titleField":"title","moduleName":"clients","getDataAction":"clients\/clientsadm\/getList","saveCommand":"clients\/clientsadm\/save","deleteCommand":"clients\/clientsadm\/delete","sortable":1},"parentId":0,"parentIdParamName":"parentRecordId","action":"clients\/clientsadm\/getinterface","getDataAction":""})