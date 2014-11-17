Ext.define('Ext.ux.index.window.EditorWindow', {
    extend: 'Ext.Window',
    alias: ['widget.uxindexform'],
    model: null,
    form: null,
    mode: 'insert',
    width: 200,
    height: 300,
    recordTitle: '',
    accusativeRecordTitle: '',
    store: null,

    setSizeAndPosition: function () {
        var me = this,
            maxWidth, maxHeight,
            width, height;

        maxWidth = IndexNextApp.getApplication().viewport.getWidth() - Math.floor(IndexNextApp.getApplication().viewport.getWidth() * 0.5);
        maxHeight = IndexNextApp.getApplication().viewport.getHeight() - Math.floor(IndexNextApp.getApplication().viewport.getHeight() * 0.5);

        width = me.items.getAt(0).getWidth();
        height = me.items.getAt(0).getHeight();
        if (width > maxWidth) {
            me.setWidth(maxWidth);
        } else {
            me.setWidth(width+20);
        }
        if (height > maxHeight) {
            me.setHeight(maxHeight);
        } else {
            me.setHeight(height+70);
        }

        me.center();
    },

    onRender: function (parentNode, containerIdx) {
        this.callParent(arguments);
    },

    initComponent: function () {
        var me = this,
            saveButtonText = 'Сохранить';
        me.addEvents('beforeload', 'afterload', 'beforeupdate', 'afterupdate', 'beforeinsert', 'afterinsert');

        if (me.model) {
            me.closeAction = 'hide';
            me.autoScroll = true;
            me.modal = true;
            me.form = Ext.create('Ext.ux.index.form.SimpleForm', {
                model: me.model,
                tabs: [],
                listeners: {
                    beforeupdate: function () {
                        me.fireEvent('beforeupdate', me);
                    },
                    beforeinsert: function () {
                        me.fireEvent('beforeinsert', me);
                    },
                    afterinsert: function () {
                        me.fireEvent('afterinsert', me);
                    },
                    afterupdate: function () {
                        me.fireEvent('afterupdate', me);
                    },
                    beforeload: function (form, record) {
                        me.fireEvent('beforeload', me, record);
                    },
                    afterload: function (form, record) {
                        me.fireEvent('afterload', me, record);
                    },
                    render: function () {
                        me.setSizeAndPosition();
                    }
                },
                userRights: me.userRights
            });

            me.items = [me.form];

            me.buttons = [{
                text: 'Сохранить',
                handler: function () {
                    me.form.save();
                }
            }];

        }
        me.callParent(arguments);

        if (me.mode == 'update') {
            me.setTitle('Изменить '+me.accusativeRecordTitle.toLowerCase());
        } else {
            me.setTitle('Добавить '+me.accusativeRecordTitle.toLowerCase());
        }
    },

    setMode: function (mode) {
        var me = this;
        me.mode = mode;
        me.form.mode = mode;
    },

    loadRecord: function (record) {
        var me = this;
        me.setMode('update');
        me.form.loadRecord(record);
    }
});