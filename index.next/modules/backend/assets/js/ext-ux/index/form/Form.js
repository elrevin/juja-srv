Ext.define('Ext.ux.index.form.Form', {
    extend: 'Ext.ux.index.form.SimpleForm',
    alias: ['widget.uxindexform'],

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
    extraFields: {},
    parentRecordCombo: null,

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

                if (field.extra) {
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
            for (i =0; i < modelFieldsCount; i++) {
                field = me.model.fields.get(i);
                if (field.extra) {
                    continue;
                }
                me._showConditionAnalytic(field, Ext.getCmp(me.id + '_field_' + field.name));
                me._showFilterAnalytic(field, Ext.getCmp(me.id + '_field_' + field.name));
            }
        }
    },

    getIdentifyField: function (model) {
        var i, count = model.fields.getCount(), field;

        for (i = 0; i < count; i++) {
            field = model.fields.getAt(i);
            if (field.identify) {
                return field;
            }
        }
        return null;
    },

    createParentCombo: function (toolbarItems) {
        var me = this,
            comboType = null,
            parentModel,
            parentModelIdentifyField,
            parentModelName,
            modelField;

        if (me.model.recursive) {
            comboType = 'tree';
            parentModel = me.model;
            parentModelIdentifyField = me.identifyField;
            parentModelName = me.modelClassName.replace('ModelClass', '');
            modelField = me.extraFields.parent_id;
        } else if (me.model.parentModelName) {
            parentModel = Ext.create(me.model.parentModelName+'ModelClass');
            parentModelIdentifyField = me.getIdentifyField(parentModel);
            parentModelName = me.model.parentModelName;
            modelField = me.extraFields.master_table_id;
            if (parentModel.recursive) {
                comboType = 'tree';
            } else {
                comboType = 'list';
            }
        }

        if (comboType == 'tree') {
            me.parentRecordCombo = Ext.create('Ext.ux.form.field.TreeCombo', {
                store: Ext.create('Ext.data.TreeStore', {
                    autoLoad: false,
                    fields: [{name: 'id', type: 'int'}, {name: parentModelIdentifyField.name, type: 'string'}],
                    proxy: {
                        type: 'ajax',
                        url: $url(parentModel.getDataAction[0], parentModel.getDataAction[1], parentModel.getDataAction[2], {modelName: parentModelName, identifyOnly: 1, all: 1}),
                        actionMethods: {read: "POST"},
                        reader: {
                            type: 'json',
                            root: 'data'
                        }
                    }
                }),
                rootVisible: false,
                width: 200,
                treeWidth: 350,
                displayField: parentModelIdentifyField.name,
                id: me.id + '_field_'+modelField.name,
                name: modelField.name,
                modelField: modelField
            });
        } else if (comboType == 'list') {
            me.parentRecordCombo = Ext.create('Ext.ux.form.field.PointerComboBox', {
                store: new Ext.data.JsonStore ({
                    autoLoad: true,
                    fields: [{name: 'id', type: 'int'}, {name: parentModelIdentifyField.name, type: 'string'}],
                    proxy: {
                        type: 'ajax',
                        url: $url(parentModel.getDataAction[0], parentModel.getDataAction[1], parentModel.getDataAction[2], {modelName: parentModelName, identifyOnly: 1}),
                        actionMethods: {read: "POST"},
                        reader: {
                            type: 'json',
                            root: 'data'
                        }
                    }
                }),
                width: 200,
                displayField: parentModelIdentifyField.name,
                valueField: 'id',
                id: me.id + '_field_'+modelField.name,
                name: modelField.name,
                modelField: modelField,
                isPointerField: true
            });
        }
        if (me.parentRecordCombo) {
            toolbarItems[toolbarItems.length] = '|';
            toolbarItems[toolbarItems.length] = 'Поместить в:';
            toolbarItems[toolbarItems.length] = me.parentRecordCombo;
        }
    },

    createTopToolbar: function () {
        var me = this,
          toolbarItems;

        if (me.userRights > 1) {
            toolbarItems = [
                {
                    text: 'Сохранить',
                    handler: function () {
                        me.save();
                    }
                }
            ];
            me.createParentCombo(toolbarItems);
            me.topToolbar = Ext.create('Ext.toolbar.Toolbar', {
                height: 58,
                cls: (me.tabs.length ? 'in2-editor-form-toolbar-tab-form' : 'in2-editor-form-toolbar'),
                defaults: {
                    scale: 'medium'
                },
                items: toolbarItems
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
                    }/*, '->', {
                        text: 'Сохранить и добавить'
                    }*/
                ]
            });
        }
        return me.bottomToolbar;
    },

    beforeInitComponent: function () {
        var me = this,
            editorPanelConfig,
            modelFieldsCount,
            tab,
            tabClassName;

        me.addEvents('beforeload', 'afterload', 'beforeupdate', 'afterupdate', 'beforeinsert', 'afterinsert');
        me.bodyCls = 'in2-editor-form';
        me.layout = 'border';

        if (!me.model) {
            me.model = Ext.create(me.modelClassName, {});
        }

        if (me.model && (modelFieldsCount = me.model.fields.getCount())) {
            for (i = 0; i < modelFieldsCount; i++) {
                field = me.model.fields.getAt(i);
                if (field.extra) {
                    me.extraFields[field.name] = field;
                }
                if (field.identify) {
                    me.identifyField = field;
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
                        layout: 'fit',
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
            for (i = 0; i < me.tabs.length; i++) {
                if (!me.tabs[i].createInterfaceForExistingParentOnly) {
                    if (me.tabs[i].className) {
                        tabClassName = me.tabs[i].className;
                    } else {
                        if (me.tabs[i].masterModelRelationsType == 'master_detail') {
                            tabClassName = 'Ext.ux.index.tab.DetailPanel'
                        } else if (me.tabs[i].masterModelRelationsType == 'many_to_many') {
                            tabClassName = 'Ext.ux.index.tab.Many2ManyPanel'
                        }
                    }
                    me.tabs[i]['parentForm'] = me;
                    tab = Ext.create(tabClassName, me.tabs[i]);
                    me.tabs[i]['object'] = tab;
                    me.tabPanel.add(tab);
                }
            }
        } else {
            me.items[me.items.length] = me.editorPanel;
        }
    },

    renew: function () {
        var me = this,
            i, tabClassName, createTabNow;
        me.mode = 'insert';

        // Удаление табов
        if (me.tabs.length) {
            for (i = 0; i < me.tabs.length; i++) {
                if (me.tabs[i]['object']) {
                    me.tabPanel.remove(me.tabs[i]['object']);
                    delete me.tabs[i]['object'];
                }
            }
        }

        // Создание табов.
        for (i = 0; i < me.tabs.length; i++) {
            if (!me.tabs[i].createInterfaceForExistingParentOnly) {
                if (me.tabs[i].className) {
                    tabClassName = me.tabs[i].className;
                } else {
                    if (me.tabs[i].masterModelRelationsType == 'master_detail') {
                        tabClassName = 'Ext.ux.index.tab.DetailPanel'
                    } else if (me.tabs[i].masterModelRelationsType == 'many_to_many') {
                        tabClassName = 'Ext.ux.index.tab.Many2ManyPanel'
                    }
                }
                me.tabs[i]['parentForm'] = me;
                tab = Ext.create(tabClassName, me.tabs[i]);
                me.tabs[i]['object'] = tab;
                me.tabPanel.add(tab);
            }
        }

        me.titlePanel.renew();

        me.model = Ext.create(me.modelClassName, {});
        me.getForm().reset();
    },

    copy: function (record) {
        var me = this,
            i, tabClassName, createTabNow;

        me.mode = 'insert';
        // Удаление табов
        if (me.tabs.length) {
            for (i = 0; i < me.tabs.length; i++) {
                if (me.tabs[i]['object']) {
                    me.tabPanel.remove(me.tabs[i]['object']);
                    delete me.tabs[i]['object'];
                }
            }
        }

        // Создание табов.
        for (i = 0; i < me.tabs.length; i++) {
            if (!me.tabs[i].createInterfaceForExistingParentOnly) {
                if (me.tabs[i].className) {
                    tabClassName = me.tabs[i].className;
                } else {
                    if (me.tabs[i].masterModelRelationsType == 'master_detail') {
                        tabClassName = 'Ext.ux.index.tab.DetailPanel'
                    } else if (me.tabs[i].masterModelRelationsType == 'many_to_many') {
                        tabClassName = 'Ext.ux.index.tab.Many2ManyPanel'
                    }
                }
                me.tabs[i]['parentForm'] = me;
                tab = Ext.create(tabClassName, me.tabs[i]);
                me.tabs[i]['object'] = tab;
                me.tabPanel.add(tab);
            }
        }

        me.model = Ext.create(me.modelClassName, {});

        for (i = 0; i < record.fields.getCount(); i++) {
            if (record.fields.getAt(i).name == 'id') {
                me.model.set('id', 0);
            } else {
                me.model.set(record.fields.getAt(i).name, record.get(record.fields.getAt(i).name));
            }
        }

        me.getForm().loadRecord(me.model);

        me.mode = 'insert';
    },

    afterLoad: function (record) {
        var me = this,
            tabClassName,
            tab, i;

        for (i = 0; i < me.tabs.length; i++) {
            if ((me.tabs[i].createInterfaceForExistingParentOnly != undefined || me.tabs[i].createInterfaceForExistingParentOnly) && !me.tabs[i].object) {
                if (me.tabs[i].className) {
                    tabClassName = me.tabs[i].className;
                } else {
                    if (me.tabs[i].masterModelRelationsType == 'master_detail') {
                        tabClassName = 'Ext.ux.index.tab.DetailPanel'
                    } else if (me.tabs[i].masterModelRelationsType == 'many_to_many') {
                        tabClassName = 'Ext.ux.index.tab.Many2ManyPanel'
                    }
                }
                me.tabs[i]['parentForm'] = me;
                tab = Ext.create(tabClassName, me.tabs[i]);
                me.tabs[i]['object'] = tab;
                me.tabPanel.add(tab);
            }
        }

        if (me.model.recursive) {
            me.parentRecordCombo.store.getProxy().setExtraParam('colFilter', Ext.JSON.encode([{
                type: "numeric",
                comparison: "noteq",
                value: me.model.get('id'),
                field: 'id'
            }]));
            me.parentRecordCombo.store.load();
        }
        me.callParent([record]);
    }
});