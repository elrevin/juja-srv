Ext.define('Ext.ux.index.editors.RelatedListsEditor', {
  extend: 'Ext.ux.index.editors.BaseEditor',
  mainModel: null,
  childModel: null,
  titleField: 'title',
  additionTools: [],

  _addMenu: null,
  _removeMenu: null,

  constructor: function (config) {
    this.callParent(arguments);

    this.childModel._titleAc1Upcase = firstCharUpCase(this.childModel.titleAc);
    this.childModel._title1Upcase = firstCharUpCase(this.childModel.title);

    this.mainModel._titleAc1Upcase = firstCharUpCase(this.mainModel.titleAc);
    this.mainModel._title1Upcase = firstCharUpCase(this.mainModel.title);
    this.addEvents(
      'recordSelect'
    );
  },

  onSave: function (recordId, model, oldRecord, newRecord) {
    if (model == this.childModel) {
      this.dataPanel.getStore().reload();
    } else {
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
    }
    this.fireEvent('save', recordId, model, oldRecord, newRecord);
  },

  onDel: function (recordId, model, record) {
    if (model == this.childModel) {
      this.dataPanel.getStore().reload();
    } else {
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
    }
    if (this.childModel.record) {
      this.childModel.record = null;
      this._removeMenu.items.removeAt(0);
      this.editorForm.buildForm(this.childModel, 'add');
    }
    this.fireEvent('del', recordId, model, record);
  },

  onGroupDel: function (items, model) {
    if (model == this.childModel) {
      this.dataPanel.getStore().reload();
    }
  },

  onGroupEdit: function () {
    this.dataPanel.getStore().reload();
    this.dataPanel.getSelectionModel().deselectAll();
  },

  onAdd: function (recordId, model, newRecord) {
    if (model == this.childModel) {
      this.dataPanel.getStore().reload();
    } else {
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
    }
    this.fireEvent('add', recordId, model, newRecord);
  },

  init: function (id) {
    this.callParent(arguments);

    var params = {};
    params[this.parentIdParamName] = this.parentId;

    this.toolBar = Ext.create('Ext.toolbar.Toolbar', {
      height: 58,
      style: "background: #f0f0f0",
      defaults: {
        scale: 'medium'
      }
    });

    this._addMenu = Ext.create("Ext.menu.Menu", {
      items: [
        {
          text: this.mainModel._titleAc1Upcase,
          scope: this,
          handler: function () {
            this.editorForm.buildForm(this.mainModel, 'add');
          }
        }, {
          text: this.childModel._titleAc1Upcase,
          scope: this,
          handler: function () {
            this.editorForm.buildForm(this.childModel, 'add');
          }
        }
      ]
    });
    this.toolBar.add({
      xtype: 'button',
      text: 'Добавить',
      icon: "/themes/backend/images/buttons/plus.png",
      tooltip: "Добавить",
      menu: this._addMenu
    });
    if (this.mainModel.canDelete && this.childModel.canDelete) {
      this._removeMenu = Ext.create("Ext.menu.Menu", {
        items: [
          {
            text: this.mainModel._titleAc1Upcase+' "'+this.mainModel.record[this.mainModel.titleField]+'"',
            scope: this,
            handler: function() {
              Ext.Msg.show({
                title:'Удалить '+this.mainModel.title,
                msg: 'Вы действильно хотите удалить '+this.mainModel.title+' "'+this.mainModel.record[this.mainModel.titleField]+'"?',
                buttons: Ext.Msg.YESNO,
                icon: Ext.Msg.WARNING,
                fn: function (buttonId) {
                  if (buttonId == 'yes') {
                    this.editorForm.del();
                  }
                },
                scope: this
              });
            }
          }
        ]
      });
      this.toolBar.add('-',{
        xtype: 'button',
        text: '',
        icon: "/themes/backend/images/buttons/del.png",
        tooltip: "Удалить",
        menu: this._removeMenu
      });
    }
    if (this.additionTools.length) {
      this.toolBar.add('-');
      this.toolBar.add(this.additionTools);
    }

    var dataPanelConfig = {
      model: this.childModel,
      parentIdParamName: this.parentIdParamName,
      parentRecordId: this.parentId,
      stateId: this.action.replace('/', '_'),
      listeners: {
        selectionchange: {
          fn: function (g, selected) {
            if (selected.length == 1) {
              this._gridSelect(selected[0]);
            } else if (selected.length) {
              var ids = [];
              for (var i = 0; i < selected.length; i++) {
                ids[ids.length] = selected[i].get('id');
              }

              this.childModel.record = {};
              this.editorForm.formConfig = this.childModel.getFormConfig != undefined ? this.childModel.getFormConfig() : {};
              if (!this.editorForm.formConfig.items) this.editorForm.formConfig.items = [];
              this.editorForm.formConfig.items[this.editorForm.formConfig.items.length] = {
                xtype: 'hiddenfield',
                name: 'groupEdit',
                value: ids.join(",")
              }
              this._removeMenu.items.getAt(0).setText("Удалить выбранные записи");
              this.editorForm.buildForm(this.childModel, 'groupEdit');
            } else {
              this.childModel.record = {};
              this.editorForm.formConfig = this.childModel.getFormConfig != undefined ? this.childModel.getFormConfig() : {};
              this.editorForm.buildForm(this.childModel, 'add');
              this._removeMenu.items.removeAt(0);
            }
          },
          scope: this
        }
      },
      tbar: this.toolBar
    };

    this.dataPanel = Ext.create('Ext.ux.index.editors.ListGrid',dataPanelConfig);

    this.editorForm = Ext.create("Ext.ux.index.editors.EditorForm", {
      module: this,
      formConfig: this.mainModel.getFormConfig != undefined ? this.mainModel.getFormConfig() : {}
    });
    this.editorForm.init(this.mainModel, 'edit');
    this.form = this.onCreateForm(this.editorForm._editorPanel);
    this.panel = Ext.create('Ext.panel.Panel', {
      layout: 'border',
      border: false,
      header: false,
      items: [
        this.dataPanel,
        this.form
      ]
    });
  },

  _gridSelect: function (rec) {
    this.childModel.record = rec.raw;
    this.editorForm.formConfig = this.childModel.getFormConfig != undefined ? this.childModel.getFormConfig() : {};
    this.editorForm.buildForm(this.childModel, 'edit');
    var menuItemsCount = this._removeMenu.items.getCount();
    if (menuItemsCount == 1) {
      this._removeMenu.insert(0, {
        text: this.childModel._titleAc1Upcase + ' "'+rec.get(this.childModel.titleField)+'"',
        scope: this,
        handler: function() {
          var ids = this.dataPanel.getSelectionModel().getSelection();
          var msg = 'Вы действительно хотите удалить выбранные записи?';
          var title = 'Удаление записей';
          if (ids.length == 1) {
            msg = 'Вы действильно хотите удалить '+this.childModel.title+' "'+this.childModel.record[this.childModel.titleField]+'"?';
            title = 'Удалить '+this.childModel.title;
          }
          Ext.Msg.show({
            title:title,
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
    } else if (menuItemsCount == 2){
      this._removeMenu.items.getAt(0).setText(this.childModel._titleAc1Upcase  + ' "'+rec.get(this.childModel.titleField)+'"');
    }
    this.fireEvent('recordselect', rec);
  },

  getMainModelRecordId: function () {
    if (this.mainModel.record) {
      return this.mainModel.record.id;
    }
    return 0;
  },

  getChildModelRecordId: function () {
    if (this.childModel.record) {
      return this.childModel.record.id;
    }
    return 0;
  },

  _afterMainMenuCommand: function () {
    this._removeMenu.remove(0);
  },

  onCreateForm: function (form) {
    if (this.createForm) {
      return this.createForm.call(this, form);
    } else {
      return form;
    }
  }
});