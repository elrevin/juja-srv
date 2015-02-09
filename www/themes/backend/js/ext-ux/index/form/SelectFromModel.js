Ext.define('Ext.ux.index.form.SelectFromModel', {
    extend: 'Ext.form.field.Trigger',
    alias: 'widget.selectfrommodel',
    trigger1Class: 'x-form-select-trigger',
    trigger2Class: 'x-form-clear-trigger',
    triggerWidth: 54,
    model: null,
    dialog: null,
    hiddenName: '',
    hiddenDataCls: Ext.baseCSSPrefix + 'hide-display ' + Ext.baseCSSPrefix + 'form-data-hidden',
    fieldSubTpl: [
        '<div class="{hiddenDataCls}" role="presentation" id="{id}-form-data-hidden"></div>',
        '<input id="{id}" type="{type}" {inputAttrTpl} class="{fieldCls} {typeCls} {editableCls}" autocomplete="off"',
        '<tpl if="value"> value="{[Ext.util.Format.htmlEncode(values.value)]}"</tpl>',
        '<tpl if="name"> name="{name}"</tpl>',
        '<tpl if="placeholder"> placeholder="{placeholder}"</tpl>',
        '<tpl if="size"> size="{size}"</tpl>',
        '<tpl if="maxLength !== undefined"> maxlength="{maxLength}"</tpl>',
        '<tpl if="readOnly"> readonly="readonly"</tpl>',
        '<tpl if="disabled"> disabled="disabled"</tpl>',
        '<tpl if="tabIdx"> tabIndex="{tabIdx}"</tpl>',
        '<tpl if="fieldStyle"> style="{fieldStyle}"</tpl>',
        '/>',
        {
            compiled: true,
            disableFormats: true
        }
    ],
    getSubTplData: function () {
        var me = this;
        Ext.applyIf(me.subTplData, {
            hiddenDataCls: me.hiddenDataCls
        });
        return me.callParent(arguments);
    },
    afterRender: function () {
        var me = this;
        me.callParent(arguments);
        me.setHiddenValue(me.value);
    },

    onTriggerClick: function () {
        var me = this;
        if (me.dialog) {
            me.dialog.select(function (rec) {
                me.setValue(rec.get('id'), rec.get(me.model.identifyFieldName));
                me.fireEvent('select', me, rec);
            });
        }
    },

    initComponent: function () {
        var me = this;

        NextIndexApp.getApplication().loadDialog("Ext.ux.index.dialogs.SingleList", {
            model: me.model
        }, function (dialog) {
            me.dialog = dialog;
        });

        Ext.applyIf(me.renderSelectors, {
            hiddenDataEl: '.' + me.hiddenDataCls.split(' ').join('.')
        });

        this.addEvents('select');
        this.callParent(arguments);
    },

    setHiddenValue: function (value) {
        var me = this,
            name = me.hiddenName,
            dom, childNodes, input;

        me.hiddenDataEl = Ext.get(me.id + "-inputEl-form-data-hidden");
        if (!me.hiddenDataEl || !name) {
            return;
        }
        dom = me.hiddenDataEl.dom;
        childNodes = dom.childNodes;
        if (childNodes.length) {
            childNodes[0].value = value;
        } else {
            me.hiddenDataEl.update(Ext.DomHelper.markup({
                tag: 'input',
                type: 'hidden',
                name: name
            }));
            input = dom.firstChild;
            input.value = value;
        }
    },

    setValue: function (value, title) {
        var me = this;
        me.value = value;
        me.setHiddenValue(me.value);
        me.setRawValue(title);
    },

    getValue: function () {
        var me = this,
            picker = me.picker,
            rawValue = me.getRawValue(),
            value = me.value;

        me.hiddenDataEl = Ext.get(me.id + "-inputEl-form-data-hidden");
        if (me.hiddenDataEl) {
            dom = me.hiddenDataEl.dom;
            childNodes = dom.childNodes;
            if (childNodes.length) {
                value = childNodes[0].value;
            }
        }
        return value;
    },

    getSubmitValue: function () {
        var value = this.getValue();
        // If the value is null/undefined, we still return an empty string. If we
        // don't, the field will never get posted to the server since nulls are ignored.
        if (Ext.isEmpty(value)) {
            value = '';
        }
        return value;
    },
    reset: function () {
        var me = this;

        me.beforeReset();
        me.setValue(0, '');
        me.clearInvalid();
        delete me.wasValid;
    },
    onRender: function (ct, position) {
        Ext.ux.index.form.SelectFromModel.superclass.onRender.call(this, ct, position);
        var id = this.getId();
        this.triggerConfig = {
            tag: 'div', cls: 'x-form-twin-triggers', style: 'display:block;width:54px;', cn: [
                {
                    tag: "img",
                    style: Ext.isIE ? 'margin-left:-3;height:19px' : '',
                    src: Ext.BLANK_IMAGE_URL,
                    id: "trigger1" + id,
                    name: "trigger1" + id,
                    cls: "x-form-trigger " + this.trigger1Class
                },
                {
                    tag: "div",
                    style: 'height: 19px; width: 1px; margin: 0 3px 0 3px; background-color: #d9d9d9; display: inline-block'
                },
                {
                    tag: "img",
                    style: Ext.isIE ? 'margin-left:-6;height:19px' : '',
                    src: Ext.BLANK_IMAGE_URL,
                    id: "trigger2" + id,
                    name: "trigger2" + id,
                    cls: "x-form-trigger " + this.trigger2Class
                }
            ]
        };
        this.triggerEl.replaceWith(this.triggerConfig);
        this.triggerEl.on('mouseup', function (e) {

                if (e.target.name == "trigger1" + id) {
                    this.onTriggerClick();
                } else if (e.target.name == "trigger2" + id) {
                    this.reset();
                }
            },
            this);
        var trigger1 = Ext.get("trigger1" + id);
        var trigger2 = Ext.get("trigger2" + id);
        trigger1.addClsOnOver('x-form-trigger-over');
        trigger2.addClsOnOver('x-form-trigger-over');
    }
});