Ext.define('Ext.ux.form.field.PointerComboBox', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.clearablecombo',
    spObj: '',
    spForm: '',
    spExtraParam: '',
    isPointerField: false,
    afterLoadSetValue: null,
    setValue: function(value, doSelect) {
        var me = this;
        if (value) {
            if (typeof value == 'object') {
                if (value && value.id != undefined) {
                    value = value.id;
                }
            }
            if (me.store.isLoading() || !me.store.wasLoaded) {
                me.afterLoadSetValue = value;
                return me;
            }

            if (/^[0-9]+$/.test(value+'')) {
                value = parseInt(value);
            }
            return me.callParent([value, doSelect]);
        }
    },
    getValue: function(getObj) {
        var me = this,
          picker = me.picker,
          rawValue = me.getRawValue(),
          value = me.value;
        if (me.getDisplayValue() !== rawValue) {
            value = rawValue;
            me.value = me.displayTplData = me.valueModels = null;
            if (picker) {
                me.ignoreSelection++;
                picker.getSelectionModel().deselectAll();
                me.ignoreSelection--;
            }
        } else if (me.isPointerField){
            return {
                id: value,
                value: rawValue
            };
        }

        return value;
    },
    initComponent: function () {
        var me = this;
        me.store.wasLoaded = false;
        me.store.on('load', function () {
            me.store.wasLoaded = true;
            if (me.afterLoadSetValue) {
                me.setValue(me.afterLoadSetValue);
            }
        });
        return me.callParent(arguments);
    }
});
