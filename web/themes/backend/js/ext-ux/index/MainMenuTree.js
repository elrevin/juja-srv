Ext.require([
    'Ext.ux.index.MainMenuTreeColumn',
    'Ext.ux.index.MainMenuTreeHeader'
]);

Ext.override(Ext.tree.View, {
    toggleOnDblClick: false,
    onItemClick: function (record, item, index, e) {
        if ((e.getTarget(this.expanderSelector, item) && record.isExpandable()) || (!this.toggleOnDblClick && record.isExpandable())) {
            this.toggle(record, e.ctrlKey);
            //return false;
        }
        return this.callParent(arguments);
    }
});

Ext.define('Ext.ux.index.MainMenuTree', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.mainmenutreepanel',
    alternateClassName: [],
    bodyCls: 'cp-main-menu-body',

    header: {
        xtype: "mainmenutreeheader"
    },

    columns: [{
        xtype: 'mainmenutreecolumn',
        text: 'Название',
        width: Ext.isIE6 ? '100%' : 10000, // IE6 needs width:100%
        dataIndex: 'title'
    }],

    viewConfig: {
        rowTpl: [
            '{%',
            'var dataRowCls = (values.recordIndex === -1 ? "" : " ' + Ext.baseCSSPrefix + 'grid-data-row")+(values.record.get("isRootNode") ? "" : " ' + Ext.baseCSSPrefix + 'grid-data-row-inner");',
            '%}',
            '<tr role="row" {[values.rowId ? ("id=\\"" + values.rowId + "\\"") : ""]} ',
            'data-boundView="{view.id}" ',
            'data-recordId="{record.internalId}" ',
            'data-recordIndex="{recordIndex}" ',
            'class="{[values.itemClasses.join(" ")]} {[values.rowClasses.join(" ")]}{[dataRowCls]}" ',
            '{rowAttr:attributes} tabIndex="-1" rrrttt=1>',
            '<tpl for="columns">' +
            '{%',
            'parent.view.renderCell(values, parent.record, parent.recordIndex, xindex - 1, out, parent)',
            '%}',
            '</tpl>',
            '</tr>',
            {
                priority: 0
            }
        ],
        toggleOnDblClick: false
    },

//  placeholder: {
//    xtype: 'panel',
//    border: false,
//    header: false,
//    bodyCls: 'cp-main-menu-placeholder',
//    width: 43,
//    layout: {
//      type: 'vbox',
//      align: 'stretch'
//    }
//  },

    initPlaceholderMenu: function (node) {
        var child,
            children = node.childNodes,
            me = this,
            placeholder = me.getPlaceholder(),
            el;
        for (var i = 0; i < children.length; i++) {
            child = children[i];
            placeholder.add({
                xtype: 'box',
                height: 34,
                autoEl: {
                    cn: "<div class='button' nodeId='" + child.get('id') + "' id='plh-button-" + child.get('id') + "'><div class='button-inner' style='background-repeat: no-repeat; background-image: url(\"" + child.get('icon') + "\")'></div></div> "
                },
                elId: "plh-button-" + child.get('id'),
                listeners: {
                    render: function (thisBox) {
                        el = Ext.get(thisBox.elId).on('mouseover', function (e, target) {
                            var target = Ext.get(target).parent();
                            target.addCls('button-over')
                        });
                        el = Ext.get(thisBox.elId).on('mouseleave', function (e, target) {
                            var target = Ext.get(target);
                            target.removeCls('button-over');
                        });
                    }
                }
            });
        }
    },

    initComponent: function () {
        var me = this;
        me.callParent();
        me.addCls(me.autoWidthCls);

//    var root = me.getStore().getRootNode();
//    me.store.on('load', function (store, node) {
//      if (node.isRoot()) {
//        me.initPlaceholderMenu(node);
//      }
//
//    });
//
    }
});