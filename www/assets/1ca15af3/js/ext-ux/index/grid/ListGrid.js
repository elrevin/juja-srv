Ext.require([
    'Ext.ux.grid.FiltersFeature',
    'Ext.ux.ajax.JsonSimlet',
    'Ext.ux.ajax.SimManager'
]);

Ext.define('Ext.ux.index.grid.ListGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.listgrid',
    modelClassName: '',
    addColumns: null,
    getDataAction: null,
    saveAction: null,
    deleteAction: null,
    sortAction: null,
    parentIdParamName: '',
    parentRecordId: 0,
    pageSize: 50,
    region: 'west',
    width: 250,
    collapsible: true,
    split: true,
    header: false,
    listForSelect: false,

    sortable: false,

    createColumn: function (field) {
        var me = this,
            column,
            isIdentify = field.identify;

        if (field.name == 'id') return;

        column = {
            dataIndex: field.name,
            text: field.title,
            sortable: !me.sortable // Если модель разрешает ручную сортировку (драг-н-дроп), то автоматическую запрещаем
        };

        if (!me.sortable) {
            if (["string", "text", 'html'].indexOf(field.type.type) >= 0) {
                column['filter'] = {
                    type: 'string'
                }
            } else if (field.type.type == 'pointer') {
                column['filter'] = {
                    type: 'pointer'
                }
            } else if (field.type.type == 'date') {
                column['filter'] = {
                    type: 'date',
                    dateFormat: 'Y-m-d',
                    beforeText: 'До',
                    afterText: 'После',
                    onText: "Дата"
                };
            } else if (field.type.type == 'datetime') {
                column['filter'] = {
                    type: 'datetime',
                    date: {
                        format: 'Y-m-d'
                    },

                    time: {
                        format: 'H:i:s',
                        increment: 1
                    },
                    beforeText: 'До',
                    afterText: 'После',
                    onText: "Дата"
                };
            } else if (field.type.type == 'bool') {
                column['filter'] = {
                    type: 'boolean',
                    yesText: 'Да',
                    noText: 'Нет'
                };
            } else if (field.type.type == 'select') {
                if (field.selectOptions) {
                    var opt = [], key;
                    for (key in field.selectOptions) {
                        opt[opt.length] = {
                            id: key,
                            text: field.selectOptions[key]
                        }
                    }
                    column['filter'] = {
                        type: 'list',
                        options: opt
                    };
                }
            } else if (field.type.type == 'int' || field.type.type == 'float') {
                column['filter'] = {
                    type: 'numeric'
                };
            }
        }

        if (["string", "int", 'float'].indexOf(field.type.type) >= 0) {
            column = Ext.apply(column, {
                width: (field.settings && field.settings.width ? field.settings.width : (field.type.type == 'string' ? 200 : 80)),
                renderer: function (val) {
                    if (isIdentify) {
                        return "<b>" + val + "</b>";
                    }
                    return val;
                }
            });
        } else if (field.type.type == 'text') {
            column = Ext.apply(column, {
                sortable: false,
                width: (field.settings && field.settings.width ? field.settings.width : 250),
                renderer: function (val) {
                    if (isIdentify) {
                        return "<b>" + val.substring(0, 150) + " ...</b>";
                    }
                    return val.substring(0, 150) + ' ...'
                }
            });
        } else if (field.type.type == 'date') {
            column = Ext.apply(column, {
                width: (field.settings && field.settings.width ? field.settings.width : 100),
                renderer: function (val, metaData, record, rowIndex, colIndex) {
                    if (val) {
                        if (isIdentify) {
                            return "<b>" + Ext.Date.format(val, "d.m.Y") + "</b>";
                        }
                        return Ext.Date.format(val, "d.m.Y");
                    }
                    return '';
                }
            });
        } else if (field.type.type == 'datetime') {
            column = Ext.apply(column, {
                width: (field.settings && field.settings.width ? field.settings.width : 140),
                renderer: function (val, metaData, record, rowIndex, colIndex) {
                    if (val) {
                        if (isIdentify) {
                            return "<b>" + Ext.Date.format(val, "d.m.Y H:i:s") + "</b>";
                        }
                        return Ext.Date.format(val, "d.m.Y H:i:s");
                    }
                    return '';
                }
            });
        } else if (field.type.type == 'bool') {
            column = Ext.apply(column, {
                width: (field.settings && field.settings.width ? field.settings.width : 60),
                renderer: function (val, metaData, record, rowIndex, colIndex) {
                    if (isIdentify) {
                        if (val) {
                            return '<b>Да</b>';
                        }
                        return '<b>Нет</b>';
                    }
                    if (val) {
                        return 'Да';
                    }
                    return 'Нет';
                }
            });
        } else if (field.type.type == 'pointer') {
            column = Ext.apply(column, {
                width: (field.settings && field.settings.width ? field.settings.width : 60),
                renderer: function (val, metaData, record, rowIndex, colIndex) {
                    if (val && val.id != undefined && val.value != undefined) {
                        if (isIdentify) {
                            return '<b>'+val.value+'</b>';
                        } else {
                            return val.value;
                        }
                    }
                    return '';
                }
            });
        } else if (field.type.type == 'select') {
            column = Ext.apply(column, {
                width: (field.settings && field.settings.width ? field.settings.width : 60),
                renderer: function (val) {
                    if (val && field.selectOptions && field.selectOptions[val.id]) {
                        return field.selectOptions[val.id];
                    }
                    return '';
                }
            });
        } else {
            column = null;
        }

        if (column) {
            if (!me.columns) {
                me.columns = [];
            }
            me.columns[me.columns.length] = column;
        }
    },

    createColumns: function () {
        var me = this,
            fields = Ext.ClassManager.classes[me.modelClassName].getFields(),
            i, tmpColNames = [];

        if (me.addColumns) {
            for (i = 0; i < me.addColumns.length; i++) {
                tmpColNames[i] = me.addColumns[i].dataIndex;
            }
        }

        for (i = 0; i < fields.length; i++) {
            if (me.addColumns && me.addColumns.length) {
                if (tmpColNames.indexOf(fields[i].name) < 0) {
                    me.createColumn(fields[i]);
                }
            } else {
                me.createColumn(fields[i]);
            }
        }

        if (me.addColumns && me.addColumns.length) {
            me.columns = me.columns.concat(me.addColumns);
        }
    },

    initComponent: function () {
        var me = this;

        me.createColumns();

        if (me.sortable) {
            me.sortAction = [];
            me.sortAction = [
                me.getDataAction[0],
                me.getDataAction[1],
                'sort-records'
            ];

            me.enableDragDrop = true;
            me.viewConfig = {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    dragGroup: 'reorger-'+me.modelClassName,
                    dropGroup: 'reorger-'+me.modelClassName,
                    dragText: "Поместить строку на новое место"
                },
                listeners: {
                    drop: {
                        fn: function(node, data, dropRec, dropPosition) {
                            var url, records = [], i;
                            if (data && data.records.length && dropRec) {
                                url = $url(
                                    me.sortAction[0],
                                    me.sortAction[1],
                                    me.sortAction[2],
                                    {modelName: me.modelClassName.replace('ModelClass', '')}
                                );

                                for (i = 0; i < data.records.length; i++) {
                                    records[records.length] = data.records[i].get('id');
                                }

                                Ext.Ajax.request({
                                    url: url,
                                    params: {
                                        records: Ext.JSON.encode(records),
                                        position: dropPosition,
                                        over: dropRec.get('id')
                                    },
                                    scope: this
                                });
                            }
                        },
                        scope: me
                    }
                }
            };
        } else {
            me.dockedItems = Ext.create('Ext.ux.toolbar.Paging', {
                dock: 'bottom',
                store: me.store
            });

            var filters = {
                ftype: 'filters',
                encode: true,
                local: false
            };
            me.features = [filters];
        }

        this.callParent(arguments);
    }
});