Ext.define('Ext.ux.index.form.SimpleForm', {
    extend: 'Ext.form.Panel',
    alias: ['widget.uxindextabform'],

    model: null,
    modelClassName: '',

    userRights: 0,

    identifyField: null,
    width: 450,
    mode: 'insert', // По умолчанию режим добавления записи

    _getField: function (field) {
        var me = this;

        if (field.type == Ext.data.Types.INTEGER) {
            if (field.name == 'id') {
                // если ID, то создаем скрытое поле
                return Ext.create('Ext.form.field.Hidden', {
                    name: 'id',
                    id: me.id + '_field_' + field.name,
                    modelField: field
                });
            }
            // Обычное целочисленное поле
            return Ext.create('Ext.form.field.Number', {
                allowDecimals: false,
                name: field.name,
                id: me.id + '_field_' + field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 150,
                allowBlank: !field.required,
                msgTarget: 'side',
                modelField: field
            });
        } else if (field.type == Ext.data.Types.FLOAT) {
            // Число с точкой
            return Ext.create('Ext.form.field.Number', {
                allowDecimals: true,
                name: field.name,
                id: me.id + '_field_' + field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 150,
                allowBlank: !field.required,
                msgTarget: 'side',
                modelField: field
            });
        } else if (field.type == Ext.data.Types.STRING) {
            // Обычное строковое поле
            return Ext.create('Ext.form.field.Text', {
                name: field.name,
                id: me.id + '_field_' + field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 400,
                allowBlank: !field.required,
                msgTarget: 'side',
                modelField: field
            });
        } else if (field.type == Ext.data.Types.TEXT) {
            // Многострочный текст
            return Ext.create('Ext.form.field.TextArea', {
                name: field.name,
                id: me.id + '_field_' + field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 400,
                height: 80,
                allowBlank: !field.required,
                msgTarget: 'side',
                modelField: field
            });
        } else if (field.type == Ext.data.Types.DATE) {
            // Дата
            return Ext.create('Ext.form.field.Date', {
                name: field.name,
                id: me.id + '_field_' + field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 110,
                allowBlank: !field.required,
                format: 'd.m.Y',
                submitFormat: 'Y-m-d',
                msgTarget: 'side',
                modelField: field
            });
        } else if (field.type == Ext.data.Types.DATETIME) {
            // Дата и время
            return Ext.create('Ext.ux.form.DateTimeField', {
                name: field.name,
                id: me.id + '_field_' + field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 160,
                allowBlank: !field.required,
                format: 'd.m.Y',
                submitFormat: 'Y-m-d H:i:s',
                msgTarget: 'side',
                modelField: field
            });
        } else if (field.type == Ext.data.Types.BOOL) {
            // Дата и время
            var labelWidth = getFormFieldLabelsWidth(field.title);
            labelWidth = Math.ceil(labelWidth + labelWidth * 0.2);
            return Ext.create('Ext.form.field.Checkbox', {
                checked: false,
                name: field.name,
                id: me.id + '_field_' + field.name,
                fieldLabel: field.title,
                labelAlign: 'left',
                labelWidth: labelWidth,
                modelField: field
            });
        } else if (field.type == Ext.data.Types.POINTER) {
            // Справочник
            var url = $url(field.relativeModel.moduleName, 'main', 'list', {modelName: field.relativeModel.name, identifyOnly: 1});
            return Ext.create('Ext.ux.form.ClearableComboBox', {
                name: field.name,
                id: me.id+'_field_'+field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 400,
                allowBlank: !field.required,
                msgTarget: 'side',
                displayField: field.relativeModel.identifyFieldName,
                valueField: 'id',
                editable: false,
                isPointerField: true,
                store: new Ext.data.JsonStore ({
                    proxy: {
                        type: 'ajax',
                        url: url,
                        actionMethods:  {read: "GET"},
                        extraParams: {},
                        reader: {
                            type: 'json',
                            root: 'data',
                            idProperty: 'id'
                        }
                    },
                    fields: [{name: 'id', type: 'integer'}, {name: field.relativeModel.identifyFieldName, type: field.relativeModel.identifyFieldType}],
                    pageSize: 50,
                    autoLoad: true
                }),
                modelField: field
            });
        }

        return null;
    },

    createFields: function () {
        var me = this,
            modelFieldsCount,
            currentContainer,
            groups = [],
            i,
            filtered = [],
            currentField = null,
            field;

        if (me.model && (modelFieldsCount = me.model.fields.getCount())) {
            for (i = 0; i < modelFieldsCount; i++) {
                field = me.model.fields.getAt(i);
                if (field.identify) {
                    continue;
                }
                if (field.group) {
                    filtered = (function (field, groups) {
                        return groups.filter(function (element) {
                            return element.title == field.group;
                        })
                    })(field, groups);

                    if (!filtered.length) {
                        groups[groups.length] = {
                            title: field.group,
                            obj: null
                        }
                    }
                }
            }

            for (i = 0; i < modelFieldsCount; i++) {
                field = me.model.fields.getAt(i);
                if (field.identify) {
                    continue;
                }

                currentContainer = null;

                if (groups.length > 1) {
                    filtered = (function (field, groups) {
                        return groups.filter(function (element) {
                            return element.title == field.group;
                        })
                    })(field, groups);

                    if (filtered.length) {
                        currentContainer = filtered[0].obj;
                        if (!currentContainer) {
                            currentContainer = Ext.create('Ext.ux.index.form.FieldSet', {
                                title: filtered[0].title,
                                collapsible: true,
                                width: 600,
                                collapsed: (groups.length > 1 && filtered[0] != groups[0])
                            });
                            filtered[0].obj = currentContainer;
                        }
                    }
                }

                if (!currentContainer) {
                    currentContainer = me;
                }

                currentField = me._getField(field);
                if (currentField) {
                    currentContainer.add(currentField);
                }
            }

            if (groups.length > 1) {
                for (i = 0; i < groups.length; i++) {
                    if (groups[i].obj) {
                        me.add(groups[i].obj);
                    }
                }
            }
        }
    },

    beforeInitComponent: function () {
        var me = this,
            modelFieldsCount;

        me.addEvents('beforeload', 'afterload', 'beforeupdate', 'afterupdate', 'beforeinsert', 'afterinsert');
        me.bodyCls = 'in2-editor-form';
        me.layout = 'anchor';

        if (!me.model) {
            me.model = Ext.create(me.modelClassName, {});
        }

        if (me.model && (modelFieldsCount = me.model.fields.getCount())) {
            for (i = 0; i < modelFieldsCount; i++) {
                field = me.model.fields.getAt(i);
                if (field.identify) {
                    me.identifyField = field;
                    break;
                }
            }
        }
    },

    initComponent: function () {
        var me = this;
        me.beforeInitComponent();
        me.callParent();
        me.createFields();
    },

    beforeLoad: function (record) {
        var me = this;
        me.fireEvent('beforeload', me, record);
    },

    afterLoad: function (record) {
        var me = this;
        me.fireEvent('afterload', me, record);
    },

    loadRecord: function (record) {
        var me = this;

        me.mode = 'update';

        if (record == undefined || !record) {
            record = me.model;
        } else {
            me.model = record;
        }

        me.beforeLoad(record);
        me.callParent([record]);
        me.afterLoad(record);
    },

    save: function () {
        var me = this,
            modelFieldsCount,
            field,
            input,
            values = {};

        if (me.isValid()) {
            me.fireEvent((me.mode == 'update' ? 'beforeupdate' : 'beforeinsert'), me, me.model);

            if (me.model && (modelFieldsCount = me.model.fields.getCount())) {
                for (var i = 0; i < modelFieldsCount; i++) {
                    field = me.model.fields.getAt(i);
                    input = Ext.getCmp(me.id + '_field_' + field.name);
                    if (input) {
                        if (input.modelField.type == Ext.data.Types.POINTER) {
                            values[field.name] = Ext.JSON.encode(input.getValue());
                        } else {
                            values[field.name] = input.getValue();
                        }
                    }
                }
            }

            me.model.set(values);

            me.fireEvent((me.mode == 'update' ? 'afterupdate' : 'afterinsert'), me, me.model);

            me.mode = (me.mode == 'insert' ? 'update' : 'update');
        } else {
            IndexNextApp.getApplication().showErrorMessage(null, 'Некоторые поля заполнены не правильно или не заполнены совсем.<br>Поля содержащие ошибки отмечены иконкой <img src="'+$themeUrl('/js/ext/resources/ext-theme-neptune/images/form/exclamation.png')+'" /> и красной обводкой.<br/>'+
                                                            'Наведя мышь на иконку <img src="'+$themeUrl('/js/ext/resources/ext-theme-neptune/images/form/exclamation.png')+'" /> рядом с полем, Вы увидите пояснение ошибки.');
        }
    }

});