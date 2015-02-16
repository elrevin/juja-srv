function doOverride() {
    Ext.override(Ext.resizer.Splitter, {
        size: 13,
        style: {
            background: "url('" + $assetUrl("/images/extOverride/splitter-bg.png") + "')"
        }
    });


    Ext.override(Ext.data.writer.Writer, {
        writeValue: function (data, field, record) {
            var name = field[this.nameProperty],
                dateFormat = this.dateFormat || field.dateWriteFormat || field.dateFormat,
                dateTimeFormat = this.dateTimeFormat || this.dateFormat || field.dateWriteFormat || field.dateFormat,
                value = record.get(field.name);

            // Allow the nameProperty to yield a numeric value which may be zero.
            // For example, using a field's numeric mapping to write an array for output.
            if (name == null) {
                name = field.name;
            }

            if (field.serialize) {
                data[name] = field.serialize(value, record);
            } else if (field.type === Ext.data.Types.DATE && dateFormat && Ext.isDate(value)) {
                data[name] = Ext.Date.format(value, dateFormat);
            } else if (field.type === Ext.data.Types.DATETIME && dateTimeFormat && Ext.isDate(value)) {
                data[name] = Ext.Date.format(value, dateTimeFormat);
            } else {
                data[name] = value;
            }
        }
    });

    Ext.override(Ext.data.Model, {
        modelName: '', // Имя модели в PHP
        recordTitle: '', // Название записи в едиственном чистел в именительном падеже, нпрмер "характеристика"
        accusativeRecordTitle: '', // Название записи в единственном числе в винительном падеже, например "характеристику"
        createNew: function () {
            return Ext.create(this.$className, {});
        },
        recursive: false, // Модель рекурсивная
        getDataAction: [],
        saveAction: [],
        deleteAction: []
    });

    Ext.override(Ext.data.Field, {
        title: '',
        group: '',
        required: false,
        identify: false,
        extra: false, // true если поле будет сабмититься, но создаваться в форме и в гриде не будет. Например поля hidden, parent_id
        relativeModel: {
            name: '',
            moduleName: '',
            modalSelect: false,
            runAction: '',
            identifyFieldName: '',
            identifyFieldType: ''
        },
        settings: {},
        showCondition: {},
        filterCondition: {},
        calc: false,
        selectOptions: {}
    });

    Ext.data.Types.TEXT = {
        convert: function (v) {
            var defaultValue = this.useNull ? null : '';
            return (v === undefined || v === null) ? defaultValue : String(v);
        },
        sortType: function (v) {
            return '';
        },
        type: 'text'
    };

    Ext.data.Types.HTML = {
        convert: function (v) {
            var defaultValue = this.useNull ? null : '';
            return (v === undefined || v === null) ? defaultValue : String(v);
        },
        sortType: function (v) {
            return '';
        },
        type: 'html'
    };

    Ext.data.Types.DATETIME = {
        convert: function (v) {
            var df = this.dateReadFormat || this.dateFormat,
                parsed;

            if (!v) {
                return null;
            }
            // instanceof check ~10 times faster than Ext.isDate. Values here will not be cross-document objects
            if (v instanceof Date) {
                return v;
            }
            if (df) {
                return Ext.Date.parse(v, df);
            }

            parsed = Date.parse(v);
            return parsed ? new Date(parsed) : null;
        },
        sortType: Ext.data.SortTypes.asDate,
        type: 'datetime'
    };

    Ext.data.Types.SELECT = {
        convert: function(v, data) {
            var me=this;
            if (v == undefined) {
                return v;
            }
            if (!v.length) {
                return null
            }
            var obj = Ext.JSON.decode(v);
            if (obj && obj.id != undefined && obj.value != undefined) {
                return obj;
            }
            return null;
        },
        sortType: function(v) {
            return v.value;
        },
        type: 'select'
    };

    Ext.data.Types.POINTER = {
        convert: function(v, data) {
            var me=this;
            if (v == undefined) {
                return v;
            }
            if (!v.length) {
                return null
            }
            var obj = Ext.JSON.decode(v);
            if (obj && obj.id != undefined && obj.value != undefined) {
                return obj;
            }
            return null;
        },
        sortType: function(v) {
            return v.value;
        },
        type: 'pointer'
    };

    Ext.data.Types.IMG = {
        convert: function(v, data) {
            var me=this;
            if (v == undefined) {
                return v;
            }
            if (!v.length) {
                return null
            }
            var obj = Ext.JSON.decode(v);
            if (obj && obj.id != undefined && obj.value != undefined) {
                return obj;
            }
            return null;
        },
        sortType: function(v) {
            return v.value;
        },
        type: 'img'
    };

    Ext.data.Types.FILE = {
        convert: function(v, data) {
            var me=this;
            if (v == undefined) {
                return v;
            }
            if (!v.length) {
                return null
            }
            var obj = Ext.JSON.decode(v);
            if (obj && obj.id != undefined && obj.value != undefined) {
                return obj;
            }
            return null;
        },
        sortType: function(v) {
            return v.value;
        },
        type: 'file'
    };
}