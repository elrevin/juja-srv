Ext.define('Ext.ux.index.form.TitleEditPanel', {
  extend: 'Ext.Panel',
  alias: ['widget.uxtitleeditpanel'],
  field: null,
  fieldPanel: null,
  textPanel: null,
  defaultText: 'Новая запись sadg dfg df gf gsdf gsdfg sdfgsdfgs dfgsdfg sdfg sdfg',
  defaultValue: 'Новая запись',

  initComponent: function () {
    var me = this;
    me.bodyCls = 'in2-form-title-container';
    me.layout = "card";
    me.height = 62;

    me.textPanel = Ext.create("Ext.Panel", {
      border: false,
      header: false,
      hidden: false,
      html: "<b>"+me.defaultText+"</b>&nbsp;&nbsp;<a href='#' id='in2-title-edit-button-"+me.id+"'></a>"
    });

    me.fieldPanel = Ext.create("Ext.Panel", {
      border: false,
      header: false,
      items: [
        Ext.create('Ext.form.field.Text', {
          hideLabel: true,
          width: 300
        })
      ]
    });

    me.items = [
      me.textPanel,
      me.fieldPanel
    ];

    me.activeItem = 0;

    me.callParent();
  },

  showEditor: function () {
    var me = this;

    me.getLayout().setActiveItem(1);
  },

  afterRender: function (container, position) {
    var me = this;

    Ext.get('in2-title-edit-button-'+me.id).dom.onclick = function () {
      me.showEditor();

      return false;
    };
    me.callParent(arguments);
  }
});