Ext.define('Ext.ux.form.field.TreeCombo', {
    extend: 'Ext.form.field.Picker',
    alias: 'widget.treecombo',
    treePanel: null,
    treeWidth: 300,
    treeHeight: 200,
    store: null,
    displayField: 'text',
    rootVisible: true,
    afterLoadSetValue: null,
    initComponent: function () {
        var me = this;

        me.store.wasLoaded = false;
        me.treePanel = Ext.create('Ext.tree.Panel', {
            alias: 'widget.assetstree',
            hidden: true,
            minHeight: 50,
            rootVisible: (typeof me.rootVisible != 'undefined') ? me.rootVisible : true,
            floating: true,
            useArrows: true,
            width: me.treeWidth,
            autoScroll: true,
            height: me.treeHeight,
            store: me.store,
            displayField: me.displayField,
            pickerField: this,
            listeners: {
                load: function (store, records) {
                    store.wasLoaded = true;
                    if (me.afterLoadSetValue) {
                        me.setValue(me.afterLoadSetValue);
                    }
                },
                itemclick: function (view, record, item, index, e, eOpts) {
                    me.setValue(record.get('id'));
                    me.collapse();
                }
            },
            setRootNode: function() {
                if (me.store.autoLoad) {
                    me.treePanel.callParent(arguments);
                }
            }
        });
        me.store.on('load', function () {
           if (me.afterLoadSetValue) {
               me.setValue(me.afterLoadSetValue);
           }
        });
        me.callParent(arguments);
    },
    createPicker: function () {
        var me = this;
        return me.treePanel;
    },

    setValue: function (value) {
        var me = this,
            idValue,
            node;
        if (!me.treePanel) {
            me.value = null;
            return false;
        }

        if (value instanceof Object) {
            idValue = value.id;
        } else {
            idValue = value;
        }

        if (me.treePanel.store.isLoading() || !me.treePanel.store.wasLoaded) {
            me.afterLoadSetValue = idValue;
        }

        node = me.treePanel.getRootNode();
        if (!node) {
            me.value = null;
            return false;
        }

        node = node.findChild('id', idValue, true);
        if (!node) {
            me.value = null;
            return false;
        }

        me.value = {
            id: node.get('id'),
            value: node.get(me.displayField)
        };

        if (me.inputEl && me.emptyText && !Ext.isEmpty(values)) {
            me.inputEl.removeCls(me.emptyCls);
        }

        if (me.inputEl) {
            me.inputEl.dom.value = me.value.value;
        }

        return me;
    },

    getValue: function () {
        var me = this;
        return me.value;
    }
});