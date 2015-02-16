Ext.define('Ext.ux.index.form.SimpleForm', {
    extend: 'Ext.form.Panel',
    alias: ['widget.uxindextabform'],

    model: null,
    modelClassName: '',

    userRights: 0,

    identifyField: null,
    width: 450,
    mode: 'insert', // По умолчанию режим добавления записи

    groups: [],

    _showConditionAnalytic: function (field, formField) {
        var me = this,
          fieldItem,
            i, j, fieldsCount = me.model.fields.getCount(),
          show, process, value;

        for (i = 0; i < fieldsCount; i++) {
            fieldItem = me.model.fields.get(i);
            show = true;
            process = false;
            if (fieldItem.showCondition && fieldItem.showCondition[field.name]) {
                var showConditionArr = fieldItem.showCondition[field.name];
                if (!(showConditionArr instanceof Array)) {
                    showConditionArr = [showConditionArr];
                }
                process = true;

                for (j = 0; j < showConditionArr.length; j++) {
                    showCondition = showConditionArr[j];

                    if (field.type == Ext.data.Types.INTEGER || field.type == Ext.data.Types.FLOAT ||
                        field.type == Ext.data.Types.STRING || field.type == Ext.data.Types.TEXT ||
                        field.type == Ext.data.Types.DATE || field.type == Ext.data.Types.DATETIME
                    ) {
                        if (showCondition.operation == 'set') {
                            show = (show && formField.getValue() ? true : false);
                        } else if (showCondition.operation == 'notset') {
                            show = (show && !formField.getValue() ? true : false);
                        } else {
                            if (field.type == Ext.data.Types.DATE) {
                                value = Ext.Date.format(formField.getValue(), 'Y-m-d');
                            } else if (field.type == Ext.data.Types.DATETIME) {
                                value = Ext.Date.format(formField.getValue(), 'Y-m-d H:i:s');
                            } else {
                                value = formField.getValue();
                            }

                            if (showCondition.operation == '==') {
                                show = (show && value == showCondition.value);
                            } else if (showCondition.operation == '!=') {
                                show = (show && value != showCondition.value);
                            } else if (showCondition.operation == '>') {
                                show = (show && value > showCondition.value);
                            } else if (showCondition.operation == '<') {
                                show = (show && value < showCondition.value);
                            } else if (showCondition.operation == '>=') {
                                show = (show && value >= showCondition.value);
                            } else if (showCondition.operation == '<=') {
                                show = (show && value <= showCondition.value);
                            }
                        }
                    } else if (field.type == Ext.data.Types.BOOL) {
                        show = (show && (showCondition.operation == 'set' && formField.getValue()) ||
                        (showCondition.operation == 'notset' && !formField.getValue()));
                    } else if (field.type == Ext.data.Types.POINTER) {
                        fieldValue = formField.getValue().value;
                        if (showCondition.operation == '==') {
                            show = (show && fieldValue == showCondition.value);
                        } else if (showCondition.operation == '!=') {
                            show = (show && fieldValue != showCondition.value);
                        } else if (showCondition.operation == 'set') {
                            show = (show && fieldValue ? true : false);
                        } else if (showCondition.operation == 'notset') {
                            show = (show && !fieldValue ? true : false);
                        }
                    }
                }
            }

            if (process) {
                Ext.getCmp(me.id + '_field_' + fieldItem.name).setVisible(show);
            }
        }
    },
    _showFilterAnalytic: function (field, formField) {

        var me = this,
            fieldItem,
            i, j, fieldsCount = me.model.fields.getCount();

        for (i = 0; i < fieldsCount; i++) {
            fieldItem = me.model.fields.get(i);

            if (fieldItem.filterCondition && fieldItem.filterCondition[field.name]) {
                var filterConditionArr = fieldItem.filterCondition[field.name];
                var filterType = fieldItem.filterCondition['type'];

                if (!(filterConditionArr instanceof Array)) {
                    filterConditionArr = [filterConditionArr];
                }

                for (j = 0; j < filterConditionArr.length; j++) {
                    filterCondition = filterConditionArr[j];
                    if (field.type == Ext.data.Types.POINTER && formField.value != null && filterCondition == 'eq') {
                        store = Ext.getCmp(me.id + '_field_' + fieldItem.name).getStore();
                        store.getProxy().setExtraParam('colFilter', Ext.JSON.encode([{
                            type: filterType,
                            comparison: filterCondition,
                            value: formField.value,
                            field: field.name
                        }]));
                        store.reload();
                    }
                }
            }
        }
    },
    _getField: function (field) {
        var me = this;

        if (field.type == Ext.data.Types.INTEGER) {
            if (field.name == 'id') {
                // если ID, то создаем скрытое поле
                return Ext.create('Ext.form.field.Hidden', {
                    name: 'id',
                    id: me.id + '_field_' + field.name,
                    modelField: field,
                    enableKeyEvents: true
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
                modelField: field,
                enableKeyEvents: true,
                _keypressTimeout: null,
                readOnly: field.calc,
                listeners: {
                    keypress: function (thisField) {
                        if (thisField._keypressTimeout) {
                            clearTimeout(thisField._keypressTimeout);
                            thisField._keypressTimeout = null;
                        }

                        thisField._keypressTimeout = setTimeout(function () {
                            me._showConditionAnalytic(thisField.modelField, thisField);
                            thisField._keypressTimeout = null;
                        }, 500);
                    },
                    change: function (thisField) {
                        me._showConditionAnalytic(thisField.modelField, thisField);
                    }
                }
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
                modelField: field,
                enableKeyEvents: true,
                readOnly: field.calc,
                _keypressTimeout: null,
                listeners: {
                    keypress: function (thisField) {
                        if (thisField._keypressTimeout) {
                            clearTimeout(thisField._keypressTimeout);
                            thisField._keypressTimeout = null;
                        }

                        thisField._keypressTimeout = setTimeout(function () {
                            me._showConditionAnalytic(thisField.modelField, thisField);
                            thisField._keypressTimeout = null;
                        }, 500);
                    },
                    change: function (thisField) {
                        me._showConditionAnalytic(thisField.modelField, thisField);
                    }
                }
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
                modelField: field,
                enableKeyEvents: true,
                readOnly: field.calc,
                _keypressTimeout: null,
                listeners: {
                    keypress: function (thisField) {
                        if (thisField._keypressTimeout) {
                            clearTimeout(thisField._keypressTimeout);
                            thisField._keypressTimeout = null;
                        }

                        thisField._keypressTimeout = setTimeout(function () {
                            me._showConditionAnalytic(thisField.modelField, thisField);
                            thisField._keypressTimeout = null;
                        }, 500);
                    },
                    change: function (thisField) {
                        me._showConditionAnalytic(thisField.modelField, thisField);
                    }
                }
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
                modelField: field,
                enableKeyEvents: true,
                readOnly: field.calc,
                _keypressTimeout: null,
                listeners: {
                    keypress: function (thisField) {
                        if (thisField._keypressTimeout) {
                            clearTimeout(thisField._keypressTimeout);
                            thisField._keypressTimeout = null;
                        }

                        thisField._keypressTimeout = setTimeout(function () {
                            me._showConditionAnalytic(thisField.modelField, thisField);
                            thisField._keypressTimeout = null;
                        }, 500);
                    },
                    change: function (thisField) {
                        me._showConditionAnalytic(thisField.modelField, thisField);
                    }
                }
            });
        } else if (field.type == Ext.data.Types.HTML) {
            // Многострочный текст HTML

            return Ext.create("Ext.ux.form.TinyMCETextArea", {
                fieldLabel: field.title,
                labelAlign: 'top',
                msgTarget: 'side',
                modelField: field,
                width: 400,
                height: 400,
                name: field.name,
                fieldStyle: 'font-family: Courier New; font-size: 12px;',
                id: me.id + '_field_' + field.name,
                noWysiwyg: false,
                tinyMCEConfig: {
                    language: 'ru',
                    theme: "advanced",
                    //skin: "extjs",
                    inlinepopups_skin: "extjs",
                    template_external_list_url: "example_template_list.js",
                    theme_advanced_row_height: 27,
                    delta_height: 1,
                    //schema: "html5",
                    plugins: "autolink,lists,style,table,advimage,advlink,inlinepopups,preview,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist",
                    theme_advanced_buttons1: "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,|,styleselect,formatselect",
                    theme_advanced_buttons2: "undo,redo,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,link,unlink,anchor,image,|,cleanup,code",
                    theme_advanced_buttons3: "tablecontrols,|,sub,sup,|,charmap",
                    theme_advanced_toolbar_location: "top",
                    theme_advanced_toolbar_align: "left",
                    theme_advanced_statusbar_location: "bottom",
                    relative_urls: false,
                    remove_script_host: true,
                    document_base_url: "/",
                    convert_urls: false,
                    //content_css: "/themes/frontend/css/style.css",
                    file_browser_callback: function (field_name, url, type, win) {
                        var _type = type;
                        var _field_name = field_name;
                        var _win = win;

                        IndexNextApp.getApplication().loadModule({
                            runAction: ['files', 'main', 'get-interface'],
                            listeners: {
                                select: function (record) {
                                    _win.document.getElementById(_field_name).value = record.get('path');
                                    if (_win.ImageDialog && _win.ImageDialog.showPreviewImage) {
                                        _win.ImageDialog.showPreviewImage(record.get('path'));
                                    }
                                }
                            },
                            modal: true,
                            modelName: 'files',
                            params: {
                                types: (_type == 'image' ? ['img'] : [])
                            }
                        });
                    },
                    resize: true
                }
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
                modelField: field,
                readOnly: field.calc,
                listeners: {
                    change: function (thisField) {
                        me._showConditionAnalytic(thisField.modelField, thisField);
                    }
                }
            });
        } else if (field.type == Ext.data.Types.DATETIME) {
            // Дата и время
            return Ext.create('Ext.ux.form.field.DateTime', {
                name: field.name,
                id: me.id + '_field_' + field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 250,
                allowBlank: !field.required,
                format: 'd.m.Y',
                submitFormat: 'Y-m-d H:i:s',
                msgTarget: 'side',
                modelField: field,
                readOnly: field.calc,
                listeners: {
                    change: function (thisField) {
                        me._showConditionAnalytic(thisField.modelField, thisField);
                    }
                }
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
                modelField: field,
                readOnly: field.calc,
                listeners: {
                    change: function (thisField) {
                        me._showConditionAnalytic(thisField.modelField, thisField);
                    }
                }
            });
        } else if (field.type == Ext.data.Types.SELECT) {
            // фиксированный список
            var data = [], key;
            for (key in field.selectOptions) {
                data[data.length] = {
                    id: key,
                    value: field.selectOptions[key]
                }
            }
            return Ext.create('Ext.ux.form.ClearableComboBox', {
                name: field.name,
                id: me.id+'_field_'+field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 400,
                allowBlank: !field.required,
                msgTarget: 'side',
                displayField: 'value',
                valueField: 'id',
                editable: false,
                isPointerField: true,
                store: Ext.create('Ext.data.Store', {
                    fields: [{name: 'id', type: 'string'}, 'value'],
                    data: data
                }),
                modelField: field,
                listeners: {
                    change: function (thisField) {
                        me._showConditionAnalytic(thisField.modelField, thisField);
                    }
                }
            });
        } else if (field.type == Ext.data.Types.POINTER && !field.relativeModel.modalSelect) {
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
                modelField: field,
                listeners: {
                    change: function (thisField) {
                        me._showConditionAnalytic(thisField.modelField, thisField);
                        me._showFilterAnalytic(thisField.modelField, thisField);
                    }
                }
            });
        } else if (field.type == Ext.data.Types.POINTER && field.relativeModel.modalSelect) {
            // Справочник
            return Ext.create('Ext.ux.form.field.ModalSelect', {
                name: field.name,
                id: me.id+'_field_'+field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 400,
                allowBlank: !field.required,
                msgTarget: 'side',
                editable: false,
                isPointerField: true,
                modelField: field,
                modelName: field.relativeModel.name,
                runAction: field.relativeModel.runAction,
                listeners: {
                    change: function (thisField) {
                        me._showConditionAnalytic(thisField.modelField, thisField);
                    }
                }
            });
        } else if (field.type == Ext.data.Types.FILE) {
            return Ext.create('Ext.ux.form.field.File', {
                name: field.name,
                id: me.id+'_field_'+field.name,
                fieldLabel: field.title,
                labelAlign: 'top',
                width: 500,
                allowBlank: !field.required,
                msgTarget: 'side',
                isPointerField: true,
                modelField: field,
                modelName: field.relativeModel.name,
                runAction: field.relativeModel.runAction,
                fieldSettings: field.settings
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

                if (field.extra) {
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

                me.groups = groups;
            }

            for (i =0; i < modelFieldsCount; i++) {
                field = me.model.fields.get(i);
                me._showConditionAnalytic(field, Ext.getCmp(me.id + '_field_' + field.name));
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

    afterInitComponent: function () {},

    initComponent: function () {
        var me = this;
        me.beforeInitComponent();
        me.callParent();
        me.createFields();
        me.afterInitComponent();
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
        var me = this,
          i, fieldsCount = me.model.fields.getCount(), field;

        me.mode = 'update';

        if (record == undefined || !record) {
            record = me.model;
        } else {
            me.model = record;
        }

        me.beforeLoad(record);
        me.callParent([record]);
        me.afterLoad(record);

        for (i = 0; i < fieldsCount; i++) {
            field = me.model.fields.get(i);
            me._showConditionAnalytic(field, Ext.getCmp(me.id + '_field_' + field.name));
        }
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
                    if (!field.calc) {
                        input = Ext.getCmp(me.id + '_field_' + field.name);
                        if (input) {
                            if (input.modelField.type == Ext.data.Types.POINTER || input.modelField.type == Ext.data.Types.SELECT || input.modelField.type == Ext.data.Types.FILE) {
                                values[field.name] = Ext.JSON.encode(input.getValue());
                            } else {
                                values[field.name] = input.getValue();
                            }
                        }
                    }
                }
            }

            me.model.set(values);

            me.fireEvent((me.mode == 'update' ? 'afterupdate' : 'afterinsert'), me, me.model);

            me.mode = (me.mode == 'insert' ? 'update' : 'update');
        } else {
            IndexNextApp.getApplication().showErrorMessage(null, 'Некоторые поля заполнены не правильно или не заполнены совсем.<br>Поля содержащие ошибки отмечены иконкой <img src="'+$assetUrl('/js/ext/resources/ext-theme-neptune/images/form/exclamation.png')+'" /> и красной обводкой.<br/>'+
                                                            'Наведя мышь на иконку <img src="'+$assetUrl('/js/ext/resources/ext-theme-neptune/images/form/exclamation.png')+'" /> рядом с полем, Вы увидите пояснение ошибки.');
        }
    },

    getInputFieldByName: function (name) {
        var me = this;
        return Ext.getCmp(me.id + '_field_' + name);
    }

});