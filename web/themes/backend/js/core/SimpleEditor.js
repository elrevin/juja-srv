Ext.define('App.core.SimpleEditor', {
    extend: 'App.core.Module',
    _form: null,
    modelName: '',
    _modelClassName: '',
    _formModel: null,
    fields: [],

    createModelClass: function () {
        var me = this,
          fieldConf;
        if (me.fields.length) {
            me._modelClassName = this.modelName + "Model";
            var modelClassDefinition = {
                extend: 'Ext.data.Model',
                fields: []
            };
            for (var i = 0; i < me.fields.length; i++) {
                fieldConf = {
                    name: me.fields[i].name,
                    type: me.fields[i].type,
                    title: me.fields[i].title,
                    group: me.fields[i].group,
                    identify: me.fields[i].identify,
                    settings: me.fields[i].settings
                };

                if (fieldConf.type == 'pointer' && me.fields[i].relativeModel != undefined && relativeModel.name != undefined && relativeModel.moduleName != undefined) {
                    fieldConf['relativeModel'] = me.fields[i].relativeModel;
                }

                modelClassDefinition.fields[modelClassDefinition.fields.length] = fieldConf;
            }
            Ext.define(me._modelClassName, modelClassDefinition);
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