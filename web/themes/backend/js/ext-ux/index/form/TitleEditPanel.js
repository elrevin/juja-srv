Ext.define('Ext.ux.index.form.TitleEditPanel', {
    extend: 'Ext.Panel',
    alias: ['widget.uxtitleeditpanel'],
    field: null,
    textPanel: null,
    form: null,

    getValueByText: function () {
        var me = this,
            formField;

        if (!me.field) return '';

        formField = Ext.getCmp(me.form.id + '_field_' + me.field.name);
        if (me.field.type == Ext.data.Types.STRING) {
            return formField.getValue();
        } else if (me.field.type == Ext.data.Types.INTEGER) {
            return formField.getValue() + '';
        } else if (me.field.type == Ext.data.Types.FLOAT) {
            return formField.getValue() + '';
        } else if (me.field.type == Ext.data.Types.DATE) {
            return Ext.Date.format(formField.getValue(), "d.m.Y");
        } else if (me.field.type == Ext.data.Types.DATETIME) {
            return Ext.Date.format(formField.getValue(), "d.m.Y H:i:s");
        } else if (me.field.type == Ext.data.Types.POINTER) {
            return '';
        }
    },

    renew: function () {
        var me = this;

        me.setTitleText('Добавить '+me.form.model.accusativeRecordTitle);
    },

    initComponent: function () {
        var me = this,
            formField,
            html;
        me.bodyCls = 'in2-form-title-container';
        me.layout = "fit";
        me.height = 25;

        html = "<b id='in2-title-edit-text-" + me.id + "'>" + 'Добавить '+me.form.model.accusativeRecordTitle + "</b>&nbsp;&nbsp;";

        me.textPanel = Ext.create("Ext.Panel", {
            border: false,
            header: false,
            hidden: false,
            html: html
        });

        me.items = [
            me.textPanel
        ];

        me.form.on('afterload', function (form, record) {
            Ext.get('in2-title-edit-text-' + me.id).dom.innerHTML = 'Изменить '+me.form.model.accusativeRecordTitle;
        });

        me.callParent();
    },

    setTitleText: function (text) {
        var me = this;
        Ext.get('in2-title-edit-text-' + me.id).dom.innerHTML = text;
    }
});