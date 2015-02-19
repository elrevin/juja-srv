Ext.define('App.core.SimpleEditor', {
    extend: 'App.core.Module',
    mixins: {
        modelLoader: 'Ext.ux.index.mixins.ModelLoaderWithStore'
    },

    data: null,
    record: null,
    recordId: null,

    form: null,

    toolbar: null,

    tabs: [],

    addRecord: function () {
        var me = this;
        me.data = null;
        me.form.renew();
        if (me.currentMainMenuNode && me.currentMainMenuNode.get('recordId') && me.recursive) {
            me.form.getInputFieldByName('parent_id').store.getProxy().setExtraParam('colFilter');
            me.form.getInputFieldByName('parent_id').store.load();
            me.form.getInputFieldByName('parent_id').setValue(me.currentMainMenuNode.get('recordId'));
        }
    },

    copyRecord: function () {
        var me = this,
            selectModel = me.grid.getSelectionModel(),
            selections;

        if (selectModel.getCount()) {
            selections = selectModel.getSelection();
            me.form.copy(selections[0]);
        }
    },

    deleteRecord: function () {
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
                            url: $url(me.deleteAction[0], me.deleteAction[1], me.deleteAction[2], {modelName: me.modelClassName.replace('ModelClass', '')}),
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

    createToolbar: function () {
        var me = this,
          buttons = [],
            i;

        if (me.userRights > 1) {
            buttons[buttons.length] = {
                xtype: 'button',
                text: 'Добавить',
                icon: $assetUrl('/images/buttons/plus.png'),
                scope: this,
                itemId: 'add',
                handler: function () {
                    me.addRecord();
                }
            };
            buttons[buttons.length] = { xtype: 'tbspacer' };

            if (me.userRights > 2) {
                buttons[buttons.length] = {
                    xtype: 'button',
                    icon: $assetUrl('/images/buttons/del.png'),
                    scope: this,
                    itemId: 'del',
                    handler: function () {
                        me.deleteRecord();
                    }
                };
            }
        }
        for (i = buttons.length -1; i >= 0; i--) {
            me.form.topToolbar.insert(0, buttons[i]);
        }
    },

    createForm: function () {
        var me = this;

        me.form = Ext.create('Ext.ux.index.form.Form', {
            modelClassName: me.modelClassName,
            tabs: me.tabs,
            listeners: {
                afterinsert: function (form, record) {
                    record.save({
                        callback: function () {
                            IndexNextApp.getApplication().refreshMainMenuNode(me.currentMainMenuNode);
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

                            me.currentMainMenuNode.set('title', record.get(me.form.identifyField.name));
                        }
                    });
                }
            },
            userRights: me.userRights
        });
        me.createToolbar();
    },

    init: function () {
        var me = this;
        if (me.userRights > 0) {
            if (me.getDataAction.length) {
                me.createActions();
                me.createModelClass(true);

                if (me.modelClassName) {

                    me.record = Ext.create(me.modelClassName, {});

                    me.createForm();

                    if (me.data) {
                        me.record.set(me.data);
                    }

                    me.form.on('afterlayout', function () {
                        if (me.data) {
                            me.form.loadRecord(me.record);
                        }
                    });

                    me._mainPanel = Ext.create('Ext.Panel', {
                        layout: 'fit',
                        items: [
                            {
                                xtype: 'panel',
                                layout: 'fit',
                                region: 'center',
                                items: [
                                    me.form
                                ]
                            }
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