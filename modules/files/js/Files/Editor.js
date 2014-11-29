Ext.define('App.modules.files.Files.Editor', {
    extend: 'App.core.Module',
    mixins: {
        modelLoader: 'Ext.ux.index.mixins.ModelLoaderWithStore'
    },
    listView: null,
    toolbar:null,
    editWindow: null,
    createEditWindow: function () {
        var me = this;

        me.editWindow = Ext.create('Ext.Window', {
            title: '',
            width: 300,
            hight: 500,
            modal: true,
            resizable: false,
            items: [
                {
                    xtype: 'form',
                    border: false,
                    header: false,
                    items: [
                        {
                            xtype: 'text',
                            fieldLabel: 'Название',
                            labelAlign: 'top'
                        }, {
                            xtype: 'text',
                            fieldLabel: 'Название',
                            labelAlign: 'top'
                        }
                    ]
                }
            ]
        });
    },
    createToolbar: function () {
        var me = this,
            buttons = [];

        if (me.userRights > 1) {
            buttons[buttons.length] = {
                xtype: 'button',
                text: 'Добавить',
                icon: $themeUrl('/images/buttons/plus.png'),
                scope: this,
                itemId: 'add',
                handler: function () {
                    me.addRecord();
                }
            };
            buttons[buttons.length] = { xtype: 'tbspacer' };

            if (me.userRights > 2) {
                buttons[buttons.length] = {
                    xtype: 'button',
                    icon: $themeUrl('/images/buttons/del.png'),
                    scope: this,
                    itemId: 'del',
                    disabled: true,
                    handler: function () {
                        me.deleteRecord();
                    }
                };
            }

            me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
                height: 58,
                style: "background: #f0f0f0",
                defaults: {
                    scale: 'medium'
                },
                items: buttons
            });
            return me.toolbar;
        }
    },
    createListView: function () {
        var me = this;

        me.listView = Ext.create('Ext.view.View', {
            store: me.store,
            tpl: [
                '<tpl for=".">',
                '<div class="thumb-wrap" id="filesItems_{id}">',
                '<div class="thumb"><img src="{icon}?nc=' + new Date().getTime() + '" title="{title:htmlEncode}" style="width: 80px; height: 60px;"></div>',
                '<span class="x-editable">{shortName:htmlEncode}</span>',
                '</div>',
                '</tpl>',
                '<div class="x-clear"></div>'
            ],
            autoScroll: true,
            multiSelect: true,
            overItemCls: 'x-item-over',
            itemSelector: 'div.thumb-wrap',
            emptyText: 'Нет файлов',
            plugins: [
                Ext.create('Ext.ux.DataView.DragSelector', {})
            ],
            prepareData: function(data) {
                Ext.apply(data, {
                    shortName: Ext.util.Format.ellipsis(data.title, 45)
                });
                return data;
            },
            listeners: {
                selectionchange: {
                    fn: function(dv, nodes ){
                    },
                    scope: this
                }
            }
        });
    },
    init: function () {
        var me = this;

        if (me.userRights > 1) {
            if (me.getDataAction.length) {
                me.createActions();
                me.createModelClass();
                if (me.modelClassName) {
                    me.createStore();
                    me.createListView();
                    me._mainPanel = Ext.create('Ext.Panel', {
                        layout: 'fit',
                        tbar: me.createToolbar(),
                        items: [
                            me.listView
                        ]
                    });
                    me.callParent();
                    return;
                }
            }
        }

        me.fireEvent('initfail');
    }
});