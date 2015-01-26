Ext.define('Ext.ux.toolbar.Paging', {
    extend: 'Ext.toolbar.Paging',
    alias: 'widget.uxpagingtoolbar',
    getPagingItems: function () {
        var me = this;

        return [{
            itemId: 'first',
            tooltip: me.firstText,
            overflowText: me.firstText,
            iconCls: Ext.baseCSSPrefix + 'tbar-page-first',
            disabled: true,
            handler: me.moveFirst,
            scope: me
        }, {
            itemId: 'prev',
            tooltip: me.prevText,
            overflowText: me.prevText,
            iconCls: Ext.baseCSSPrefix + 'tbar-page-prev',
            disabled: true,
            handler: me.movePrevious,
            scope: me
        },
            '-',
            me.beforePageText,
            {
                xtype: 'numberfield',
                itemId: 'inputItem',
                name: 'inputItem',
                cls: Ext.baseCSSPrefix + 'tbar-page-number',
                allowDecimals: false,
                minValue: 1,
                hideTrigger: true,
                enableKeyEvents: true,
                keyNavEnabled: false,
                selectOnFocus: true,
                submitValue: false,
                // mark it as not a field so the form will not catch it when getting fields
                isFormField: false,
                width: me.inputItemWidth,
                margins: '-1 2 3 2',
                listeners: {
                    scope: me,
                    keydown: me.onPagingKeyDown,
                    blur: me.onPagingBlur
                }
            }, {
                xtype: 'tbtext',
                itemId: 'afterTextItem',
                text: Ext.String.format(me.afterPageText, 1)
            },
            '-',
            {
                itemId: 'next',
                tooltip: me.nextText,
                overflowText: me.nextText,
                iconCls: Ext.baseCSSPrefix + 'tbar-page-next',
                disabled: true,
                handler: me.moveNext,
                scope: me
            }, {
                itemId: 'last',
                tooltip: me.lastText,
                overflowText: me.lastText,
                iconCls: Ext.baseCSSPrefix + 'tbar-page-last',
                disabled: true,
                handler: me.moveLast,
                scope: me
            },
            '-',
            'записей на страницу',
            {
                xtype: 'numberfield',
                itemId: 'inputPageSize',
                cls: Ext.baseCSSPrefix + 'tbar-page-number',
                allowDecimals: false,
                minValue: 1,
                hideTrigger: true,
                enableKeyEvents: true,
                keyNavEnabled: false,
                selectOnFocus: true,
                submitValue: false,
                // mark it as not a field so the form will not catch it when getting fields
                isFormField: false,
                width: me.inputItemWidth,
                margins: '-1 2 3 2',
                value: me.store.pageSize,
                listeners: {
                    scope: me,
                    keydown: me.onCountKeyDown
                }
            }, '-',
            {
                itemId: 'refresh',
                tooltip: me.refreshText,
                overflowText: me.refreshText,
                iconCls: Ext.baseCSSPrefix + 'tbar-loading',
                handler: me.doRefresh,
                scope: me
            }];
    },
    onCountKeyDown: function (field, e) {
        var me = this,
            k = e.getKey(),
            pageData = me.getPageData(),
            increment = e.shiftKey ? 10 : 1,
            pageNum,
            pageSize;

        if (k == e.RETURN) {
            e.stopEvent();
            pageNum = 1;
            pageNum = Math.min(Math.max(1, pageNum), pageData.pageCount);
            if (me.fireEvent('beforechange', me, pageNum) !== false) {
                pageSize = this.child('#inputPageSize').getValue();
                me.store.pageSize = pageSize;
                if (me.store.model && me.store.model.$className) {
                    localStorageSet(me.store.model.$className+"_pageSize", pageSize);
                }
                me.store.loadPage(pageNum);
            }
        }
    }
});