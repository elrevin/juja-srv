function doOverride() {
    Ext.override(Ext.resizer.Splitter, {
        size: 13,
        style: {
            background: "url('" + $themeUrl("/images/extOverride/splitter-bg.png") + "')"
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
        createNew: function () {
            return Ext.create(this.$className, {});
        }
    });

    Ext.override(Ext.data.Field, {
        title: '',
        group: '',
        required: false,
        identify: false,
        relationModel: {
            name: '',
            moduleName: ''
        }
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

}