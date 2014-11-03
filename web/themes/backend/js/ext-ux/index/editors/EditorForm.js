function customFileBrowser(field_name, url, type, win) {
    var _type = type;
    var _field_name = field_name;
    var _win = win;

    var _selWindow = Ext.create('Ext.ux.index.ImageSelectWindow', {imagesOnly: 0});

    _selWindow.show(function (imgId, path) {
        _win.document.getElementById(_field_name).value = path;
    });
}

Ext.define('Ext.ux.index.editors.EditorForm', {
    _editorPanel: null,
    panel: null,
    module: null,
    _model: null,
    _formPanel: null,
    _id: 0,
    _recordId: 0,
    _oldRecord: {},
    _mode: '',

    constructor: function (config) {
        var me = this;
        Ext.apply(me, config);
    },

    _processGettingDataUrl: function (url, field) {
        var record = this._model.record;
        var _url = url;
        if (typeof field.settings != 'undefined' && field.settings) {
            if ((typeof field.settings.addToGetRecordsCommand != 'undefined') && (field.settings.addToGetRecordsCommand)) {
                _url = _url + (_url.search(/\?/) >= 0 ? '&' : '?') + field.settings.addToGetRecordsCommand;
                var r = /FIELD_([a-zA-Z0-9_]+)/g;
                if (r.test(field.settings.addToGetRecordsCommand)) {
                    var arr = field.settings.addToGetRecordsCommand.match(r);
                    for (var i = 0; i < arr.length; i++) {
                        var s = arr[i].replace('FIELD_', '');
                        if (typeof record != 'undefined') {
                            if (typeof record[s] != 'undefined') {
                                _url = _url.replace(arr[i], record[s]);
                            } else {
                                showErrorMessage("0002", 'Поле "' + s + '" не определено в этой модели');
                            }
                        } else {
                            var cmp = Ext.getCmp(this._id + "_" + field.name);
                            if (!cmp) {
                                showErrorMessage("0001", 'Не найдено поле формы "' + field.name + '"');
                            }
                            var value = Ext.getCmp(this._id + "_" + field.name);
                            _url = _url.replace(arr[i], value);
                        }
                    }
                }
            }
        }
        return _url;
    },

    _processChangeValue: function (field) {

        var name = field.getName();
        name = name.replace("ff_", "");

        if (field.niField.type == 'bool') {
            this._model.record[name] = field.getValue() ? 1 : 0; // Именно так, нам нужна ЕДИНИЦА!
        } else {
            this._model.record[name] = field.getValue();
        }

        for (var i = 0; i < this._model.fields.length; i++) {
            if (this._model.fields[i].name == name) {
                continue;
            }
            var s = this._model.fields[i].show_condition;
            if (s) {
                var re = new RegExp("getFieldValue\\(['\"]" + name + "['\"]\\)");
                if (s.search(re) >= 0) {
                    Ext.getCmp(this._id + "_" + this._model.fields[i].name).setVisible(evaluate.call(this, s));
                }
            }

            if (this._model.fields[i].type == 'pointer') {
                if (this._model.fields[i].settings && typeof this._model.fields[i].settings['addToGetRecordsCommand'] != 'undefined' && this._model.fields[i].settings['addToGetRecordsCommand']) {
                    var url = '/admin/' + this._model.fields[i].get_data_command + (this._model.fields[i].get_data_command.search(/\?/) >= 0 ? '&' : '?') + 'ajax=1&answer_type=json&forSelect=1';
                    url = this._processGettingDataUrl(url, this._model.fields[i]);
                    Ext.getCmp(this._id + "_" + this._model.fields[i].name).getStore().getProxy().url = url;
                    Ext.getCmp(this._id + "_" + this._model.fields[i].name).getStore().reload();
                }
            }
        }
    },

    _getFormField: function (field, mode, index) {
        var value = (this._model.record ? this._model.record[field.name] : null);
        var record = this._model.record;
        var ret = null;
        var maxLength = 255;
        var fieldId = this._id + "_" + field.name;
        var fieldIndex = index;
        var show = true;
        var pointerType = 'standart';
        if (field.show_condition) {
            show = evaluate.call(this, field.show_condition);
        }
        if (field.settings && typeof field.settings.maxLength != 'undefined' && field.settings.maxLength) {
            maxLength = field.settings.maxLength;
        }
        if (field.settings && typeof field.settings.pointerType != 'undefined' && field.settings.pointerType) {
            pointerType = field.settings.pointerType;
        }
        var width = 0;
        if (field.settings && typeof field.settings.width != 'undefined' && field.settings.width) {
            width = field.settings.width;
        }
        var height = 0;
        if (field.settings && typeof field.settings.height != 'undefined' && field.settings.height) {
            height = field.settings.height;
        }

        if (field.default) {
            field.default = evaluate.call(this, field.default);
        }

        var fieldLabel = field.title;

        if (this.formConfig.overrideFields != undefined && this.formConfig.overrideFields[field.name] != undefined) {
            ret = this.formConfig.overrideFields[field.name]({
                width: (width ? width : 600),
                height: height,
                maxLength: maxLength,
                value: (mode == 'edit' ? value : field.default),
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                name: 'ff_' + field.name,
                enableKeyEvents: true,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0)
            }, field, record, this);
        } else if (field.type == 'string') {
            width = (width ? width : 600);
            ret = Ext.create("Ext.form.field.Text", {
                width: width,
                maxLength: maxLength,
                value: (mode == 'edit' ? value : field.default),
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                name: 'ff_' + field.name,
                enableKeyEvents: true,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0)
            });
            //ret.on('keypress', this._processChangeValue, this);
        } else if (field.type == 'int') {
            width = (width ? width : 100);
            ret = Ext.create("Ext.form.field.Number", {
                width: width,
                value: (mode == 'edit' ? value : (field.default ? field.default : 0)),
                allowDecimals: false,
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                name: 'ff_' + field.name,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0)
            });
        } else if (field.type == 'real') {
            width = (width ? width : 100);
            ret = Ext.create("Ext.form.field.Number", {
                width: width,
                value: (mode == 'edit' ? value : (field.default ? field.default : 0)),
                allowDecimals: true,
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                name: 'ff_' + field.name,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0),
                labelWidth: 200
            });
        } else if (field.type == 'date') {
            width = (width ? width : 150);
            ret = Ext.create("Ext.form.field.Date", {
                width: width,
                value: (mode == 'edit' ? (value ? value.split(' ')[0] : value) : (field.default ? field.default : '')),
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                name: 'ff_' + field.name,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0),
                format: 'd.m.Y',
                submitFormat: 'Y-m-d'
            });
        } else if (field.type == 'datetime') {
            width = (width ? width : 170);
            ret = Ext.create("Ext.ux.form.DateTimeField", {
                width: width,
                value: (mode == 'edit' ? mysqlTimeStampToDate(value) : (field.default ? mysqlTimeStampToDate(field.default) : '')),
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                name: 'ff_' + field.name,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0),
                format: 'd.m.Y',
                submitFormat: 'Y-m-d H:i:s'
            });
        } else if (field.type == 'bool') {
            var labelWidth = getFormFieldLabelsWidth(fieldLabel);
            labelWidth = Math.ceil(labelWidth + labelWidth * 0.2);
            if (mode == 'groupEdit') {
                ret = Ext.create("Ext.form.ComboBox", {
                    hideLabel: (mode == 'groupEdit'),
                    displayField: 'value',
                    valueField: 'id',
                    width: 100,
                    editable: false,
                    store: Ext.create('Ext.data.Store', {
                        fields: ['id', 'value'],
                        data: [{id: 'on', value: 'Да'}, {id: '', value: 'Нет'}]
                    }),
                    value: 'on',
                    name: 'ff_' + field.name,
                    id: fieldId
                });
            } else {
                ret = Ext.create("Ext.form.field.Checkbox", {
                    checked: (mode == 'edit' ? value == 1 : parseInt(field.default) == 1),
                    fieldLabel: fieldLabel,
                    labelAlign: 'left',
                    labelWidth: labelWidth,
                    name: 'ff_' + field.name,
                    hideLabel: (mode == 'groupEdit'),
                    hidden: !show,
                    id: fieldId,
                    readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                    allowBlank: (field.required == 0)
                });
                ret.on('change', this._processChangeValue, this);
            }
        } else if (field.type == 'pointer') {
            width = (width ? width : 600);
            if (pointerType == 'window') {
                ret = Ext.create('Ext.ux.index.form.SelectFromModel', {
                    model: field.rel_model,
                    hideLabel: (mode == 'groupEdit'),
                    fieldLabel: fieldLabel,
                    width: width,
                    hiddenName: 'ff_' + field.name,
                    name: 'ff_' + field.name,
                    labelAlign: 'top',
                    editable: false
                });
                if (mode == 'edit') {
                    ret.setValue(value, this._model.record["val_of_" + field.name]);
                } else if (field.default) {
                    ret.setValue(field.default, "")
                }
            } else {
                var url = '/admin/' + field.get_data_command + (field.get_data_command.search(/\?/) >= 0 ? '&' : '?') + 'ajax=1&answer_type=json&forSelect=1';
                url = this._processGettingDataUrl(url, field, record);
                ret = Ext.create("Ext.ux.form.ClearableComboBox", {
                    fieldLabel: fieldLabel,
                    labelAlign: 'top',
                    hideLabel: (mode == 'groupEdit'),
                    displayField: field.rel_model_identify_field,
                    valueField: 'id',
                    width: width,
                    editable: false,
                    store: new Ext.data.JsonStore({
                        proxy: {
                            type: 'ajax',
                            url: url,
                            actionMethods: {read: "POST"},
                            extraParams: {},
                            reader: {
                                type: 'json',
                                root: 'list',
                                idProperty: 'id'
                            }
                        },
                        fields: [{name: 'id', type: 'string'}, field.rel_model_identify_field],
                        pageSize: 50,
                        autoLoad: true,
                        listeners: {
                            load: {
                                fn: function (t) {
                                    if (mode == 'edit') {
                                        if (parseInt(value)) {
                                            ret.setValue(value + "");
                                        } else {
                                            ret.setValue("");
                                        }
                                    } else {
                                        if (parseInt(field.default)) {
                                            ret.setValue(field.default + "");
                                        } else {
                                            ret.setValue("");
                                        }
                                    }
                                }
                            }
                        }
                    }),
                    name: 'ff_' + field.name,
                    hidden: !show,
                    id: fieldId,
                    readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                    allowBlank: (field.required == 0)
                });
            }
            if (mode != 'groupEdit') ret.on('select', this._processChangeValue, this);
        } else if (field.type == 'select') {
            var data = [], key;

            if (field.select_options instanceof Array) {
                for (key = 0; key < field.select_options.length; key++) {
                    data[data.length] = {
                        id: key + "",
                        value: field.select_options[key]
                    }
                }
            } else {
                for (key in field.select_options) {
                    data[data.length] = {
                        id: key,
                        value: field.select_options[key]
                    }
                }
            }

            width = (width ? width : 600);
            ret = Ext.create("Ext.form.ComboBox", {
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                displayField: 'value',
                valueField: 'id',
                width: width,
                editable: false,
                store: Ext.create('Ext.data.Store', {
                    fields: ['id', 'value'],
                    data: data
                }),
                value: (mode == 'edit' ? value : field.default),
                name: 'ff_' + field.name,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0)
            });
            if (mode != 'groupEdit') ret.on('select', this._processChangeValue, this);
        } else if (field.type == 'key_val') {
            width = (width ? width : 600);
            height = (height ? height : 200);
            ret = Ext.create("Ext.form.field.TextArea", {
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                value: (mode == 'edit' ? value : field.default),
                width: width,
                height: height,
                name: 'ff_' + field.name,
                enableKeyEvents: true,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0)
            });
            //ret.on('keypress', this._processChangeValue, this);
        } else if (field.type == 'text') {
            width = (width ? width : 600);
            height = (height ? height : 200);
            ret = Ext.create("Ext.form.field.TextArea", {
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                value: (mode == 'edit' ? value : field.default),
                width: width,
                height: height,
                name: 'ff_' + field.name,
                enableKeyEvents: true,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0)
            });
            //ret.on('keypress', this._processChangeValue, this);
        } else if (field.type == 'html') {
            width = (width ? width : 600);
            height = (height ? height : 600);
            ret = Ext.create("Ext.ux.form.TinyMCETextArea", {
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                value: (mode == 'edit' ? value : field.default),
                width: width,
                height: height,
                name: 'ff_' + field.name,
                fieldStyle: 'font-family: Courier New; font-size: 12px;',
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
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
                    content_css: "/themes/frontend/css/style.css",
                    file_browser_callback: 'customFileBrowser',
                    resize: true
                }
            });
        } else if (field.type == 'url') {
            width = (width ? width : 600);
            ret = Ext.create("Ext.form.field.Text", {
                width: width,
                maxLength: maxLength,
                value: (mode == 'edit' ? value : field.default),
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                name: 'ff_' + field.name,
                enableKeyEvents: true,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0)
            });
        } else if (field.type == 'img') {
            width = (width ? width : 600);
            ret = Ext.create("Ext.ux.index.ImgSelectField", {
                width: width,
                value: (mode == 'edit' ? value : field.default),
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                name: 'ff_' + field.name,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0)
            });
        } else if (field.type == 'file') {
            width = (width ? width : 600);
            ret = Ext.create("Ext.ux.index.FileSelectField", {
                width: width,
                value: (mode == 'edit' ? value : field.default),
                fieldLabel: fieldLabel,
                labelAlign: 'top',
                hideLabel: (mode == 'groupEdit'),
                name: 'ff_' + field.name,
                hidden: !show,
                id: fieldId,
                readOnly: (mode == 'edit' ? (field.readonly == 1 || field.readonly == '1') : false),
                allowBlank: (field.required == 0)
            });
        } else {
            return null;
        }
        ret.niFieldIndex = fieldIndex;
        ret.niField = field;
        return ret;
    },

    enableTitleEdit: function (mode) {
        var el = Ext.get("ftc_" + this._id);
        var width = el.getWidth(),
            height = el.getHeight() + 8,
            title = (mode == 'edit' ? this._model.record[this._model.titleField] : 'Новая запись');
        el.setHTML("<input id='fftitle_" + this._id + "' class='x-form-text x-form-field ni-form-title' style='resize: none; width: 100%; height: " + height + "px;' value='" + title + "'/>");
    },

    buildForm: function (model, mode) {
        this._mode = mode;
        this._model = model;
        this._oldRecord = {};
        if (mode == 'edit') {
            if (typeof this._model.record != 'undefined' && this._model.record && typeof this._model.record.id != 'undefined') {
                this._oldRecord = Ext.clone(this._model.record);
                this._recordId = this._model.record.id;
            }
        } else {
            this._model.record = {};
            for (var i = 0; i < model.fields.length; i++) {
                switch (model.fields[i].type) {
                    case "string", "text", "url", "email", "html", "key_val", "select" :
                        this._model.record[model.fields[i].name] = "";
                        break;
                    case "int", "real", "bool", "pointer" :
                        this._model.record[model.fields[i].name] = 0;
                        break;
                }
            }
            this._recordId = 0;
        }
        if (this._editorPanel) {
            this._editorPanel.removeAll();
        } else {
            this._editorPanel = Ext.create('Ext.panel.Panel', {
                header: false,
                border: false,
                region: 'center',
                layout: 'border',
                bodyCls: 'next-index-panels-body',
                bodyStyle: 'padding: 0 5px 5px 5px'
            });
        }

        this._formPanel = Ext.create('Ext.form.Panel', {
            autoScroll: true,
            header: (this.formConfig.formType != undefined && this.formConfig.formType == 'tab' && mode != 'groupEdit'),
            title: (this.formConfig.formTitle != undefined && this.formConfig.formTitle ? this.formConfig.formTitle : ''),
            border: false,
            items: [
                Ext.create('Ext.form.field.Hidden', {
                    name: 'id',
                    id: this._id + "_id",
                    value: this._recordId
                })
            ]
        });

        window['niEditorForm' + this._id] = this;

        var title = 'Новая запись';

        if (mode && mode == 'edit') {
            title = model.record[model.titleField];
        }

        if (mode == 'groupEdit') {
            this._editorPanel.add({
                region: 'north',
                autoHeight: true,
                xtype: 'box',
                cls: 'ni-form-title-container',
                autoEl: {cn: '<div><b class="ni-form-title" >Изменение выбранных записей</b></div>'}
            });
        } else {
            this._editorPanel.add({
                region: 'north',
                autoHeight: true,
                xtype: 'box',
                cls: 'ni-form-title-container',
                autoEl: {cn: '<div id="ftc_' + this._id + '"><b class="ni-form-title" id="ftb_' + this._id + '">' + title + '</b>&nbsp;<a href="#" onclick="window[\'niEditorForm' + this._id + '\'].enableTitleEdit.call(window[\'niEditorForm' + this._id + '\'], \'' + mode + '\'); return false;" id="fta_' + this._id + '"></a></div>'}
            });
        }

        var tbar = Ext.create('Ext.toolbar.Toolbar', {
            height: 58,
            cls: this.formConfig.formType != undefined && this.formConfig.formType == 'tab' && mode != 'groupEdit' ? "ni-form-toolbar-tab-form" : "ni-form-toolbar",
            defaults: {
                scale: 'medium'
            },
            items: {
                text: 'Сохранить',
                scope: this,
                handler: function () {
                    this._save();
                }
            }
        });

        var items = [];

        if (this.formConfig.formType != undefined && this.formConfig.formType == 'tab' && mode != 'groupEdit') {
            this.tabPanel = Ext.create('Ext.tab.Panel', {
                items: [
                    this._formPanel
                ]
            });
            items = [
                this.tabPanel
            ];
            if (this.formConfig.tabs) {
                items[0].add(this.formConfig.tabs);
            }
        } else {
            items = [
                this._formPanel
            ];
        }

        this.panel = Ext.create('Ext.panel.Panel', {
            border: false,
            header: false,
            region: 'center',
            layout: 'fit',
            tbar: tbar,
            items: items
        });

        this._editorPanel.add(this.panel);

        this._formPanel.editorFormConfig = {
            model: model,
            mode: mode
        };

        var fieldsGroups = 0;
        var currentFieldsGroup = 0;
        for (i = 0; i < model.fields.length; i++) {
            if (model.fields[i].id_fields_group != currentFieldsGroup) {
                currentFieldsGroup = model.fields[i].id_fields_group;
                fieldsGroups++;
                if (fieldsGroups > 1) {
                    break;
                }
            }
        }

        fieldsGroups = (fieldsGroups > 1);

        currentFieldsGroup = null;
        var currentFieldsGroupTitle = '';
        for (i = 0; i < model.fields.length; i++) {
            if (mode == 'groupEdit') {
                if (parseInt(model.fields[i].allow_group_edit)) {
                    var formField = this._getFormField(model.fields[i], mode, i);

                    currentFieldsGroup = Ext.create('Ext.ux.index.FieldSet', {
                        title: model.fields[i].title + '&nbsp;&nbsp;&nbsp;',
                        checkboxToggle: true,
                        width: 600,
                        collapsed: true,
                        fldName: model.fields[i].name,
                        items: [
                            formField,
                            {
                                xtype: 'hiddenfield',
                                name: 'swf_' + model.fields[i].name,
                                id: 'swf_' + model.fields[i].name,
                                value: 0
                            }
                        ],
                        listeners: {
                            expand: function (f) {
                                Ext.getCmp('swf_' + f.fldName).setValue(1);
                            },
                            collapse: function (f) {
                                Ext.getCmp('swf_' + f.fldName).setValue(0);
                            }
                        }
                    });

                    this._formPanel.add(currentFieldsGroup);
                }
            } else if (!model.fields[i].addField && model.fields[i].name != model.titleField) {
                var formField = this._getFormField(model.fields[i], mode, i);
                if (formField) {
                    if (fieldsGroups) {
                        if (currentFieldsGroupTitle != model.fields[i].fields_group_title) {
                            currentFieldsGroup = Ext.create('Ext.ux.index.FieldSet', {
                                title: model.fields[i].fields_group_title,
                                collapsible: true,
                                width: 600,
                                collapsed: (currentFieldsGroupTitle != '')
                            });
                            this._formPanel.add(currentFieldsGroup)
                            currentFieldsGroupTitle = model.fields[i].fields_group_title;
                        }
                        currentFieldsGroup.add(formField);
                    } else {
                        this._formPanel.add(formField);
                    }
                }
            } else if (model.fields[i].name == model.titleField) {
                this._formPanel.add(Ext.create('Ext.form.field.Hidden', {
                    name: 'ff_' + model.fields[i].name,
                    id: this._id + '_' + model.fields[i].name,
                    value: (mode == 'edit' ? model.record[model.fields[i].name] : 'Новая запись')
                }))
            }
        }

        if (this.formConfig.items != undefined) {
            this._formPanel.add(this.formConfig.items);
        }
    },

    init: function (model, mode) {
        this._id = NextIndexApp.getApplication().generateId();
        if (this.module instanceof Ext.ux.index.editors.BaseEditor) {
            // Строим форму для редактирования модели mainModel
            if (model == undefined) {
                this.buildForm(this.module.mainModel, 'edit');
            } else {
                this.buildForm(model, mode == undefined ? 'edit' : mode);
            }
        }
    },

    _bindToRecord: function () {
        for (var i = 0; i < this._model.fields.length; i++) {
            var field = Ext.getCmp(this._id + "_" + this._model.fields[i].name);
            if (field) {
                if (this._model.fields[i].type == 'bool') {
                    this._model.record[this._model.fields[i].name] = field.getValue() ? 1 : 0; // Именно так, нам нужна ЕДИНИЦА!
                } else {
                    this._model.record[this._model.fields[i].name] = field.getValue();
                }
            }
        }
    },

    _save: function () {
        var form = this._formPanel.getForm();
        var el = Ext.get("fftitle_" + this._id);
        if (el) {
            Ext.getCmp(this._id + "_" + this._model.titleField).setValue(el.dom.value);
        }
        if (form.isValid()) {
            var url = this._model.saveCommand.replace('FORM_MODEL_ID', this._model.id);
            form.submit({
                url: '/admin/' + url + (url.search(/\?/) >= 0 ? '&' : '?') + 'ajax=1&answer_type=json&idModel=' + this._model.id,
                waitMsg: 'Сохранение...',
                success: function (fp, o) {
                    if (this._mode != 'groupEdit') {
                        if (!el) {
                            Ext.get('ftb_' + this._id).setHTML(Ext.getCmp(this._id + "_" + this._model.titleField).getValue());
                            if (this._recordId) {

                            } else {

                            }
                        }
                        this._bindToRecord();
                        if (this._recordId) {
                            this.module.onSave(this._recordId, this._model, this._oldRecord, this._model.record);
                        } else {
                            this._recordId = o.result.id;
                            Ext.getCmp(this._id + "_id").setValue(this._recordId);
                            this._model.record.id = this._recordId;
                            this.module.onAdd(this._recordId, this._model, this._model.record);
                        }
                        this._oldRecord = Ext.clone(this._model.record);
                    } else {
                        this.module.onGroupEdit()
                    }
                },
                scope: this
            });
        }
    },

    del: function (ids) {
        var url = this._model.deleteCommand.replace('FORM_MODEL_ID', this._model.id);
        if (ids) {
            var _ids = [];
            for (var i = 0; i < ids.length; i++) {
                _ids[_ids.length] = ids[i].get('id');
            }
        }

        Ext.Ajax.request({
            url: '/admin/' + url + (url.search(/\?/) >= 0 ? '&' : '?') + 'ajax=1&answer_type=json&idModel=' + this._model.id,
            params: {
                id: (ids ? _ids.join(',') : this._model.record.id),
                modelId: this._model.id
            },
            success: function (response) {
                var response = Ext.JSON.decode(response.responseText);
                if (!response.success) {
                    showErrorMessage(response.errorCode, response.errorMsg);
                } else {
                    if (!ids || (ids && ids.length == 1)) {
                        this.module.onDel(this._recordId, this._model, this._model.record);
                    } else {
                        this.module.onGroupDel(ids, this._model);
                    }
                }
            },
            scope: this
        });
    },

    getFieldValue: function (name) {
        var field = Ext.getCmp(this._id + "_" + name);
        if (!field) {
            if (this._model && typeof this._model.record != 'undefined') {
                return this._model.record[name];
            } else {
                showErrorMessage("0003", 'Не найдено поле формы "' + name + '"');
                return null;
            }
        }
        return field.getValue();
    },

    setFieldValue: function (name, value) {
        var field = Ext.getCmp(this._id + "_" + name);
        if (!field) {
            if (this._model && typeof this._model.record != 'undefined') {
                this._model.record[name] = value;
            } else {
                showErrorMessage("0003", 'Не найдено поле формы "' + name + '"');
                return null;
            }
        }
        field.setValue(value);
    },

    getParentFieldValue: function (name) {
        if (this.module.mainModel && this.module.mainModel.record && this.module.mainModel.record[name] != undefined) {
            return this.module.mainModel.record[name];
        }
    }
});
