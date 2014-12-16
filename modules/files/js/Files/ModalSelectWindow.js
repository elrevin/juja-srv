Ext.define('App.modules.files.Files.ModalSelectWindow', {
    extend: 'App.core.CustomModalSelectWindow',
    listView: null,
    addFilePanel: null,
    getFields: function () {
        var me = this,
            fields;
        fields = me.mixins.modelLoader.getFields.call(me);
        fields[fields.length] = {
            name: 'icon',
            type: 'string',
            title: '',
            group: '',
            identify: false,
            required: true
        };
        return fields;
    },
    createItemsPanel: function () {
        var me = this;

        me.listView = Ext.create('Ext.view.View', {
            store: me.store,
            region: 'center',
            tpl: [
                '<tpl for=".">',
                '<div class="thumb-wrap" id="filesItems_{id}">',
                '<div class="thumb"><img src="{icon}&width=150&height=150&bgColor=EFEFEF" title="{title:htmlEncode}" style="width: 150px; height: 150px;"></div>',
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
                    fn: function(dv, selected ){
                        me.toolbar.getComponent('edit').setDisabled(!(selected.length == 1 && me.userRights > 1));
                        me.toolbar.getComponent('del').setDisabled(!(selected.length && me.userRights > 2));
                    }
                }
            }
        });

        me.addFilePanel = Ext.create('Ext.Panel', {
            region: 'east',
            layout: 'fit'

        });

        me.itemsPanel = Ext.create('Ext.Panel', {
            bodyCls: 'in-data-view',
            layout: 'border',
            items: [
                me.listView
            ]
        });
    }
});