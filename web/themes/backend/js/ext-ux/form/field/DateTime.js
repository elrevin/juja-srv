Ext.define('Ext.ux.form.field.DateTime', {
    extend: 'Ext.form.FieldContainer',
    mixins: {
        field: 'Ext.form.field.Field'
    },
    alias: 'widget.datetimefield',
    layout: 'hbox',
    width: 200,
    combineErrors: true,
    msgTarget: 'side',
    submitFormat: 'c',

    dateCfg: null,
    timeCfg: null,

    allowBlank: true,

    initComponent: function () {
        var me = this;
        if (!me.dateCfg) me.dateCfg = {};
        if (!me.timeCfg) me.timeCfg = {};
        me.buildField();
        me.callParent();
        me.dateField = me.down('datefield')
        me.timeField = me.down('timefield')

        me.initField();
    },

    //@private
    buildField: function () {
        var me = this;
        me.items = [
            Ext.apply({
                xtype: 'datefield',
                submitValue: false,
                format: 'd.m.Y',
                width: 100,
                flex: 2,
                allowBlank: me.allowBlank,
                disabled: me.disabled
            }, me.dateCfg),
            Ext.apply({
                xtype: 'timefield',
                submitValue: false,
                format: 'H:i',
                width: 80,
                flex: 1,
                allowBlank: me.allowBlank,
                disabled: me.disabled
            }, me.timeCfg)]
    },

    getValue: function () {
        var me = this,
            value,
            date = me.dateField.getSubmitValue(),
            dateFormat = me.dateField.format,
            time = me.timeField.getSubmitValue(),
            timeFormat = me.timeField.format;
        if (date) {
            if (time) {
                value = Ext.Date.parse(date + ' ' + time, me.getFormat());
            } else {
                value = me.dateField.getValue();
            }
        }
        return value;
    },

    setValue: function (value) {
        var me = this;
        me.dateField.setValue(value);
        me.timeField.setValue(value);
    },

    getSubmitData: function () {
        var me = this,
            data = null;
        if (!me.disabled && me.submitValue && !me.isFileUpload()) {
            data = {},
                value = me.getValue(),
                data[me.getName()] = '' + value ? Ext.Date.format(value, me.submitFormat) : null;
        }
        return data;
    },

    getFormat: function () {
        var me = this;
        return (me.dateField.submitFormat || me.dateField.format) + " " + (me.timeField.submitFormat || me.timeField.format)
    },
    isValid: function () {
        var me = this,
            ret = true;
        if (!me.dateField.isValid()) {
            me.setActiveErrors(me.dateField.getErrors());
            ret = false;
        }
        if (!me.timeField.isValid()) {
            me.setActiveErrors(me.timeField.getErrors());
            ret = false;
        }
        return ret;
    }
});