Ext.define('App.core.SimpleEditor', {
  extend: 'App.core.Module',
  _form: null,
  modelName: '',
  _modelClassName: '',
  _formModel: null,
  fields: [],

  createModelClass: function () {
    if (this.fields.length) {
      this._modelClassName = this.modelName+"Model";
      var modelClassDefinition = {
        extend: 'Ext.data.Model',
        fields: []
      };
      for (var i = 0; i < this.fields.length; i++) {
        modelClassDefinition.fields[modelClassDefinition.fields.length] = {
          name: this.fields[i].name,
          type: this.fields[i].type,
          title: this.fields[i].title,
          group: this.fields[i].group,
          identify: this.fields[i].identify
        };
      }
      Ext.define(this._modelClassName, modelClassDefinition);
    }
  },

  init: function () {
    this.createModelClass();
    if (this._modelClassName) {
      this._formModel = Ext.create(this._modelClassName, {});
      this._form = Ext.create('Ext.ux.index.form.Form', {
        model: this._formModel
      });
      this._mainPanel = this._form;
      this.callParent();
    } else {
      this.fireEvent('initfail');
    }
  }
});