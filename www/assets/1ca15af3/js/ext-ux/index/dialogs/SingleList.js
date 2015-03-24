Ext.define("Ext.ux.index.dialogs.SingleList", {
    window: null,
    grid: null,
    model: null,
    width: 500,
    height: 500,
    onDialogLoad: null,
    onDialogLoadScope: null,

    select: function (handler, scope) {
        var me = this, _handler = handler, _scope = scope;
        if (!me.window) {
            me.window = Ext.create('Ext.window.Window', {
                layout: 'fit',
                width: me.width,
                height: me.height,
                title: 'Выбрать ' + me.model.title,
                closeAction: 'hide',
                items: Ext.create('Ext.ux.index.editors.ListGrid', {
                    model: me.model,
                    listForSelect: true
                }),
                buttons: [
                    {
                        text: 'Выбрать',
                        handler: function () {
                            var sm = me.window.items.getAt(0).getSelectionModel();
                            if (sm.getCount()) {
                                var rec = sm.getSelection()[0];
                                if (_handler) {
                                    if (_scope) {
                                        _handler.call(_scope, rec);
                                    } else {
                                        _handler.call(this, rec);
                                    }
                                }
                                me.window.hide();
                            }
                        },
                        scope: this
                    }
                ]
            });
        }
        me.window.show();
    },
    constructor: function (config) {
        var me = this;
        Ext.apply(me, config);
        if (me.onDialogLoad) {
            if (me.onDialogLoadScope) {
                me.onDialogLoad.call(me.onDialogLoadScope, this);
            } else {
                me.onDialogLoad(this);
            }
        }
    }
});