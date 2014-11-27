Ext.define('Ext.ux.form.field.ModalSelect', {
    extend: 'Ext.form.field.Trigger',
    alias: 'widget.uxmodalselect',
    trigger1Class: 'x-form-select-trigger',
    trigger2Class: 'x-form-clear-trigger',
    triggerWidth: 54,
    modelName: '',
    runAction: [],
    hiddenName: '',
    originValue: null,
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
    modelField: null,
    getSubTplData: function(){
        var me = this;
        Ext.applyIf(me.subTplData, {
            hiddenDataCls: me.hiddenDataCls
        });
        return me.callParent(arguments);
    },

    onTriggerClick: function () {
        var me = this;

        IndexNextApp.getApplication().loadModule({
            runAction: me.runAction,
            listeners: {
                select: function (record) {
                    me.setValue({
                        id: record.get('id'),
                        value: record.get(me.modelField.relativeModel.identifyFieldName)
                    });
                }
            },
            modal: true,
            modelName: me.modelName
        });
    },

    setValue: function (value) {
        var me = this;
        me.originValue = value;
        if (value && value.value != undefined) {
            return me.callParent([value.value]);
        }
        return me.callParent([value]);
    },

    getValue: function() {
        var me = this;
        return me.originValue;
    },

    onRender: function(ct, position){
        var me = this,
            id = this.getId();

        me.callParent(arguments);
        me.triggerConfig = {
            tag:'div', cls:'x-form-twin-triggers', style:'display:block;width:54px;', cn:[
                {tag: "img", style: Ext.isIE?'margin-left:-3;height:19px':'', src: Ext.BLANK_IMAGE_URL, id:"trigger1" + id, name:"trigger1" + id, cls: "x-form-trigger " + this.trigger1Class},
                {tag: "div", style: 'height: 19px; width: 1px; margin: 0 3px 0 3px; background-color: #d9d9d9; display: inline-block'},
                {tag: "img", style: Ext.isIE?'margin-left:-6;height:19px':'', src: Ext.BLANK_IMAGE_URL, id:"trigger2" + id, name:"trigger2" + id, cls: "x-form-trigger " + this.trigger2Class}
            ]};
        me.triggerEl.replaceWith(this.triggerConfig);
        me.triggerEl.on('mouseup',function(e){
                if(e.target.name == "trigger1" + id ){
                    me.onTriggerClick();
                } else if(e.target.name == "trigger2" + id){
                    me.reset();
                }
            });
        var trigger1 = Ext.get("trigger1" + id);
        var trigger2 = Ext.get("trigger2" + id);
        trigger1.addClsOnOver('x-form-trigger-over');
        trigger2.addClsOnOver('x-form-trigger-over');
    }
});