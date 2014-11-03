Ext.define('Ext.ux.index.form.TitleEditPanel', {
    extend: 'Ext.Panel',
    alias: ['widget.uxtitleeditpanel'],
    field: null,
    fieldPanel: null,
    textPanel: null,
    defaultText: 'Новая запись',
    form: null,

    createField: function () {
        var me = this,
            formField = null,
            form = me.form;
        if (me.field.type == Ext.data.Types.STRING) {
            formField = Ext.create('Ext.form.field.Text', {
                hideLabel: true,
                width: 300,
                value: 'Новая запись',
                id: form.id + '_field_' + field.name,
                name: me.field.name
            });
        } else if (me.field.type == Ext.data.Types.INTEGER) {
            formField = Ext.create('Ext.form.field.Number', {
                hideLabel: true,
                allowDecimals: false,
                name: me.field.name,
                id: form.id + '_field_' + field.name,
                width: 150,
                allowBlank: false,
                value: 0
            });
        } else if (me.field.type == Ext.data.Types.FLOAT) {
            formField = Ext.create('Ext.form.field.Number', {
                hideLabel: true,
                allowDecimals: true,
                name: me.field.name,
                id: form.id + '_field_' + field.name,
                width: 150,
                allowBlank: false,
                value: 0
            });
        } else if (me.field.type == Ext.data.Types.DATE) {
            formField = Ext.create('Ext.form.field.Date', {
                hideLabel: true,
                name: me.field.name,
                id: form.id + '_field_' + field.name,
                width: 110,
                allowBlank: false,
                value: (new Date()),
                format: 'd.m.Y',
                submitFormat: 'Y-m-d'
            });
        } else if (me.field.type == Ext.data.Types.DATETIME) {
            formField = Ext.create('Ext.ux.form.DateTimeField', {
                hideLabel: true,
                name: me.field.name,
                id: form.id + '_field_' + field.name,
                width: 110,
                allowBlank: false,
                value: (new Date()),
                format: 'd.m.Y',
                submitFormat: 'Y-m-d H:i:s'
            });
        } else if (me.field.type == Ext.data.Types.POINTER) {
            //formField = Ext.create('Ext.ux.form.DateTimeField', {
            //  hideLabel: true,
            //  name: me.field.name,
            //  id: me.id+'_field_'+field.name,
            //  width: 110,
            //  allowBlank: false,
            //  value: (new Date()),
            //  format: 'd.m.Y',
            //  submitFormat: 'Y-m-d H:i:s'
            //});
        }

        return formField;
    },

    getValueByText: function () {
        var me = this,
            formField;

        if (!me.field) return me.defaultText;

        formField = me.fieldPanel.items.getAt(0);
        if (me.field.type == Ext.data.Types.STRING) {
            return formField.getValue();
        } else if (me.field.type == Ext.data.Types.INTEGER) {
            return formField.getValue() + '';
        } else if (me.field.type == Ext.data.Types.FLOAT) {
            return formField.getValue() + '';
        } else if (me.field.type == Ext.data.Types.DATE) {
            return formField.getValue();
        } else if (me.field.type == Ext.data.Types.DATETIME) {
            return formField.getValue();
        } else if (me.field.type == Ext.data.Types.POINTER) {
            return '';
        }
    },

    initComponent: function () {
        var me = this,
            formField,
            html;
        me.bodyCls = 'in2-form-title-container';
        me.layout = "card";
        me.height = 62;

        if (me.field) {
            me.fieldPanel = Ext.create("Ext.Panel", {
                border: false,
                header: false,
                items: [
                    me.createField()
                ]
            });
        }

        html = "<b id='in2-title-edit-text-" + me.id + "'>" + me.getValueByText() + "</b>&nbsp;&nbsp;";

        if (me.field) {
            html += "<a href='#' id='in2-title-edit-button-" + me.id + "'></a>";
        }

        me.textPanel = Ext.create("Ext.Panel", {
            border: false,
            header: false,
            hidden: false,
            html: html
        });

        me.items = [
            me.textPanel,
            me.fieldPanel
        ];

        me.activeItem = 0;

        me.form.on('afterload', function (form, record) {
            Ext.get('in2-title-edit-text-' + me.id).dom.innerHTML = me.getValueByText();
        });

        me.callParent();
    },

    showEditor: function () {
        var me = this;

        me.getLayout().setActiveItem(1);
    },

    afterRender: function (container, position) {
        var me = this;

        if (me.field) {
            Ext.get('in2-title-edit-button-' + me.id).dom.onclick = function () {
                me.showEditor();

                return false;
            };
        }
        me.callParent(arguments);
    }
});