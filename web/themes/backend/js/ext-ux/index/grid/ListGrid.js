Ext.require([
    'Ext.ux.grid.FiltersFeature',
    'Ext.ux.ajax.JsonSimlet',
    'Ext.ux.ajax.SimManager'
]);

Ext.define('Ext.ux.index.grid.ListGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.listgrid',
    modelClassName: '',
    getDataAction: [],
    saveAction: [],
    deleteAction: [],
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
            column = null,
            isIdentify = field.identify;

        if (field.name == 'id') return;

        column = {
            dataIndex: field.name,
            text: field.title,
            sortable: !me.sortable // Если модель разрешает ручную сортировку (драг-н-дроп), то автоматическую запрещаем
        };

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
            i;

        for (i = 0; i < fields.length; i++) {
            me.createColumn(fields[i]);
        }
    },

    initComponent: function () {
        var me = this;

        me.createColumns();

        this.callParent(arguments);
    }
});