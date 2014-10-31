Ext.define('Ext.ux.index.ImageSelectWindow', {
  mixins: {
    observable: 'Ext.util.Observable'
  },
  _window: null,
  _uploadForm: null,
  treeFolder: null,
  title: 'Выбор изображения',
  selectButtonText: "Выбрать",
  searchPlaceholder: 'Поиск по названию...',
  rootFolderName: 'Файлы и папки',
  rootFolderId: 0,
  imagesOnly: true,
  _tmpHandler: null,
  _tmpHandlerScope: null,
  _keyPressTimeout: 0,
  constructor: function (config) {
    var me = this;
    this.mixins.observable.constructor.call(this, config);
    Ext.apply(me, config);
    me._uploadForm = Ext.create('Ext.form.Panel', {
      region: "north",
      height: 80,
      fieldDefaults: {
        labelAlign: 'top',
        msgTarget: 'side'
      },
      items: [
        {
          xtype: 'fieldset',
          anchor: '100%',
          layout: 'hbox',
          title: 'Загрузить файл',
          items:[{
            xtype: 'container',
            width: 235,
            layout: 'anchor',
            items: [{
              xtype:'filefield',
              fieldLabel: 'Файл',
              allowBlank: false,
              name: 'file',
              anchor:'95%',
              buttonText: 'Выбрать...'
            }]
          },{
            xtype: 'container',
            width: 235,
            layout: 'anchor',
            items: [{
              xtype:'textfield',
              fieldLabel: 'Название',
              allowBlank: false,
              name: 'title',
              anchor:'95%'
            }]
          },{
            xtype: 'container',
            flex: 1,
            layout: 'anchor',
            style: 'padding-top: 20px',
            items: [{
              xtype:'button',
              text: 'Загрузить',
              handler: function () {
                var me = this;
                if(me._uploadForm.isValid()){
                  var
                    selected = me._window.items.getAt(2).getSelectionModel().getSelection(),
                    id = selected.length ? selected[0].get('id') : this.mainModel.record['id_folder'];

                  me._uploadForm.submit({
                    url: '/admin/files/filesadm/addfile?ajax=1&answer_type=json',
                    params: {
                      idFolder: id
                    },
                    waitMsg: 'Загрузка...',
                    success: function(fp, o){
                      me._window.items.getAt(1).items.getAt(0).getStore().load({'params':{'folder' : id}});
                      me._uploadForm.getForm().reset();
                    },
                    scope: me
                  });
                }
              },
              scope: me
            }]
          }]
        }
      ]
    });
    var store = new Ext.data.JsonStore ({
      pageSize: 30,
      proxy: {
        type: 'ajax',
        url: '/admin/files/filesadm/getfiles?ajax=1&answer_type=json',
        actionMethods:  {read: "POST"},
        reader: {
          type: 'json',
          root: 'files',
          idProperty: 'id'
        }
      },
      fields: ['id', {name: 'text', type: 'string'}, {name: 'src', type: 'string'}, 'path'],
      autoLoad: {
        folder: 0,
        start: 0,
        limit: 30,
        imagesOnly: me.imagesOnly ? 1 : 0
      }
    });
    me.treeFolder = Ext.create('Ext.tree.Panel', {
      header: false,
      width: 250,
      store: Ext.create('Ext.data.TreeStore',{
        autoload: false,
        fields: ['name', 'id', 'text', 'description'],
        root: {
          text: me.rootFolderName,
          id: me.rootFolderId,
          expanded: true
        },
        proxy: {
          type: 'ajax',
          url: '/admin/files/filesadm/getfolders?ajax=1&answer_type=json',
          actionMethods:  {read: "POST"},
          reader: {
            type: 'json',
            root: 'nodes'
          }
        }
      }),
      split: true,
      region: 'west',
      listeners: {
        itemclick: {
          fn: function (p, record) {
            this._window.items.getAt(1).items.getAt(0).getStore().getProxy().setExtraParam('folder', record.get('id'));
            this._window.items.getAt(1).items.getAt(0).getStore().getProxy().setExtraParam('imagesOnly', me.imagesOnly ? 1 : 0);
            this._window.items.getAt(1).items.getAt(0).getStore().reload();
          },
          scope: me
        }
      }
    });
    me._window = Ext.create('Ext.window.Window', {
      editImage: 0,
      title: me.title,
      width: 700,
      height: 550,
      resizable: false,
      modal: true,
      closeAction: 'hide',
      style: "padding: 5px;",
      bodyCls: 'ni-images-view',
      tbar: [
        Ext.create('Ext.form.TextField', {
          width: 200,
          emptyText: me.searchPlaceholder,
          enableKeyEvents: true,
          listeners: {
            keyup: {
              fn: function (field) {
                var me = this;
                if (me._keyPressTimeout) {
                  clearTimeout(me._keyPressTimeout);
                  // Обновление списка картинок
                }
                me._keyPressTimeout = setTimeout(function () {
                  var
                    selected =me._window.items.getAt(2).getSelectionModel().getSelection(),
                    id = selected.length ? selected[0].get('id') : me.rootFolderId;
                  me._window.items.getAt(1).items.getAt(0).getStore().getProxy().extraParams = {
                    folder : id,
                    search : field.getValue(),
                    start: 0,
                    limit: 30,
                    imagesOnly: me.imagesOnly ? 1 : 0
                  };
                  me._window.items.getAt(1).items.getAt(0).getStore().reload();
                }, 300);
              },
              scope: me
            }
          }
        })
      ],
      layout: 'border',
      items: [
        me._uploadForm,
        Ext.create('Ext.Panel', {
          layout: 'fit',
          region: 'center',
          frame: false,
          header: false,
          border: false,
          bodyStyle: "border-top: 1px solid #c3c3c3 !important",
          items: Ext.create('Ext.view.View', {
            store: store,
            tpl: [
              '<tpl for=".">',
              '<div class="thumb-wrap" id="sel_img_{id}" path="{path}">',
              '<div class="thumb"><img src="{src}" title="{text:htmlEncode}" style="width: 80px; height: 60px"></div>',
              '<span class="x-editable">{shortName:htmlEncode}</span>',
              '</div>',
              '</tpl>',
              '<div class="x-clear"></div>'
            ],
            autoScroll: true,
            multiSelect: false,
            trackOver: true,
            overItemCls: 'x-item-over',
            itemSelector: 'div.thumb-wrap',
            emptyText: 'Нет изображений',
            plugins: [
              Ext.create('Ext.ux.DataView.DragSelector', {}),
              Ext.create('Ext.ux.DataView.LabelEditor', {dataIndex: 'text'})
            ],
            prepareData: function(data) {
              Ext.apply(data, {
                shortName: Ext.util.Format.ellipsis(data.text, 15)
              });
              return data;
            },
            listeners: {
              selectionchange: function(dv, nodes ){

              }
            }
          }),
          dockedItems: [Ext.create('Ext.ux.toolbar.Paging', {
            dock: 'bottom',
            store: store,
            isplayInfo: true,
            displayMsg: ''
          })]
        }),
        me.treeFolder
      ],
      buttons: [
        {
          text: me.selectButtonText,
          handler: function () {
            var sel = this._window.items.getAt(1).items.getAt(0).getSelectedNodes();

            if (sel.length) {
              var selId = sel[0].id.replace('sel_img_', '');
              var selPath = sel[0].getAttribute('path');
              this.fireEvent('select', selId, selPath);
              if (this._tmpHandler) {
                if (this._tmpHandlerScope) {
                  this._tmpHandler.call(this._tmpHandlerScope, selId, selPath);
                } else {
                  this._tmpHandler.call(window, selId, selPath);
                }
              }
              this._tmpHandler = null;
              this._tmpHandlerScope = null;
              this._window.hide();
            }
          },
          scope: me
        }
      ]
    });
  },
  show: function (handler, scope) {
    var me = this;
    if (handler != undefined) {
      me._tmpHandler = handler;
    }

    if (scope != undefined) {
      me._tmpHandlerScope = scope;
    }
    me._window.show();
//    me.treeFolder.getStore().reload({
//      node: me.treeFolder.getStore().getRootNode(),
//      callback: function () {
//        me.treeFolder.getStore().getRootNode().expand();
//      }
//    });
    me.treeFolder.getSelectionModel().select(me.treeFolder.getStore().getRootNode());
  }
});