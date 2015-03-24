Ext.define('Ext.ux.index.MainMenuTreeColumn', {
    extend: 'Ext.tree.Column',
    alias: 'widget.mainmenutreecolumn',
    cellTpl: [
        '<tpl for="lines">',
        '<img src="{parent.blankUrl}" class="{parent.childCls} {parent.elbowCls}-img ',
        '{parent.elbowCls}-empty"/>',
        '</tpl>',
        '<img src="{blankUrl}"/>',
        '<tpl if="checked !== null">',
        '<input type="button" role="checkbox" <tpl if="checked">aria-checked="true" </tpl>',
        'class="{childCls} {checkboxCls}<tpl if="checked"> {checkboxCls}-checked</tpl>"/>',
        '</tpl>',
        '<tpl if="expandable">' +
        '<img src="{blankUrl}" class="cp-node-arrow {expanderCls} {baseIconCls}">' +
        '<tpl else>',
            '<tpl if="isFirstLevel">' +
            '<img src="/cp-files/images/modules-icons/place_for_ico.png">' +
            '</tpl>'+
        '</tpl>',
        '<tpl if="isFirstLevel">' +
            '<tpl if="icon">',
            '<img src="{blankUrl}" class="{childCls} cp-main-tree-icon" style="background-image:url({icon})"/>',
            '<tpl else>',
            '<img src="/cp-files/images/modules-icons/simple.png" />',
            '</tpl>' +
        '</tpl>',
        '<tpl if="href">',
        '<a href="{href}" target="{hrefTarget}" class="{textCls} {childCls}">{value} </a>',
        '<tpl else>',
        '<span class="<tpl if="isFirstLevel"> first-level-node-text <tpl else> deep-node-text <tpl if="expandable"> deep-node-text-expandable </tpl> </tpl>" niMainMenu="mainMenuNode_{record.data.id}">{value}</span>',
//          '<img src="'+baseURL+'/images/buttons/plus.small.hidden.png" style="margin-top: -4px; margin-bottom: -4px; margin-right: 4px" id="treeToolPlus_{record.data.id}" onClick="NextIndexApp.getApplication()._mainMenuToolClick(\'plus\', \'{record.data.id}\')">',
//          '<img src="'+baseURL+'/images/buttons/minus.small.hidden.png" style="margin-top: -4px; margin-bottom: -4px; margin-right: 4px" id="treeToolMinus_{record.data.id}">',
        '</tpl>'
    ],
    initComponent: function () {
        var me = this;
        me.callParent();
    },
    treeRenderer: function (value, metaData, record, rowIdx, colIdx, store, view) {
        var me = this,
            cls = record.get('cls'),
            renderer = me.origRenderer,
            data = record.data,
            parent = record.parentNode,
            rootVisible = view.rootVisible,
            lines = [],
            parentData;

        if (cls) {
            metaData.tdCls += ' ' + cls;
        }

        while (parent && (rootVisible || parent.data.depth > 0)) {
            parentData = parent.data;
            lines[rootVisible ? parentData.depth : parentData.depth - 1] =
                parentData.isLast ? 0 : 1;
            parent = parent.parentNode;
        }

        return me.getTpl('cellTpl').apply({
            record: record,
            baseIconCls: me.iconCls,
            iconCls: data.iconCls,
            icon: data.icon,
            checkboxCls: me.checkboxCls,
            checked: data.checked,
            elbowCls: me.elbowCls,
            expanderCls: me.expanderCls,
            textCls: me.textCls,
            leaf: data.leaf,
            expandable: record.isExpandable(),
            isLast: data.isLast,
            blankUrl: Ext.BLANK_IMAGE_URL,
            href: data.href,
            hrefTarget: data.hrefTarget,
            lines: lines,
            metaData: metaData,
            childCls: me.getChildCls ? me.getChildCls() + ' ' : '',
            value: (renderer ? renderer.apply(me.origScope, arguments) : value),
            isFirstLevel: record.get('depth') == 1
        });
    }
});