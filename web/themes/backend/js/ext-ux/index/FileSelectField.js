Ext.define('Ext.ux.index.FileSelectField', {
    extend: 'Ext.form.field.Base',
    alias: 'widget.fileselectfield',
    selButtonText: "Файл на сервере",
    loadButtonText: "Загрузить с компьютера",
    _selWindow: null,
    fieldSubTpl: [ // note: {id} here is really {inputId}, but {cmpId} is available
        '<div class="ni-img-field" style="width: {width}px;">',
        '<input id="{id}" type="{type}" {inputAttrTpl}',
        '<tpl if="name"> name="{name}"</tpl>',
        '<tpl if="value"> value="{[Ext.util.Format.htmlEncode(values.value)]}"<tpl else> value="0"</tpl>',
        '/>',
        '<div style="width: 50%; float: left" id="file_cont_{cmpId}">',
        '<tpl if="value">',
        '<a href="/admin/files/filesadm/GetFileContent?ajax=1&id={value}" id="file_link_{cmpId}" />Скачать</a>',
        '<tpl else>',
        '<div style="padding-top: 4px">Файл не выбран</div>',
        '</tpl>',
        '</div>',
        '<div style="float: left; padding-left: 10px; width: 50%">',
        '<div id="file_select_button_cont_{cmpId}" style="margin-bottom: 10px">',
        '</div>',
        '<div id="file_load_button_cont_{cmpId}" style="margin-bottom: 10px">',
        '</div>',
        '</div>',
        '<br clear="all" />',
        '</div>',
        {
            disableFormats: true
        }
    ],
    inputType: 'hidden',
    width: 400,
    constructor: function (conf) {
        var me = this;
        me.callParent(arguments);
        if (me.value) {
            me.value = parseInt(me.value);
        }
        me._selWindow = Ext.create('Ext.ux.index.ImageSelectWindow', {imagesOnly: false});
    },

    getSubTplData: function () {
        var me = this,
            type = me.inputType,
            inputId = me.getInputId(),
            data;

        data = Ext.apply({
            id: inputId,
            cmpId: me.id,
            name: me.name || inputId,
            disabled: me.disabled,
            readOnly: me.readOnly,
            value: parseInt(me.getRawValue()),
            type: type,
            tabIdx: me.tabIndex,
            width: me.width,
            height: me.height,
            imgWidth: Math.floor(me.width / 2) - 4

        }, me.subTplData);

        me.getInsertionRenderData(data, me.subTplInsertions);

        return data;
    },
    onRender: function () {
        var me = this;

        me.callParent();
        me._selButton = Ext.create('Ext.Button', {
            text: me.selButtonText,
            renderTo: "file_select_button_cont_" + me.id,
            handler: function () {
                var me = this;
                me._selWindow.show(function (fileId) {
                    var me = this;
                    Ext.get('file_cont_' + me.id).dom.innerHTML = '<a href="/admin/files/filesadm/GetFileContent?ajax=1&id=' + fileId + '" id="file_link_' + me.id + '" />Скачать</a>';
                    Ext.get(me.getInputId()).dom.value = fileId;
                }, me);
            },
            scope: me
        });
    }
});
