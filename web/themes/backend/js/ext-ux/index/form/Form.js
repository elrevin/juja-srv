Ext.define('Ext.ux.index.form.Form', {
    extend: 'Ext.form.Panel',
    alias: ['widget.uxindexform'],

    model: null,

    userRights: 0,

    identifyField: null,
    titlePanel: null,
    editorPanel: null,
    tabPanel: null,
    topToolbar: null,
    bottomToolbar: null,
    recordTitle: '',
    accusativeRecordTitle: '',
    tabs: [],
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
                    currentContainer = me.editorPanel;
                }

                currentField = me._getField(field);
                if (currentField) {
                    currentContainer.add(currentField);
                }
            }

            if (groups.length > 1) {
                for (i = 0; i < groups.length; i++) {
                    if (groups[i].obj) {
                        me.editorPanel.add(groups[i].obj);
                    }
                }
            }
        }
    },

    createTopToolbar: function () {
        var me = this;

        if (me.userRights > 1) {
            me.topToolbar = Ext.create('Ext.toolbar.Toolbar', {
                height: 58,
                cls: (me.tabs.length ? 'in2-editor-form-toolbar-tab-form' : 'in2-editor-form-toolbar'),
                defaults: {
                    scale: 'medium'
                },
                items: [
                    {
                        text: 'Сохранить',
                        handler: function () {
                            me.save();
                        }
                    }
                ]
            });
        }
        return me.topToolbar;
    },

    createBottomToolbar: function () {
        var me = this;

        if (me.userRights > 1) {
            me.bottomToolbar = Ext.create('Ext.toolbar.Toolbar', {
                height: 58,
                cls: 'in2-editor-form-bottom-toolbar',
                defaults: {
                    scale: 'medium'
                },
                items: [
                    {
                        text: 'Сохранить',
                        handler: function () {
                            me.save();
                        }
                    }, '->', {
                        text: 'Сохранить и добавить'
                    }
                ]
            });
        }
        return me.bottomToolbar;
    },

    initComponent: function () {
        var me = this,
            editorPanelConfig,
            modelFieldsCount,
            tab,
            tabClassName,
            createTabNow;

        me.addEvents('beforeload', 'afterload', 'beforeupdate', 'afterupdate', 'beforeinsert', 'afterinsert');
        me.bodyCls = 'in2-editor-form';
        me.layout = 'border';

        if (me.model && (modelFieldsCount = me.model.fields.getCount())) {
            for (i = 0; i < modelFieldsCount; i++) {
                field = me.model.fields.getAt(i);
                if (field.identify) {
                    me.identifyField = field;
                    break;
                }
            }
        }
        editorPanelConfig = {
            layout: 'anchor',
            region: 'center',
            border: false,
            header: false,
            autoScroll: true
        };

        if (!me.tabs.length) {
            editorPanelConfig['tbar'] = me.createTopToolbar();
            editorPanelConfig['bbar'] = me.createBottomToolbar();
        }

        me.editorPanel = Ext.create('Ext.Panel', editorPanelConfig);

        me.titlePanel = Ext.create('Ext.ux.index.form.TitleEditPanel', {
            region: 'north',
            form: me,
            field: me.identifyField
        });

        me.items = [
            me.titlePanel
        ];

        if (me.tabs.length) {
            me.tabPanel = Ext.create('Ext.tab.Panel', {
                activeTab: 0,
                border: false,
                items: [
                    {
                        title: 'Основные свойства',
                        border: false,
                        header: false,
                        items: [
                            me.editorPanel
                        ]
                    }
                ]
            });

            me.items[me.items.length] = Ext.create('Ext.Panel', {
                region: 'center',
                items: [me.tabPanel],
                tbar: me.createTopToolbar(),
                bbar: me.createBottomToolbar(),
                layout: 'fit'
            });

            for (var i = 0; i < me.tabs.length; i++) {
                tabClassName = 'Ext.ux.index.tab.DetailPanel';
                createTabNow = false;
                if (me.tabs[i].createInterfaceForExistingParentOnly == undefined || !me.tabs[i].createInterfaceForExistingParentOnly) {
                    createTabNow = true;
                    delete me.tabs[i].createInterfaceForExistingParentOnly;
                }

                if (createTabNow) {
                    if (me.tabs[i].className != undefined && me.tabs[i].className) {
                        tabClassName = me.tabs[i].className;
                        delete me.tabs[i].className;
                    }
                    me.tabs[i]['form'] = me;
                    tab = Ext.create(tabClassName, me.tabs[i]);
                    me.tabs[i]['object'] = tab;
                    me.tabPanel.add(tab);
                }
            }
        } else {
            me.items[me.items.length] = me.editorPanel;
        }

        me.callParent();
        me.createFields();
    },

    loadRecord: function (record) {
        var me = this,
          tabClassName,
          tab;

        me.mode = 'update';

        if (record == undefined || !record) {
            record = me.model;
        } else {
            me.model = record;
        }

        me.fireEvent('beforeload', me, record);
        me.callParent([record]);

        // Создаем табы, которые помечены флагом createInterfaceForExistingParentOnly

        for (var i = 0; i < me.tabs.length; i++) {
            if (me.tabs[i].createInterfaceForExistingParentOnly != undefined || me.tabs[i].createInterfaceForExistingParentOnly) {
                tabClassName = 'Ext.ux.index.tab.DetailPanel';
                delete me.tabs[i].createInterfaceForExistingParentOnly;
                if (me.tabs[i].className != undefined && me.tabs[i].className) {
                    tabClassName = me.tabs[i].className;
                    delete me.tabs[i].className;
                }
                me.tabs[i]['form'] = me;
                tab = Ext.create(tabClassName, me.tabs[i]);
                me.tabs[i]['object'] = tab;
                me.tabPanel.add(tab);
            }
        }

        me.fireEvent('afterload', me, record);
    },

    save: function () {
        var me = this,
            modelFieldsCount,
            field,
            input,
            values = {};

        if (me.isValid()) {
            me.fireEvent((me.mode == 'update' ? 'beforeupdate' : 'beforeinsert'), me);

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

            me.fireEvent((me.mode == 'update' ? 'afterupdate' : 'afterinsert'), me);

            me.mode = (me.mode == 'insert' ? 'update' : 'update');
        }
    }

});