Ext.define('Ext.ux.index.editors.SingleListEditor', {
    extend: 'Ext.ux.index.editors.BaseEditor',
    mainModel: null,
    titleField: 'title',
    additionTools: [],

    _addMenu: null,
    _removeMenu: null,
    _recId: 0,

    constructor: function (config) {
        this.callParent(arguments);
        this.mainModel._titleAc1Upcase = firstCharUpCase(this.mainModel.titleAc);
        this.mainModel._title1Upcase = firstCharUpCase(this.mainModel.title);
        this.addEvents(
            'recordSelect'
        );
    },

    onSave: function (recordId, model, oldRecord, newRecord) {
        this.dataPanel.getStore().reload();
        this.fireEvent('save', recordId, model, oldRecord, newRecord);
    },

    onDel: function (recordId, model, record) {
        this.dataPanel.getStore().reload();
        this.dataPanel.getSelectionModel().deselectAll();
        this.fireEvent('del', recordId, model, record);
    },

    onGroupDel: function (items, model) {
        this.dataPanel.getStore().reload();
        this.dataPanel.getSelectionModel().deselectAll();
        this.editorForm.buildForm(this.mainModel, 'add');
    },

    onAdd: function (recordId, model, newRecord) {
        this.dataPanel.getStore().reload();
        this.fireEvent('add', recordId, model, newRecord);
    },

    onGroupEdit: function () {
        this.dataPanel.getStore().reload();
        this.dataPanel.getSelectionModel().deselectAll();
    },

    init: function (id) {
        this.callParent(arguments);

        this.toolBar = Ext.create('Ext.toolbar.Toolbar', {
            height: 58,
            style: "background: #f0f0f0",
            defaults: {
                scale: 'medium'
            },
            items: [
                {
                    xtype: 'button',
                    text: 'Добавить',
                    icon: "/themes/backend/images/buttons/plus.png",
                    scope: this,
                    handler: function () {
                        this.mainModel.record = {};
                        this.editorForm.formConfig = this.mainModel.getFormConfig != undefined ? this.mainModel.getFormConfig() : {};
                        this.editorForm.buildForm(this.mainModel, 'add');
                    }
                }
            ]
        });


        if (this.additionTools.length) {
            this.toolBar.add('-');
            this.toolBar.add(this.additionTools);
        }

        var dataPanelConfig = {
            model: this.mainModel,
            parentIdParamName: this.parentIdParamName,
            parentRecordId: this.parentId,
            stateId: this.action.replace('/', '_'),
            listeners: {
                selectionchange: {
                    fn: function (g, selected) {
                        if (selected.length == 1) {
                            this._mainToolBarChange(true);
                            this._gridSelect(selected[0]);
                        } else if (selected.length) {
                            var ids = [];
                            for (var i = 0; i < selected.length; i++) {
                                ids[ids.length] = selected[i].get('id');
                            }

                            this.mainModel.record = {};
                            this.editorForm.formConfig = this.mainModel.getFormConfig != undefined ? this.mainModel.getFormConfig() : {};
                            if (!this.editorForm.formConfig.items) this.editorForm.formConfig.items = [];
                            this.editorForm.formConfig.items[this.editorForm.formConfig.items.length] = {
                                xtype: 'hiddenfield',
                                name: 'groupEdit',
                                value: ids.join(",")
                            }
                            this.editorForm.buildForm(this.mainModel, 'groupEdit');
                            this._mainToolBarChange(true);
                        } else {
                            this.mainModel.record = {};
                            this.editorForm.formConfig = this.mainModel.getFormConfig != undefined ? this.mainModel.getFormConfig() : {};
                            this.editorForm.buildForm(this.mainModel, 'add');
                            this._mainToolBarChange(false);
                        }
                    },
                    scope: this
                }
            },
            tbar: this.toolBar
        };


        this.dataPanel = Ext.create('Ext.ux.index.editors.ListGrid', dataPanelConfig);

        this.editorForm = Ext.create("Ext.ux.index.editors.EditorForm", {
            module: this,
            formConfig: this.mainModel.getFormConfig != undefined ? this.mainModel.getFormConfig() : {}
        });
        this.editorForm.init(this.mainModel, 'add');
        this.form = this.onCreateForm(this.editorForm._editorPanel);
        this.panel = Ext.create('Ext.panel.Panel', {
            layout: 'border',
            border: false,
            header: false,
            items: [
                this.dataPanel,
                this.form
            ]
        })
    },

    _mainToolBarChange: function (select) {
        var menuItemsCount = this.toolBar.items.getCount();
        if (select) {
            if (this.mainModel.canDelete) {
                if (menuItemsCount == 1) {
                    this.toolBar.add('-', {
                        xtype: 'button',
                        icon: "/themes/backend/images/buttons/del.png",
                        tooltip: "Удалить",
                        scope: this,
                        handler: function () {
                            var ids = this.dataPanel.getSelectionModel().getSelection();
                            var msg = 'Вы действительно хотите удалить выбранные записи?';
                            var title = 'Удаление записей';
                            if (ids.length == 1) {
                                msg = 'Вы действильно хотите удалить ' + this.mainModel.title + ' "' + this.mainModel.record[this.mainModel.titleField] + '"?';
                                title = 'Удалить ' + this.mainModel.title;
                            }
                            Ext.Msg.show({
                                title: title,
                                msg: msg,
                                buttons: Ext.Msg.YESNO,
                                icon: Ext.Msg.WARNING,
                                fn: function (buttonId) {
                                    if (buttonId == 'yes') {
                                        this.editorForm.del(ids);
                                    }
                                },
                                scope: this
                            });
                        }
                    });
                } else {
                    this.toolBar.items.getAt(1).show();
                    this.toolBar.items.getAt(2).show();
                }
            }
        } else {
            if (menuItemsCount == 3) {
                this.toolBar.items.getAt(1).hide();
                this.toolBar.items.getAt(2).hide();
            }
        }
    },

    _gridSelect: function (rec) {
        this.mainModel.record = rec.raw;
        this.editorForm.formConfig = this.mainModel.getFormConfig != undefined ? this.mainModel.getFormConfig() : {};
        this.editorForm.buildForm(this.mainModel, 'edit');
        this.fireEvent('recordselect', rec);
    },

    getMainModelRecordId: function () {
        if (this.mainModel.record != undefined && this.mainModel.record.id != undefined) {
            return this.mainModel.record.id;
        }
        return 0;
    },

    _afterMainMenuCommand: function () {
        if (this._removeMenu) {
            this._removeMenu.remove(0);
        }
    },

    onCreateForm: function (form) {
        if (this.createForm) {
            return this.createForm.call(this, form);
        } else {
            return form;
        }
    }
});