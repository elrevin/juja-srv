Ext.define('Ext.ux.index.ImgSelectField', {
  extend: 'Ext.form.field.Base',
  alias: 'widget.imgselectfield',
  selButtonText: "Изображение на сервере",
  loadButtonText: "Загрузить с компьютера",
  _selWindow: null,
  fieldSubTpl: [ // note: {id} here is really {inputId}, but {cmpId} is available
    '<div class="ni-img-field" style="width: {width}px;">',
      '<input id="{id}" type="{type}" {inputAttrTpl}',
      '<tpl if="name"> name="{name}"</tpl>',
      '<tpl if="value"> value="{[Ext.util.Format.htmlEncode(values.value)]}"<tpl else> value="0"</tpl>',
      '/>',
      '<div style="width: 50%; float: left" id="img_cont_{cmpId}">',
        '<tpl if="value">',
          '<img src="/admin/files/filesadm/GetImage?ajax=1&id={value}&width={imgWidth}" id="img_preview_{cmpId}" />',
        '<tpl else>',
          '<div style="padding-top: 4px">Изображение не выбрано</div>',
        '</tpl>',
      '</div>',
      '<div style="float: left; padding-left: 10px; width: 50%">',
        '<div id="img_select_button_cont_{cmpId}" style="margin-bottom: 10px">',
        '</div>',
        '<div id="img_load_button_cont_{cmpId}" style="margin-bottom: 10px">',
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
    me._selWindow = Ext.create('Ext.ux.index.ImageSelectWindow', {});
  },

  getSubTplData: function() {
    var me = this,
      type = me.inputType,
      inputId = me.getInputId(),
      data;

    data = Ext.apply({
      id         : inputId,
      cmpId      : me.id,
      name       : me.name || inputId,
      disabled   : me.disabled,
      readOnly   : me.readOnly,
      value      : parseInt(me.getRawValue()),
      type       : type,
      tabIdx     : me.tabIndex,
      width      : me.width,
      height     : me.height,
      imgWidth   : Math.floor(me.width/2) - 4

    }, me.subTplData);

    me.getInsertionRenderData(data, me.subTplInsertions);

    return data;
  },
  onRender : function() {
    var me = this;

    me.callParent();
    me._selButton = Ext.create('Ext.Button', {
      text: me.selButtonText,
      renderTo: "img_select_button_cont_"+me.id,
      handler: function () {
        var me = this;
        me._selWindow.show(function (imgId) {
          var me = this;
          Ext.get('img_cont_'+me.id).dom.innerHTML = '<img src="/admin/files/filesadm/GetImage?ajax=1&id='+imgId+'&width='+(Math.floor(me.width/2) - 4)+'" id="img_preview_'+me.id+'" />';
          Ext.get(me.getInputId()).dom.value = imgId;
        }, me);
      },
      scope: me
    });
//    me._loadButton = Ext.create('Ext.form.field.FileButton', {
//      text: me.loadButtonText,
//      renderTo: "img_load_button_cont_"+me.id
//    });
  }
});
