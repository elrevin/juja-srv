Ext.define('Ext.ux.form.field.TreeCombo',
    {
        extend: 'Ext.form.field.Picker',
        alias: 'widget.treecombo',
        treePanel: null,
        rootVisible: true,
        canSelectChildren: true,
        canSelectFolders: true,
        displayField: 'text',
        valueField: 'id',
        width: 300,
        treeWidth: 300,
        treeHeight: 200,
        afterLoadSetValue: null,

        constructor: function (config) {
            this.addEvents(
                {
                    "itemclick": true
                });


            this.listeners = config.listeners;
            this.callParent(arguments);
        },

        setValue: function (value) {
            var me = this, rootNode, valueNode;

            if (value == undefined) {
                value = 0;
            }

            if (value instanceof Object) {
                // Имеем value в виде объекта {id, value}
                value = value.id;
            }

            if (me.inputEl && me.emptyText && !Ext.isEmpty(values)) {
                me.inputEl.removeCls(me.emptyCls);
            }

            if (!me.treePanel) {
                me.value = null;
                return false;
            }

            rootNode = me.treePanel.getRootNode();
            if (!rootNode) {
                me.value = null;
                return false;
            }

            valueNode = rootNode.findChild('id', value, true);
            if (!valueNode) {
                me.value = null;
                return false;
            }

            me.value = {
                id: valueNode.get('id'),
                value: valueNode.get(me.valueField)
            };

            me.checkChange();
            me.applyEmptyText();
            return me;
        },

        getValue: function () {
            return this.value;
        },

        getSubmitValue: function () {
            return (this.value ? this.value.id : null);
        },

        initComponent: function () {
            var me = this;

            me.treePanel = Ext.create('Ext.tree.Panel',
                {
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
                    listeners: {
                        load: function (store, records) {

                        },
                        itemclick: function (view, record, item, index, e, eOpts) {
                            me.itemTreeClick(view, record, item, index, e, eOpts)
                        }
                    }
                });

            this.createPicker = function () {
                var me = this;
                return me.treePanel;
            };

            this.callParent(arguments);
        },

        itemTreeClick: function (view, record, item, index, e, eOpts) {
            var me = this,
                node;

            node = me.treePanel.getRootNode().findChild(me.valueField, record.get(me.valueField), true);
            if (node == null) {
                if (me.treePanel.getRootNode().get(me.valueField) == record.get(me.valueField)) {
                    node = me.treePanel.getRootNode();
                } else {
                    return false;
                }
            }


            if (me.canSelectFolders == false && record.get('leaf') == false) {
                return false;
            }

            me.setRecordsValue(view, node, item, index, e, eOpts);
        },

        setRecordsValue: function (view, record, item, index, e, eOpts) {
            var me = this;

            me.setValue(record.get(me.valueField));

            me.fireEvent('itemclick', me, record, item, index, e, eOpts, me.records, me.ids);

            me.onTriggerClick();
        }
    });