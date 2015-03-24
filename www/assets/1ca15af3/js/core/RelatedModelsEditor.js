Ext.define('App.core.RelatedModelsEditor', {
    extend: 'App.core.Module',

    data: null,
    mainRecord: null,
    mainRecordId: null,

    formPanel: null,
    mainForm: null,
    childForm: null,
    tabs: null,
    grid: null,

    mainModelLoader: null,
    childModelLoader: null,

    createToolbar: function () {
        var me = this,
            buttons = [],
            mnuItems = [];

        if (me.userRights > 1 || me.childModelConfig.userRights > 1) {
            if (me.userRights > 1) {
                mnuItems[mnuItems.length] = {
                    text: me.accusativeRecordTitle,
                    handler: function () {
                        me.addMainModelRecord();
                    }
                };
            }

            if (me.childModelConfig.userRights > 1) {
                mnuItems[mnuItems.length] = {
                    text: me.childModelConfig.accusativeRecordTitle,
                    disabled: (me.data ? false : true),
                    handler: function () {
                        me.addChildModelRecord();
                    }
                };
            }

            buttons[buttons.length] = {
                xtype: 'button',
                text: 'Добавить',
                icon: $assetUrl('/images/buttons/plus.png'),
                scope: this,
                itemId: 'add',
                menu: Ext.create('Ext.menu.Menu', {
                    items: mnuItems
                })
            };

            buttons[buttons.length] = { xtype: 'tbspacer' };

            if (me.userRights > 2 || me.childModelConfig.userRights > 1) {
                mnuItems = [];
                if (me.userRights > 2) {
                    mnuItems[mnuItems.length] = {
                        text: me.accusativeRecordTitle,
                        handler: function () {
                            me.delMainModelRecord();
                        }
                    };
                }

                if (me.childModelConfig.userRights > 1) {
                    mnuItems[mnuItems.length] = {
                        text: me.childModelConfig.accusativeRecordTitle,
                        disabled: true,
                        handler: function () {
                            me.delChildModelRecord();
                        }
                    };
                }

                buttons[buttons.length] = {
                    xtype: 'button',
                    icon: $assetUrl('/images/buttons/del.png'),
                    scope: this,
                    itemId: 'del',
                    disabled: (me.data ? false : true),
                    menu: Ext.create('Ext.menu.Menu', {
                        items: mnuItems
                    })
                };
            }

            mnuItems = [];
            if (me.userRights > 1) {
                mnuItems[mnuItems.length] = {
                    text: me.accusativeRecordTitle,
                    disabled: (me.data ? false : true),
                    handler: function () {
                        me.copyMainModelRecord();
                    }
                };
            }

            if (me.childModelConfig.userRights > 1) {
                mnuItems[mnuItems.length] = {
                    text: me.childModelConfig.accusativeRecordTitle,
                    disabled: true,
                    handler: function () {
                        me.copyChildModelRecord();
                    }
                };
            }

            buttons[buttons.length] = {
                itemId: 'copy',
                disabled: (me.data ? false : true),
                icon: $assetUrl('/images/buttons/copy.png'),
                menu: Ext.create('Ext.menu.Menu', {
                    items: mnuItems
                })
            };

            me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
                height: 58,
                style: "background: #f0f0f0",
                defaults: {
                    scale: 'medium'
                },
                items: buttons
            });
        }

        return me.toolbar;
    },

    createMainForm: function () {
        var me = this;

        if (!me.tabs) {
            me.tabs = [];
        }
        me.mainForm = Ext.create('Ext.ux.index.form.Form', {
            modelClassName: me.mainModelLoader.modelClassName,
            tabs: me.tabs,
            listeners: {
                afterinsert: function (form, record) {
                    record.save({
                        callback: function (newRecord) {
                            if (me.recursive) {

                            } else {
                                if (me.currentMainMenuNode.get('recordId')) {
                                    // Выбрана запись, нужно перезагрузить предка
                                    IndexNextApp.getApplication().refreshMainMenuNode(
                                        me.currentMainMenuNode.parentNode,
                                        function () {
                                            var node = IndexNextApp.getApplication().getMainMenuNode(me.modelName, newRecord.get('id'));
                                            IndexNextApp.getApplication().selectMainMenuNode(node);
                                        }
                                    );

                                }
                            }
                        }
                    });
                },

                afterupdate: function (form, record) {
                    record.save({
                        callback: function () {
                            var new_parent_id = record.get('parent_id'),
                                old_parent_id = me.data.parent_id,
                                node;
                            // меняем текст текущего узла главного меню

                            // Проверяем был ли изменен предок
                            if (new_parent_id != old_parent_id) {
                                // Нужно обновить соответствующих предков

                                node = IndexNextApp.getApplication().getMainMenuNode(me.modelName, new_parent_id.id);
                                node.appendChild(me.currentMainMenuNode);
                                node.expand();
                            }

                            me.currentMainMenuNode.set('title', record.get(me.mainForm.identifyField.name));
                        }
                    });
                }
            },
            userRights: me.userRights
        });
        me.createToolbar();
    },

    createChildForm: function () {
        var me = this;

        if (!me.childModelConfig.tabs) {
            me.childModelConfig.tabs = [];
        }
        me.childForm = Ext.create('Ext.ux.index.form.Form', {
            modelClassName: me.childModelLoader.modelClassName,
            tabs: me.childModelConfig.tabs,
            listeners: {
                afterinsert: function (form, record) {
                    me.childModelLoader.store.add(record);
                    me.childModelLoader.store.sync({
                        failure: function () {
                            me.childModelLoader.store.reload();
                            // Возвращяем режим записи
                            me.childForm.mode = 'insert';
                        }
                    });
                },
                afterupdate: function (form, record, beforeModel) {
                    me.childModelLoader.store.sync({
                        failure: function () {
                            me.childModelLoader.store.reload();
                        },
                        callback: function () {
                            if (record.get('master_table_id').id != beforeModel.get('master_table_id').id) {
                                node = IndexNextApp.getApplication().getMainMenuNode(me.modelName, record.get('master_table_id').id);
                                // Выбираем новый узел предка, выбираем его "громко" (параметр silence = false), но при этом перезагружается
                                // форма и это ой как хреново
                                // todo me нужно продумать: как сделать так чтобы сразу выбирался нужный пункт в таблице
                                IndexNextApp.getApplication().selectMainMenuNode(node, false);
                            }
                        }
                    });
                }
            },
            userRights: me.childModelConfig.userRights
        });
        //me.createToolbar();
    },

    selectionChange: function (selected) {
        var me = this;

        if (selected.length == 1) {
            if (me.currentMainMenuNode && me.currentMainMenuNode.get('recordId')) {
                me.childForm.getInputFieldByName('master_table_id').store.getProxy().setExtraParam('colFilter');
                me.childForm.getInputFieldByName('master_table_id').store.load();
            }
            me.childForm.loadRecord(selected[0]);
            me.formPanel.getLayout().setActiveItem(1);

            if (me.userRights > 1) {
//                me.toolbar.getComponent('copy').setDisabled(false);
            }
        } else {
            if (me.userRights > 1) {
//                me.toolbar.getComponent('copy').setDisabled(true);
            }
        }

        if (selected.length) {
            if (me.childModelLoader.userRights > 2) {
                me.toolbar.getComponent('del').menu.items.getAt(1).setDisabled(false);
            }
        } else {
            if (me.childModelLoader.userRights > 2) {
                me.toolbar.getComponent('del').menu.items.getAt(1).setDisabled(true);
            }
        }
    },

    createListGrid: function () {
        var me = this,
            gridConfig;

        if (me.childModelConfig.userRights > 0) {
            gridConfig = {
                modelClassName: me.childModelLoader.modelClassName,
                getDataAction: me.childModelLoader.getDataAction,
                saveAction: me.childModelLoader.saveAction,
                deleteAction: me.childModelLoader.deleteAction,
                store: me.childModelLoader.store,
                sortable: me.childModelLoader.sortable,
                selModel: Ext.create('Ext.selection.CheckboxModel', {
                    //mode: "MULTI"
                }),
                listeners: {
                    selectionchange: function (grid, selected, eOpts) {
                        me.selectionChange(selected);
                    }
                }
            };

            if (me.createToolbar()) {
                gridConfig['tbar'] = me.toolbar;
            }

            me.grid = Ext.create('Ext.ux.index.grid.ListGrid', gridConfig);
        }
    },

    addMainModelRecord: function () {
        var me = this;
        me.formPanel.getLayout().setActiveItem(0);
        me.data = null;
        me.mainForm.renew();
        if (me.currentMainMenuNode && me.currentMainMenuNode.get('recordId') && me.recursive) {
            me.mainForm.getInputFieldByName('parent_id').store.getProxy().setExtraParam('colFilter');
            me.mainForm.getInputFieldByName('parent_id').store.load();
            me.mainForm.getInputFieldByName('parent_id').setValue(me.currentMainMenuNode.get('recordId'));
        }
    },

    delMainModelRecord: function () {
        var me = this;

        if (me.data) {
            Ext.Msg.show({
                title: 'Удаление записи',
                msg: 'Вы действительно хотите удалить '+me.accusativeRecordTitle.toLocaleLowerCase(),
                width: 300,
                buttons: Ext.Msg.YESNO,
                icon: Ext.window.MessageBox.QUESTION,
                fn: function (button) {
                    if (button == 'yes') {
                        Ext.Ajax.request({
                            url: $url(me.mainModelLoader.deleteAction[0], me.mainModelLoader.deleteAction[1], me.mainModelLoader.deleteAction[2], {modelName: me.mainModelLoader.modelClassName.replace('ModelClass', '')}),
                            params: {
                                data: Ext.JSON.encode({
                                    id: me.record.get('id')
                                })
                            },
                            success: function (response) {
                                var data = Ext.JSON.decode(response.responseText);
                                if (data.success) {
                                    me.currentMainMenuNode = me.currentMainMenuNode.parentNode;
                                    IndexNextApp.getApplication().refreshMainMenuNode(me.currentMainMenuNode);
                                    IndexNextApp.getApplication().selectMainMenuNode(me.currentMainMenuNode);
                                }
                            }
                        });
                    }
                }
            });
        }
    },

    addChildModelRecord: function () {
        var me = this;
        me.formPanel.getLayout().setActiveItem(1);
        me.childForm.renew();
        if (me.currentMainMenuNode && me.currentMainMenuNode.get('recordId')) {
            me.childForm.getInputFieldByName('master_table_id').store.getProxy().setExtraParam('colFilter');
            me.childForm.getInputFieldByName('master_table_id').store.load();
            me.childForm.getInputFieldByName('master_table_id').setValue(me.currentMainMenuNode.get('recordId'));
        }
    },

    delChildModelRecord: function () {
        var me = this,
            selectModel = me.grid.getSelectionModel(),
            selections,
            i, count;

        count = selectModel.getCount();

        if (count) {
            Ext.Msg.show({
                title: 'Удаление записи',
                msg: 'Вы действительно хотите удалить '+me.childModelLoader.accusativeRecordTitle.toLocaleLowerCase(),
                width: 300,
                buttons: Ext.Msg.YESNO,
                icon: Ext.window.MessageBox.QUESTION,
                fn: function (button) {
                    if (button == 'yes') {
                        selections = selectModel.getSelection();
                        me.childModelLoader.store.remove(selections);
                        me.childModelLoader.store.sync({
                            failure: function () {
                                me.childModelLoader.store.reload();
                            }
                        });
                    }
                }
            });
        }
    },

    init: function () {
        var me = this;

        me.mainModelLoader = Ext.create('Ext.ux.index.mixins.ModelLoaderWithStore', {
            modelTitle: me.modelTitle,
            modelName: me.modelName,
            modelClassName: me.modelClassName,
            fields: me.fields,
            getDataAction: me.getDataAction,
            sortable: me.sortable,
            store: me.store,
            userRights: me.userRights,
            recordTitle: me.recordTitle,
            accusativeRecordTitle: me.accusativeRecordTitle,
            parentModelName: me.parentModelName,
            recursive: me.recursive
        });

        me.childModelLoader = Ext.create('Ext.ux.index.mixins.ModelLoaderWithStore', {
            modelTitle: me.childModelConfig.modelTitle,
            modelName: me.childModelConfig.modelName,
            modelClassName: me.childModelConfig.modelClassName,
            fields: me.childModelConfig.fields,
            getDataAction: me.childModelConfig.getDataAction,
            sortable: me.childModelConfig.sortable,
            store: me.childModelConfig.store,
            userRights: me.childModelConfig.userRights,
            recordTitle: me.childModelConfig.recordTitle,
            accusativeRecordTitle: me.childModelConfig.accusativeRecordTitle,
            parentModelName: me.childModelConfig.parentModelName,
            recursive: me.childModelConfig.recursive
        });

        if (me.userRights > 0) {
            if (me.getDataAction.length) {
                me.mainModelLoader.createActions();
                me.mainModelLoader.createModelClass(true);

                me.childModelLoader.createActions();
                me.childModelLoader.createModelClass();

                if (me.mainModelLoader.modelClassName) {

                    me.record = Ext.create(me.mainModelLoader.modelClassName, {});

                    me.createMainForm();
                    me.createChildForm();

                    me.mainForm.on('afterlayout', function () {
                        if (me.data) {
                            me.mainForm.loadRecord(me.record);
                        }
                    });

                    me.childModelLoader.createStore();

                    if (me.data) {
                        me.record.set(me.data);
                        me.childModelLoader.store.getProxy().setExtraParam('masterId', me.data.id);
                    }

                    me.childModelLoader.store.on('update', function (store, record) {
                        me.childForm.loadRecord(record);
                    });

                    me.createListGrid();

                    me.formPanel = Ext.create("Ext.Panel", {
                        layout: 'card',
                        region: 'center',
                        activeItem: 0,
                        items: [
                            me.mainForm,
                            me.childForm
                        ]
                    });

                    me._mainPanel = Ext.create('Ext.Panel', {
                        layout: 'border',
                        items: [
                            me.grid,
                            me.formPanel
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
        } else {
            me.fireEvent('initfail');
        }
    }
});