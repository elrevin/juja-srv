Ext.define('Ext.ux.index.editors.SimpleEditor', {
    extend: 'Ext.ux.index.editors.BaseEditor',
    mainModel: null,
    titleField: 'title',
    additionTools: [],

    _addMenu: null,
    _removeMenu: null,

    editorForm: null,

    constructor: function (config) {
        this.callParent(arguments);

        this.mainModel._titleAc1Upcase = firstCharUpCase(this.mainModel.titleAc);
        this.mainModel._title1Upcase = firstCharUpCase(this.mainModel.title);
    },

    onSave: function (recordId, model, oldRecord, newRecord) {
        if (model.mainMenuNodeId) {
            var node = NextIndexApp.getApplication().treeView.getStore().getNodeById(model.mainMenuNodeId);
            if (node) {
                node = node.parentNode;
                NextIndexApp.getApplication().treeView.getStore().reload({
                    node: node,
                    callback: function () {
                        node.expand();
                    }
                });
            }
        }
        this.fireEvent('save', recordId, model, oldRecord, newRecord);
    },

    onDel: function (recordId, model, record) {
        if (model.mainMenuNodeId) {
            var node = NextIndexApp.getApplication().treeView.getStore().getNodeById(model.mainMenuNodeId);
            if (node) {
                node = node.parentNode;
                NextIndexApp.getApplication().treeView.getStore().reload({
                    node: node,
                    callback: function () {
                        node.expand();
                    }
                });
            }
        }
        this.fireEvent('del', recordId, model, record);
    },

    onAdd: function (recordId, model, newRecord) {
        this.fireEvent('del', recordId, model, newRecord);
    },

    init: function (id) {
        this.callParent(arguments);

        var params = {};
        params[this.parentIdParamName] = this.parentId;

        this.toolBar = Ext.create('Ext.toolbar.Toolbar', {
            defaults: {
                scale: 'medium'
            }
        });

        this.toolBar.add({
            xtype: 'button',
            text: 'Добавить ' + this.mainModel.titleAc,
            icon: "/themes/backend/images/buttons/plus.png",
            scope: this,
            handler: function () {
                this.editorForm.buildForm(this.mainModel, 'add');
            }
        });
        if (this.mainModel.canDelete) {
            this.toolBar.add('-', {
                xtype: 'button',
                text: '',
                icon: "/themes/backend/images/buttons/del.png",
                //style: 'background-position: center center',
                tooltip: "Удалить",
                handler: function () {
                    Ext.Msg.show({
                        title: 'Удалить ' + this.mainModel.title,
                        msg: 'Вы действильно хотите удалить ' + this.mainModel.title + ' "' + this.mainModel.record[this.mainModel.titleField] + '"?',
                        buttons: Ext.Msg.YESNO,
                        icon: Ext.Msg.WARNING,
                        fn: function (buttonId) {
                            if (buttonId == 'yes') {
                                this.editorForm.del();
                            }
                        },
                        scope: this
                    });
                },
                scope: this
            });
        }


        if (this.additionTools.length) {
            this.toolBar.add('-');
            this.toolBar.add(this.additionTools);
        }

        this.editorForm = Ext.create("Ext.ux.index.editors.EditorForm", {
            module: this,
            formConfig: this.getFormConfig != undefined ? this.getFormConfig() : {}
        });

        this.editorForm.init(this.mainModel);
        this.form = this.onCreateForm(this.editorForm._editorPanel);
        this.panel = Ext.create('Ext.panel.Panel', {
            layout: 'fit',
            border: false,
            header: false,
            tbar: this.toolBar,
            items: [
                this.form
            ]
        })
    },

    getMainModelRecordId: function () {
        if (this.mainModel.record != undefined && this.mainModel.record.id != undefined) {
            return this.mainModel.record.id;
        }
        return 0;
    },

    _afterMainMenuCommand: function () {
    }
});
