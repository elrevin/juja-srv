Ext.define('App.modules.backend.Goods.Editor', {
    extend: 'App.core.SingleModelEditor',
    createToolbar: function () {
        var me;

        me = this.callParent();

        me.add({
            xtype: 'button',
            text: 'Custom button',
            width: 100
        });

        me.doLayout();

        return me;
    },
    init: function () {
        this.callParent();
    }
});