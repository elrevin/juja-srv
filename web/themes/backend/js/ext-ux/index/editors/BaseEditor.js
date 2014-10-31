Ext.define('Ext.ux.index.editors.BaseEditor', {
  extend: 'Classes.CModule',
  parentId: 0,
  action: '',
  parentIdParamName: 'parentId',
  dataSource: null,
  dataPanel: null,
  createForm: null,
  form: null,
  editorForm: null,

  constructor: function (config) {
    this.callParent(arguments);
    this.addEvents(
      'save',
      'add',
      'del'
    );
  },

  _afterMainMenuCommand: function (records) {},

  message: function (message, args) {
  },

  onSave: function (recordId, model, oldRecord, newRecord) {},
  onDel: function (recordId, model, newRecord) {},
  onAdd: function (recordId, model, newRecord) {},
  onGroupDel: function (items, model) {},
  onGroupEdit: function () {},
  onCreateForm: function (form) {return form;}
});