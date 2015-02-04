Ext.define('Ext.ux.form.field.TreeCombo',
  {
    extend: 'Ext.form.field.Picker',
    alias: 'widget.treecombo',
    tree: false,
    constructor: function (config) {
      this.addEvents(
        {
          "itemclick": true
        });


      this.listeners = config.listeners;
      this.callParent(arguments);
    },
    rootVisible: true,
    records: [],
    recursiveRecords: [],
    ids: [],
    selectChildren: true,
    canSelectFolders: true,
    displayField: 'text',
    valueField: 'id',
    width: 300,
    treeWidth: 300,
    matchFieldWidth: false,
    treeHeight: 200,
    masN: 0,

    spObj:'',
    spForm:'',

    recursivePush: function (node, setIds) {
      var me = this;


      me.addRecRecord(node);
      if (setIds) me.addIds(node);

      node.eachChild(function (nodesingle) {
        if (nodesingle.hasChildNodes() == true) {
          me.recursivePush(nodesingle, setIds);
        }
        else {
          me.addRecRecord(nodesingle);
          if (setIds) me.addIds(nodesingle);
        }
      });
    },
    recursiveUnPush: function (node) {
      var me = this;
      me.removeIds(node);

      node.eachChild(function (nodesingle) {
        if (nodesingle.hasChildNodes() == true) {
          me.recursiveUnPush(nodesingle);
        }
        else me.removeIds(nodesingle);
      });
    },
    addRecRecord: function (record) {
      var me = this;


      for (var i = 0, j = me.recursiveRecords.length; i < j; i++) {
        var item = me.recursiveRecords[i];
        if (item) {
          if (item.getId() == record.getId()) return;
        }
      }
      me.recursiveRecords.push(record);
    },
    afterLoadSetValue: false,
    setValue: function (valueInit) {
      if (typeof valueInit == 'undefined') {
        valueInit = 0;
      };

      var me = this,
        tree = this.tree,
        values = (valueInit == '') ? [] : valueInit.split(','),
        valueFin = [];

      inputEl = me.inputEl;


      if (tree.store.isLoading()) {
        //me.afterLoadSetValue = valueInit;
      }


      if (inputEl && me.emptyText && !Ext.isEmpty(values)) {
        inputEl.removeCls(me.emptyCls);
      }


      if (tree == false) return false;

      var node = tree.getRootNode();
      if (node == null) return false;

      me.recursiveRecords = [];
      me.recursivePush(node, false);

      me.records = [];
      Ext.each(me.recursiveRecords, function (record) {
        var id = record.get(me.valueField),
          index = values.indexOf('' + id);

        if (index != -1) {
          valueFin.push(record.get(me.displayField));
          me.addRecord(record);
        }
      });


      me.value = valueInit;
      me.setRawValue(valueFin.join(', '));

      me.checkChange();
      me.applyEmptyText();
      return me;
    },
    getValue: function () {
      return this.value;
    },
    getSubmitValue: function () {
      return this.value;
    },
    checkParentNodes: function (node) {
      if (node == null) return;

      var me = this,
        checkedAll = true;


      node.eachChild(function (nodesingle) {
        var id = nodesingle.getId(),
          index = me.ids.indexOf('' + id);

        if (index == -1) checkedAll = false;
      });

      if (checkedAll == true) {
        me.addIds(node);
        me.checkParentNodes(node.parentNode);
      }
      else {
        me.removeIds(node);
        me.checkParentNodes(node.parentNode);
      }
    },
    initComponent: function () {
      var me = this;

      me.tree = Ext.create('Ext.tree.Panel',
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
              if (me.afterLoadSetValue != false) {
                me.setValue(me.afterLoadSetValue);
              }
            },
            itemclick: function (view, record, item, index, e, eOpts) {
              me.itemTreeClick(view, record, item, index, e, eOpts, me)
            }
          }
        });

      this.createPicker = function () {
        var me = this;
        return me.tree;
      };

      this.callParent(arguments);
    },
    addIds: function (record) {
      var me = this;

      if (me.ids.indexOf('' + record.getId()) == -1) me.ids.push('' + record.get(me.valueField));
    },
    removeIds: function (record) {
      var me = this,
        index = me.ids.indexOf('' + record.getId());

      if (index != -1) {
        me.ids.splice(index, 1);
      }
    },
    addRecord: function (record) {
      var me = this;


      for (var i = 0, j = me.records.length; i < j; i++) {
        var item = me.records[i];
        if (item) {
          if (item.getId() == record.getId()) return;
        }
      }
      me.records.push(record);
    },
    removeRecord: function (record) {
      var me = this;


      for (var i = 0, j = me.records.length; i < j; i++) {
        var item = me.records[i];
        if (item && item.getId() == record.getId()) delete(me.records[i]);
      }
    },
    itemTreeClick: function (view, record, item, index, e, eOpts, treeCombo) {
      var me = treeCombo,
        checked = !record.get('checked');//it is still not checked if will be checked in this event

      var node = me.tree.getRootNode().findChild(me.valueField, record.get(me.valueField), true);
      if (node == null) {
        if (me.tree.getRootNode().get(me.valueField) == record.get(me.valueField)) node = me.tree.getRootNode();
        else return false;
      }


      //if it can't select folders and it is a folder check existing values and return false
      if (me.canSelectFolders == false && record.get('leaf') == false) {
        me.setRecordsValue(view, record, item, index, e, eOpts, treeCombo);
        return false;
      }

      //if record is leaf
      if (record.get('leaf') == true) {
        if (checked == true) {
          me.addIds(record);
        }
        else {
          me.removeIds(record);
        }
      }
      else //it's a directory
      {
        me.recursiveRecords = [];
        if (checked == true) {
          if (me.canSelectFolders == true) {
            me.recursivePush(node, true);
          }
        }
        else {
          if (me.canSelectFolders == true) me.recursiveUnPush(node);
          else me.removeIds(record);
        }
      }

      me.setRecordsValue(view, record, item, index, e, eOpts, treeCombo);
    },
    fixIds: function () {
      var me = this;

      for (var i = 0, j = me.ids.length; i < j; i++) {
        if (me.ids[i] == 'NaN') me.ids.splice(i, 1);
      }
    },
    setRecordsValue: function (view, record, item, index, e, eOpts, treeCombo) {
      var me = treeCombo;

      me.fixIds();

      me.setValue(me.ids.join(','));


      me.fireEvent('itemclick', me, record, item, index, e, eOpts, me.records, me.ids);


      me.onTriggerClick();
    }
  });