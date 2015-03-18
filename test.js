/**
 * Created by anna on 21.05.14.
 */

Ext.define('Classes.CCatalogCategoriesEditor', {
    extend: 'Ext.ux.index.editors.RelatedListsEditor',
    unitsSelekt: [],
    paramsFields: [],
    paramsType: [
        {'id': 'select', 'title': 'Набор значений'},
        {'id': 'string', 'title': 'Строка'},
        {'id':'int', 'title': 'Целое число'},
        {'id': 'float', 'title': 'Число с точкой'},
        {'id': 'flag', 'title': 'Флаг (да/нет)'}
    ],
    constructor: function (config) {
        var me = this;
        config.childModel = Ext.apply({
            getFormConfig: function () {
                return me.getFormConfigChild.call(me);
            }
        }, config.childModel);
        config.mainModel = Ext.apply({
            getFormConfig: function(){
                return me.getFormConfigMain.call(me);
            }
        }, config.mainModel);

        Ext.Ajax.request({
            url: '/admin/catalog/catalogadm/GetUnits?ajax=1&answer_type=json',
            success: function(response){
                var text = response.responseText;
                var res = Ext.JSON.decode(text);

                for (key in res.units) {
                    this.unitsSelekt[this.unitsSelekt.length] = res.units[key];
                }
            },
            scope: this
        });

        this.callParent(arguments);
    },

    keyPressTimeout: null,
    addImgWindow: null,
    toolbar: null,
    imagesView: null,
    priceView: null,
    parametersGrid: null,
    fasetGrid: null,

    toolbarParameters: null,
    toolbarPrice: null,

    showParametersEditWindow: function(rec){
        var
            unitsSelect = Ext.create("Ext.form.ComboBox", {
                fieldLabel: 'Единица измерения',
                labelAlign: 'top',
                displayField: 'title',
                valueField: 'id',
                width: 150,
                editable: false,
                value: (rec ? rec.get('unit_id') : ''),
                store: Ext.create('Ext.data.Store', {
                    fields: ['id', 'title'],
                    data : this.unitsSelekt
                })
            });

        var
            typeSelect = Ext.create("Ext.form.ComboBox", {
                fieldLabel: 'Тип параметра',
                labelAlign: 'top',
                displayField: 'title',
                valueField: 'id',
                width: 150,
                editable: false,
                allowBlank: false,
                blankText: 'Поле обязательно для заполнения',
                value: (rec ? rec.get('type_id') : ''),
                store: Ext.create('Ext.data.Store',{
                    fields: ['id', 'title'],
                    data: this.paramsType
                }),
                listeners: {
                    select: {
                        fn: function ( combo, records, eOpts ){
                            if(records[0].get('id') == 'select'){
                                if(rec) window.items.getAt(0).items.getAt(1).getStore().load({params : {'parameterId': rec.get('id')}});
                                window.items.getAt(0).items.getAt(1).tab.show();
                            }else
                                window.items.getAt(0).items.getAt(1).tab.hide();
                        },
                        scope: this
                    }
                }
            });

        var
            toolbar = Ext.create('Ext.toolbar.Toolbar', {
                items: [
                    {
                        text: 'Добавить',
                        scale: 'medium',
                        icon: "/themes/backend/images/buttons/plus.png",
                        handler: function () {
                            var dt = new Date();
                            window.items.getAt(0).items.getAt(1).getStore().add({
                                id: 'point'+dt.getTime(),
                                value_name: "Значение"
                            });
                        },
                        scope: this
                    }
                ]
            });

        var
            window = Ext.create('Ext.window.Window', {
                width: 400,
                height: 500,
                modal: true,
                resizable: false,
                layout: 'fit',
                title: (rec ? 'Редактирование параметра' : 'Добавление параметра'),
                items: [
                    Ext.create('Ext.tab.Panel', {
                        items: [
                            Ext.create('Ext.form.Panel', {
                                title: "Основные свойства",
                                header: false,
                                border: false,
                                bodyStyle: 'padding: 5px',
                                items: [
                                    Ext.create('Ext.form.field.Text', {
                                        width: 280,
                                        fieldLabel: "Название",
                                        labelAlign: "top",
                                        value: (rec ? rec.get('title') : ""),
                                        allowBlank: false,
                                        blankText: 'Поле обязательно для заполнения',
                                        msgTarget: 'Поле обязательно для заполнения'
                                    }),
                                    typeSelect,
                                    unitsSelect,
                                    Ext.create('Ext.form.Checkbox',{
                                        fieldLabel: 'Показывать в описании',
                                        checked: (rec ? (parseInt(rec.get('show_in_descr')) == 1 ? true : false) : false),
                                        labelWidth: getFormFieldLabelsWidth('Показывать в описании'),
                                        labelAlign: 'left'
                                    }),
                                    Ext.create('Ext.form.Checkbox',{
                                        fieldLabel: 'Использовать при поиске',
                                        checked: (rec ? (parseInt(rec.get('use_in_serch')) == 1 ? true : false) : false),
                                        labelWidth: getFormFieldLabelsWidth('Использовать при поиске'),
                                        labelAlign: 'right'
                                    }),
                                    Ext.create('Ext.form.Checkbox',{
                                        fieldLabel: 'Показывать в описании продукта (карточка)',
                                        checked: (rec ? (parseInt(rec.get('show_in_prod')) == 1 ? true : false) : false),
                                        labelWidth: getFormFieldLabelsWidth('Показывать в описании продукта (карточка)'),
                                        labelAlign: 'right'
                                    }),
                                    Ext.create('Ext.form.field.TextArea', {
                                        width: 280,
                                        fieldLabel: "Описание",
                                        labelAlign: "top",
                                        value: (rec ? rec.get('descr') : "")
                                    })
                                ]
                            }),
                            Ext.create('Ext.grid.Panel', {
                                tbar: toolbar,
                                hidden: (rec && rec.get('type_id') == 'select' ? false: true),
                                title: 'Варианты выбора',
                                header: false,
                                border: false,
                                forceFit: true,
                                columnLines: false,
                                region: 'north',
                                store: new Ext.data.ArrayStore({
                                    fields: ["id",
                                        {name: "value_id", type: "string"},
                                        {name: "value_name", type: "string"}
                                    ],
                                    data: (rec ? (rec.get('pointer') ? rec.get('pointer') : []) : [])
                                }),
                                plugins: [
                                    Ext.create('Ext.grid.plugin.CellEditing', {
                                        clicksToEdit: 1,
                                        plaginId: 'cellEditingPlugin',
                                        listeners: {
                                            edit: {
                                                fn: function (editor, e) {

                                                },
                                                scope: this
                                            }
                                        }
                                    })
                                ],
                                columns: {
                                    items: [
                                        {xtype: 'actioncolumn',
                                            width: 15,
                                            sortable: false,
                                            menuDisabled: true,
                                            items: [{
                                                icon: '/themes/backend/images/buttons/del.png',
                                                tooltip: 'Удалить значение',
                                                scope: this,
                                                handler: function(view, rowIndex, colIndex, item, e, record, row){
                                                    Ext.Msg.show({
                                                        title: 'Удаление значения параметра',
                                                        msg: 'Вы действительно хотите значение параметра?',
                                                        buttons: Ext.Msg.YESNO,
                                                        icon: Ext.Msg.WARNING,
                                                        fn: function (buttonId) {
                                                            if (buttonId == 'yes') {
                                                                Ext.Ajax.request({
                                                                    url: '/admin/catalog/catalogadm/DelParameterForPointer?ajax=1&answer_type=json',
                                                                    params: {
                                                                        id: record.get('id')
                                                                    },
                                                                    success: function(response){
                                                                        var text = response.responseText;
                                                                        var res = Ext.JSON.decode(text);
                                                                        if (res.success) {
                                                                            window.items.getAt(0).items.getAt(1).getStore().load({params : {'parameterId': rec.get('id')}});
                                                                        }
                                                                    },
                                                                    scope: this
                                                                });
                                                            }
                                                        },
                                                        scope: this
                                                    });
                                                }
                                            }]},
                                        {
                                            text: 'Значение',
                                            dataIndex: 'value_name',
                                            editor: "textfield"
                                        }
                                    ],
                                    defaults: {
                                        sortable: false,
                                        hideable: false
                                    }
                                },
                                listeners: {
                                    selectionchange: {
                                        fn: function (sm, rec) {

                                        },
                                        scope: this
                                    }
                                }
                            })
                        ]
                    })
                ],
                buttons: [
                    {
                        text: 'Сохранить',
                        handler: function () {
                            var
                                params = [];

                            window.items.getAt(0).items.getAt(1).getStore().each(function(record) {
                                params[params.length] = {
                                    id: record.get('id'),
                                    value_id: record.get('value_id'),
                                    value_name: record.get('value_name'),
                                    id_parameter: rec ?  rec.get('id') : 0
                                }
                            });
                            params = Ext.JSON.encode(params);
                            if(window.items.getAt(0).items.getAt(0).items.getAt(0).validate() && window.items.getAt(0).items.getAt(0).items.getAt(1).validate()){
                                Ext.Ajax.request({
                                    url: '/admin/catalog/catalogadm/SaveParameter?ajax=1&answer_type=json',
                                    params: {
                                        id: (rec ? rec.get('id') : 0),
                                        id_category: this.getMainModelRecordId(),
                                        title: window.items.getAt(0).items.getAt(0).items.getAt(0).getValue(),
                                        type: window.items.getAt(0).items.getAt(0).items.getAt(1).getValue(),
                                        unit: window.items.getAt(0).items.getAt(0).items.getAt(2).getValue(),
                                        descr: window.items.getAt(0).items.getAt(0).items.getAt(6).getValue(),
                                        show_in_descr: window.items.getAt(0).items.getAt(0).items.getAt(3).getValue() ? 1 : 0,
                                        use_in_serch: window.items.getAt(0).items.getAt(0).items.getAt(4).getValue() ? 1 : 0,
                                        show_in_prod: window.items.getAt(0).items.getAt(0).items.getAt(5).getValue() ? 1 : 0,
                                        params: params
                                    },
                                    success: function(response){
                                        var text = response.responseText;
                                        var res = Ext.JSON.decode(text);
                                        if (res.success) {
                                            this.parametersGrid.getStore().reload();
                                            window.close();
                                        }
                                    },
                                    scope: this
                                });
                            }
                        },
                        scope: this
                    }
                ]
            });

        window.show();
        if(rec && rec.get('type_id') == 'select'){
            window.items.getAt(0).items.getAt(1).getStore().load({params : {'parameterId': rec.get('id')}});
        }
    },

    showPriceEditWindow: function(rec){
        var

            citySelect = Ext.create("Ext.form.ComboBox", {
                fieldLabel: 'Город',
                labelAlign: 'top',
                displayField: 'title',
                valueField: 'id',
                width: 200,
                editable: false,
                store: new Ext.data.JsonStore ({
                    proxy: {
                        type: 'ajax',
                        url: "/admin/cities/citiesadm/GetCities?ajax=1&answer_type=json",
                        actionMethods:  {read: "POST"},
                        reader: {
                            type: 'json',
                            root: 'items',
                            idProperty: 'id'
                        }
                    },
                    fields: ['id', {name: 'title', type: 'string'}],
                    pageSize: 50,
                    autoLoad: true,
                    listeners: {
                        load: {
                            fn: function () {
                                //console.log(this.findRecordByDisplay());
                                if (rec)
                                    citySelect.setValue(rec.get('id_city'));
                            },
                            scope: this
                        }
                    }
                })
            });

        var
            window = Ext.create('Ext.window.Window', {
                width: 250,
                height: 200,
                modal: true,
                resizable: false,
                layout: 'fit',
                title: (rec ? 'Редактирование цены' : 'Добавление цены'),
                items: [

                    Ext.create('Ext.form.Panel', {
                        header: false,
                        border: false,
                        bodyStyle: 'padding: 5px',
                        items: [
                            citySelect,
                            Ext.create('Ext.form.field.Number', {
                                fieldLabel: 'Цена',
                                allowDecimals: true,
                                width: 200,
                                labelAlign: 'top',
                                value: (rec ? rec.get('price') : 0),
                                allowBlank: false,
                                blankText: 'Поле обязательно для заполнения',
                                msgTarget: 'Поле обязательно для заполнения'
                            })
                        ]
                    })


                ],
                buttons: [
                    {
                        text: 'Сохранить',
                        handler: function () {
                            if(window.items.getAt(0).items.getAt(0).validate() && window.items.getAt(0).items.getAt(1).validate()){
                                Ext.Ajax.request({
                                    method: 'GET',
                                    url: '/admin/catalog/productadm/SavePrice?ajax=1&answer_type=json',
                                    params: {
                                        idProduct: this.getChildModelRecordId(),
                                        idCity: window.items.getAt(0).items.getAt(0).getValue(),
                                        price: window.items.getAt(0).items.getAt(1).getValue()
                                    },
                                    success: function(response){
                                        var text = response.responseText;
                                        var res = Ext.JSON.decode(text);
                                        if (res.success) {
                                            this.priceView.getStore().reload();
                                            window.close();
                                        }
                                    },
                                    scope: this
                                });
                            }
                        },
                        scope: this
                    }
                ]
            });

        window.show();
    },

    showParamEditWindow: function (rec) {
        var field = null;
        switch (rec.get('type')) {
            case 'float':
                field = Ext.create('Ext.form.field.Number', {
                    fieldLabel: 'Значение',
                    allowDecimals: true,
                    width: 200,
                    labelAlign: 'top',
                    value: rec.get('value')
                });
                break;
            case 'int':
                field = Ext.create('Ext.form.field.Number', {
                    fieldLabel: 'Значение',
                    allowDecimals: false,
                    width: 200,
                    labelAlign: 'top',
                    value: rec.get('value')
                });
                break;
            case 'string':
                field = Ext.create('Ext.form.field.Text', {
                    fieldLabel: 'Значение',
                    allowDecimals: true,
                    width: 280,
                    labelAlign: 'top',
                    value: rec.get('value')
                });
                break;
            case 'flag':
                field = Ext.create('Ext.form.field.Checkbox', {
                    fieldLabel: rec.get('title'),
                    allowDecimals: true,
                    width: 280,
                    labelAlign: 'right',
                    checked: parseInt(rec.get('value')),
                    value: parseInt(rec.get('value'))
                });
                break;
            case 'select':
                field = Ext.create("Ext.form.ComboBox", {
                    fieldLabel: 'Значение',
                    labelAlign: 'top',
                    displayField: 'value_name',
                    valueField: 'value_id',
                    width: 280,
                    editable: false,
                    autoScroll: true,
                    store: new Ext.data.JsonStore ({
                        proxy: {
                            type: 'ajax',
                            url: "/admin/catalog/catalogadm/GetSelectValues?ajax=1&answer_type=json",
                            actionMethods:  {read: "POST"},
                            extraParams: {
                                id_parameter: rec.get('id_parameter')
                            },
                            reader: {
                                type: 'json',
                                root: 'values',
                                idProperty: 'value_id'
                            }
                        },
                        fields: ['value_id', 'value_name'],
                        pageSize: 50,
                        autoLoad: true,
                        listeners: {
                            load: {
                                fn: function () {
                                    field.setValue(rec.get('value_id'));
                                }
                            }
                        }
                    }),
                    queryMode:'remote'
                });
                break;
        }
        var window = Ext.create('Ext.window.Window', {
            width: 300,
            height: 250,
            modal: true,
            resizable: false,
            layout: 'fit',
            title: 'Изменение значения параметра',
            items: [
                Ext.create('Ext.form.Panel', {
                    header: false,
                    border: false,
                    bodyStyle: 'padding: 5px',
                    items: [
                        field,
                        Ext.create('Ext.form.field.TextArea',{
                            fieldLabel: 'Описание',
                            labelAlign: 'top',
                            name: 'descr',
                            width: 280,
                            height: 80,
                            value: rec.get('descr') ? rec.get('descr') : ''
                        })
                    ]
                })
            ],
            buttons: [
                {
                    text: 'Сохранить',
                    handler: function () {
                        Ext.Ajax.request({
                            url: '/admin/catalog/catalogadm/SetParameterValue?ajax=1&answer_type=json',
                            params: {
                                id: rec.get('id'),
                                id_parameter: rec.get('id_parameter'),
                                id_product: this.getChildModelRecordId(),
                                value: (rec.get('type') == 'flag' ? field.getValue() ? 1 : 0 : field.getValue()),
                                descr: window.items.getAt(0).items.getAt(1).getValue()
                            },
                            success: function(response){
                                var text = response.responseText;
                                var res = Ext.JSON.decode(text);
                                if (res.success) {
                                    this.parametersProductGrid.getStore().reload();
                                    window.close();
                                }
                            },
                            scope: this
                        });
                    },
                    scope: this
                }
            ]
        });
        window.show();
    },

    initAdditionsMain: function(){

        this.toolbarParameters = Ext.create('Ext.toolbar.Toolbar', {
            items: [
                {
                    text: 'Добавить',
                    scale: 'medium',
                    icon: "/themes/backend/images/buttons/plus.png",
                    handler: function () {
                        this.showParametersEditWindow(null);

                    },
                    scope: this
                }
            ]
        });

        this.toolbarPrice = Ext.create('Ext.toolbar.Toolbar', {
            items: [
                {
                    text: 'Добавить',
                    scale: 'medium',
                    icon: "/themes/backend/images/buttons/plus.png",
                    handler: function () {
                        this.showPriceEditWindow(null);

                    },
                    scope: this
                }
            ]
        });

        this.parametersGrid = Ext.create('Ext.grid.Panel',{
            tbar: this.toolbarParameters,
            header: false,
            border: false,
            forceFit: true,
            columnLines: false,
            store: new Ext.data.JsonStore({
                proxy: {
                    type: 'ajax',
                    url: '/admin/catalog/catalogadm/GetCategoryParams?ajax=1answer_type=json',
                    actionMethods: {read: 'POST'},
                    reader:{
                        type: 'json',
                        root: 'params',
                        idCategory: 'id'
                    }
                },
                fields: [
                    'id',
                    {name: 'title', type: 'string'},
                    {name: 'unit', type: 'string'},
                    {name: 'type', type: 'string'},
                    {name: 'show_in_descr', type: 'int'},
                    {name: 'use_in_serch', type: 'int'},
                    {name: 'show_in_prod', type: 'int'},
                    {name: 'descr', type: 'string'},
                    {name: 'unit_id', type: 'string'},
                    {name: 'type_id', type: 'string'},
                    {name: 'hidden', type: 'int'},
                    'pointer'
                ],
                autoload: false
            }),
            columns: {
                items: [
                    {
                        xtype: 'actioncolumn',
                        width: 50,
                        sortable: false,
                        menuDisabled: true,
                        items: [{
                            icon: '/themes/backend/images/buttons/edit.png',
                            tooltip: 'Редактировать параметр',
                            scope: this,
                            handler: function(view, rowIndex, colIndex, item, e, record, row){
                                this.showParametersEditWindow(record);

                            }
                        },{
                            icon: '/themes/backend/images/buttons/del.png',
                            tooltip: 'Удалить параметр',
                            scope: this,
                            handler: function(view, rowIndex, colIndex, item, e, record, row){
                                Ext.Msg.show({
                                    title: 'Удаление параметра из группы',
                                    msg: 'Вы действительно хотите удалить параметр из групы?',
                                    buttons: Ext.Msg.YESNO,
                                    icon: Ext.Msg.WARNING,
                                    fn: function (buttonId) {
                                        if (buttonId == 'yes') {
                                            Ext.Ajax.request({
                                                url: '/admin/catalog/catalogadm/DelParameterForCategory?ajax=1&answer_type=json',
                                                params: {
                                                    id: record.get('id')
                                                },
                                                success: function(response){
                                                    var text = response.responseText;
                                                    var res = Ext.JSON.decode(text);
                                                    if (res.success) {
                                                        this.parametersGrid.getStore().reload();
                                                    }
                                                },
                                                scope: this
                                            });
                                        }
                                    },
                                    scope: this
                                });
                            }
                        }]
                    },
                    {
                        text: 'Название',
                        dataIndex: 'title',
                        width: 100
                    },{
                        text: 'Ед. изм',
                        dataIndex: 'unit',
                        width: 100
                    },{
                        text: 'Тип',
                        dataIndex: 'type',
                        width: 100
                    },{
                        xtype: 'checkcolumn',
                        text: 'Показвать в описании',
                        dataIndex: 'show_in_descr',
                        width: 100
                    },{
                        xtype: 'checkcolumn',
                        text: 'Использовать при поиске',
                        dataIndex: 'use_in_serch',
                        width: 100
                    },{
                        xtype: 'checkcolumn',
                        text: 'Показывать в описании продукта (карточка)',
                        dataIndex: 'show_in_prod',
                        width: 100
                    },{
                        text: 'Описание параметра',
                        dataIndex: 'descr'
                    },{
                        xtype: 'checkcolumn',
                        text: 'Скрыть',
                        dataIndex: 'hidden',
                        width: 100
                    }
                ],

                defaults: {
                    sortable: false,
                    hideable: false
                }

            },
            plugins: [
                Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 1,
                    listeners: {
                        edit: {
                            fn: function(editor, e){
                                Ext.Ajax.request({
                                    url: '/admin/catalog/catalogadm/SetShortParam?ajax=1&inswer_type=json',
                                    params: {
                                        field: e.field,
                                        value: e.value ? 1 : 0,
                                        parameterId: e.record.get('id')
                                    },
                                    success: function(response){
                                        this.parametersGrid.getStore().reload();
                                    },
                                    scope: this
                                });
                            },
                            scope: this
                        }
                    }
                })
            ],
            enableDragDrop : true,
            viewConfig: {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    dragGroup: 'project-image-reorger',
                    dropGroup: 'project-image-reorger',
                    dragText: "Поместить строку на новое место"
                },
                listeners: {
                    drop: {
                        fn: function(node, data, dropRec, dropPosition) {
                            if (data && data.records.length && dropRec) {
                                Ext.Ajax.request({
                                    url: '/admin/catalog/catalogadm/SortParameters?ajax=1&answer_type=json',
                                    params: {
                                        categoryId: this.getMainModelRecordId(),
                                        paramId: data.records[0].get('id'),
                                        position: dropPosition,
                                        over: dropRec.get('id')
                                    }
                                });
                            }
                        },
                        scope: this
                    }
                }
            }
        });

        this.fasetGrid = Ext.create('Ext.grid.Panel',{
            header: false,
            border: false,
            forceFit: true,
            columnLines: false,
            store: new Ext.data.JsonStore({
                proxy: {
                    type: 'ajax',
                    url: '/admin/catalog/fasetadm/GetFasetForCategory?ajax=1answer_type=json',
                    actionMethods: {read: 'POST'},
                    reader:{
                        type: 'json',
                        root: 'items',
                        idCategory: 'id'
                    }
                },
                fields: [
                    'id',
                    {name: 'title', type: 'string'},
                    {name: 'hidden', type: 'int'}
                ],
                autoload: false
            }),
            columns: {
                items: [
                    {
                        xtype: 'actioncolumn',
                        width: 30,
                        sortable: false,
                        menuDisabled: true,
                        resizable: false,
                        items: [{
                            icon: '/themes/backend/images/buttons/del.png',
                            tooltip: 'Удалить фасетную выборку',
                            scope: this,
                            handler: function(view, rowIndex, colIndex, item, e, record, row){
                                Ext.Msg.show({
                                    title: 'Удаление фасетной выборки из категории',
                                    msg: 'Вы действительно хотите удалить фасетную выборку из категории?',
                                    buttons: Ext.Msg.YESNO,
                                    icon: Ext.Msg.WARNING,
                                    fn: function (buttonId) {
                                        if (buttonId == 'yes') {
                                            Ext.Ajax.request({
                                                url: '/admin/catalog/fasetadm/DelFasetForCategory?ajax=1&answer_type=json',
                                                params: {
                                                    id: record.get('id')
                                                },
                                                success: function(response){
                                                    var text = response.responseText;
                                                    var res = Ext.JSON.decode(text);
                                                    if (res.success) {
                                                        this.fasetGrid.getStore().reload();
                                                    }
                                                },
                                                scope: this
                                            });
                                        }
                                    },
                                    scope: this
                                });
                            }
                        }]
                    },
                    {
                        text: 'Название',
                        dataIndex: 'title',
                        width: 100
                    },{
                        xtype: 'checkcolumn',
                        text: 'Скрыть',
                        dataIndex: 'hidden',
                        width: 100
                    }
                ],

                defaults: {
                    sortable: false,
                    hideable: false
                }

            },
            plugins: [
                Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 1,
                    listeners: {
                        edit: {
                            fn: function(editor, e){
                                Ext.Ajax.request({
                                    url: '/admin/catalog/fasetadm/SetFasetField?ajax=1&inswer_type=json',
                                    params: {
                                        field: e.field,
                                        value: e.value ? 1 : 0,
                                        id: e.record.get('id')
                                    },
                                    success: function(response){
                                        this.fasetGrid.getStore().reload();
                                    },
                                    scope: this
                                });
                            },
                            scope: this
                        }
                    }
                })
            ]
        });
    },


    initAdditionsChild: function () {

        this.form = Ext.create('Ext.form.Panel', {
            region: "north",
            height: 80,
            fieldDefaults: {
                labelAlign: 'top',
                msgTarget: 'side'
            },
            items: [
                {
                    xtype: 'fieldset',
                    anchor: '100%',
                    layout: 'hbox',
                    title: 'Загрузить файл',
                    items:[{
                        xtype: 'container',
                        width: 235,
                        layout: 'anchor',
                        items: [{
                            xtype:'filefield',
                            fieldLabel: 'Файл',
                            allowBlank: false,
                            name: 'file',
                            anchor:'95%'
                        }]
                    },{
                        xtype: 'container',
                        width: 235,
                        layout: 'anchor',
                        items: [{
                            xtype:'textfield',
                            fieldLabel: 'Название',
                            allowBlank: false,
                            name: 'name',
                            anchor:'95%'
                        }]
                    },{
                        xtype: 'container',
                        flex: 1,
                        layout: 'anchor',
                        style: 'padding-top: 20px',
                        items: [{
                            xtype:'button',
                            text: 'Загрузить',
                            handler: function () {
                                if(this.form.isValid()){
                                    var
                                        selected = this.addImgWindow.items.getAt(2).getSelectionModel().getSelection(),
                                        id = selected.length ? selected[0].get('id') : this.mainModel.record['id_folder'];

                                    this.form.submit({
                                        url: '/admin/files/filesadm/addfile?ajax=1&answer_type=json',
                                        params: 'id=' + id,
                                        waitMsg: 'Загрузка...',
                                        success: function(fp, o){
                                            this.addImgWindow.items.getAt(1).items.getAt(0).getStore().reload({'params':{'folder' : id}});
                                            this.form.getForm().reset();
                                        },
                                        scope: this
                                    });
                                }
                            },
                            scope: this
                        }]
                    }]
                }
            ]
        }),

            this.addImgWindow = Ext.create('Ext.ux.index.ImageSelectWindow', {});

        this.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: [
                {
                    text: 'Добавить',
                    scale: 'medium',
                    icon: "/themes/backend/images/buttons/plus.png",
                    handler: function () {
                        this.addImgWindow.show(function (id) {
                            Ext.Ajax.request({
                                url: '/admin/catalog/catalogadm/AddImageToProduct?ajax=1&answer_type=json',
                                params: {
                                    productId: this.getChildModelRecordId(),
                                    imageId: id,
                                    oldImage: 0
                                },
                                success: function(response){
                                    var text = response.responseText;
                                    var res = Ext.JSON.decode(text);
                                    if (res.success) {
                                        this.imagesView.getStore().reload();
                                    }
                                },
                                scope: this
                            });
                        }, this);
                    },
                    scope: this
                }
            ]
        });

        this.imagesView = Ext.create('Ext.grid.Panel', {
            header: false,
            border: false,
            tbar: this.toolbar,
            forceFit: true,
            columnLines: false,
            store: new Ext.data.JsonStore ({
                proxy: {
                    type: 'ajax',
                    url: '/admin/catalog/catalogadm/getimages?ajax=1&answer_type=json',
                    actionMethods:  {read: "POST"},
                    reader: {
                        type: 'json',
                        root: 'files',
                        idProperty: 'id'
                    }
                },
                fields: ['id', {name: 'name', type: 'string'}, {name: 'url', type: 'string'}, {name: 'basic', type: 'bool'}],
                autoLoad: false
            }),
            columns: {
                items: [
                    {
                        xtype: 'actioncolumn',
                        width: 30,
                        sortable: false,
                        menuDisabled: true,
                        items: [{
                            icon: '/themes/backend/images/buttons/edit.png',
                            tooltip: 'Редактировать изображение',
                            scope: this,
                            handler: function (view, rowIndex, colIndex, item, e, record, row) {
                                this.addImgWindow.show(function (id) {
                                    Ext.Ajax.request({
                                        url: '/admin/catalog/catalogadm/AddImageToProduct?ajax=1&answer_type=json',
                                        params: {
                                            productId: this.getChildModelRecordId(),
                                            imageId: id,
                                            oldImage: record.get('id')
                                        },
                                        success: function(response){
                                            var text = response.responseText;
                                            var res = Ext.JSON.decode(text);
                                            if (res.success) {
                                                this.imagesView.getStore().reload();
                                            }
                                        },
                                        scope: this
                                    });
                                }, this);
                            }
                        },{
                            icon: '/themes/backend/images/buttons/del.png',
                            tooltip: 'Удалить изображение',
                            scope: this,
                            handler: function(view, rowIndex, colIndex, item, e, record, row){
                                Ext.Msg.show({
                                    title: 'Удаление изображения из товара',
                                    msg: 'Вы действильно хотите удалить выбранное изображение из товара?<br>(Файл изображения при этом сохранится на сервере)',
                                    buttons: Ext.Msg.YESNO,
                                    icon: Ext.Msg.WARNING,
                                    fn: function (buttonId) {
                                        if (buttonId == 'yes') {
                                            Ext.Ajax.request({
                                                url: '/admin/catalog/catalogadm/DelImageFromProduct?ajax=1&answer_type=json',
                                                params: {
                                                    productId: this.getChildModelRecordId(),
                                                    imageId: record.get('id')
                                                },
                                                success: function(response){
                                                    var text = response.responseText;
                                                    var res = Ext.JSON.decode(text);
                                                    if (res.success) {
                                                        this.imagesView.getStore().reload();
                                                    }
                                                },
                                                scope: this
                                            });
                                        }
                                    },
                                    scope: this
                                });
                            }
                        }]
                    },
                    {
                        xtype: 'checkcolumn',
                        header: 'Основное',
                        dataIndex: 'basic',
                        width: 100
                    },
                    {
                        text: 'Изображение',
                        dataIndex: 'url',
                        width: 130,
                        resizable: false,
                        renderer: function (value) {
                            return '<img src="'+value+'" style="width: 100px; height: 100px" />';
                        }
                    }, {
                        text: 'Название',
                        dataIndex: 'name',
                        width: 300
                    }
                ],
                defaults: {
                    sortable: false,
                    hideable: false
                }
            },
            plugins: [
                Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 1,
                    listeners: {
                        edit: {
                            fn: function(editor, e){
                                Ext.Ajax.request({
                                    url: '/admin/catalog/catalogadm/SetMainImage?ajax=1&inswer_type=json',
                                    params: {
                                        productId: this.getChildModelRecordId(),
                                        imageId: e.record.get('id')
                                    },
                                    success: function(response){
                                        this.imagesView.getStore().reload();
                                    },
                                    scope: this
                                });
                            },
                            scope: this
                        }
                    }
                })
            ],
            enableDragDrop : true,
            viewConfig: {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    dragGroup: 'project-image-reorger',
                    dropGroup: 'project-image-reorger',
                    dragText: "Поместить строку на новое место"
                },
                listeners: {
                    drop: {
                        fn: function(node, data, dropRec, dropPosition) {
                            if (data && data.records.length && dropRec) {
                                Ext.Ajax.request({
                                    url: '/admin/catalog/catalogadm/SortReorderImages?ajax=1&answer_type=json',
                                    params: {
                                        productId: this.getChildModelRecordId(),
                                        imageId: data.records[0].get('id'),
                                        position: dropPosition,
                                        over: dropRec.get('id')
                                    }
                                });
                            }
                        },
                        scope: this
                    }
                }
            },
            listeners: {
                selectionchange: {
                    fn: function (sm, rec) {

                    },
                    scope: this
                }
            }

        });

        this.priceView = Ext.create('Ext.grid.Panel',{
            tbar: this.toolbarPrice,
            header: false,
            border: false,
            forceFit: true,
            columnLines: false,
            store: new Ext.data.JsonStore({
                proxy: {
                    type: 'ajax',
                    url: '/admin/catalog/productadm/GetProductPrice?ajax=1answer_type=json',
                    actionMethods: {read: 'GET'},
                    reader:{
                        type: 'json',
                        root: 'items',
                        idProduct: 'id'
                    }
                },
                fields: [
                    'id',
                    {name: 'city', type: 'string'},
                    {name: 'id_city', type: 'int'},
                    {name: 'id_product', type: 'int'},
                    {name: 'price', type: 'float'}
                ],
                autoload: false
            }),
            columns: {
                items: [
                    {
                        xtype: 'actioncolumn',
                        width: 50,
                        sortable: false,
                        menuDisabled: true,
                        resizable: false,
                        items: [{
                            icon: '/themes/backend/images/buttons/edit.png',
                            tooltip: 'Редактировать цену',
                            scope: this,
                            handler: function(view, rowIndex, colIndex, item, e, record, row){
                                this.showPriceEditWindow(record);

                            }
                        },{
                            icon: '/themes/backend/images/buttons/del.png',
                            tooltip: 'Удалить цену',
                            scope: this,
                            handler: function(view, rowIndex, colIndex, item, e, record, row){
                                Ext.Msg.show({
                                    title: 'Удаление цены из товара',
                                    msg: 'Вы действительно хотите удалить цену из товара?',
                                    buttons: Ext.Msg.YESNO,
                                    icon: Ext.Msg.WARNING,
                                    fn: function (buttonId) {
                                        if (buttonId == 'yes') {
                                            Ext.Ajax.request({
                                                method: 'GET',
                                                url: '/admin/catalog/productadm/DelPriceForProduct?ajax=1&answer_type=json',
                                                params: {
                                                    idCity: record.get('id_city'),
                                                    idProduct: record.get('id_product')
                                                },
                                                success: function(response){
                                                    var text = response.responseText;
                                                    var res = Ext.JSON.decode(text);
                                                    if (res.success) {
                                                        this.priceView.getStore().reload();
                                                    }
                                                },
                                                scope: this
                                            });
                                        }
                                    },
                                    scope: this
                                });
                            }
                        }]
                    },
                    {
                        text: 'Город',
                        dataIndex: 'city',
                        width: 100
                    },{
                        text: 'Цена',
                        dataIndex: 'price',
                        width: 100
                    }
                ],

                defaults: {
                    sortable: false,
                    hideable: false
                }

            }
        });

        this.parametersProductGrid = Ext.create('Ext.grid.Panel', {
            header: false,
            border: false,
            forceFit: true,
            columnLines: false,
            store: new Ext.data.JsonStore ({
                proxy: {
                    type: 'ajax',
                    url: '/admin/catalog/catalogadm/GetProductParams?ajax=1&answer_type=json',
                    actionMethods:  {read: "POST"},
                    reader: {
                        type: 'json',
                        root: 'params',
                        idProduct: 'id'
                    }
                },
                fields: [
                    'id',
                    {name: 'id_parameter', type: 'int'},
                    {name: 'value', type: 'string'},
                    {name: 'title', type: 'string'},
                    {name: 'type', type: 'string'},
                    {name: 'unit_title', type: 'string'},
                    {name: 'value_id', type: 'string'},
                    'descr'
                ],
                autoLoad: false
            }),
            columns: {
                items: [
                    {
                        xtype: 'actioncolumn',
                        width: 10,
                        sortable: false,
                        menuDisabled: true,
                        items: [{
                            icon: '/themes/backend/images/buttons/edit.png',
                            tooltip: 'Редактировать значение параметра',
                            scope: this,
                            handler: function(view, rowIndex, colIndex, item, e, record, row){
                                this.showParamEditWindow(record);

                            }
                        }]
                    },
                    {
                        text: 'Название',
                        dataIndex: 'title',
                        width: 100
                    }, {
                        text: 'Значение',
                        dataIndex: 'value',
                        width: 100,
                        renderer: function (val, metaData, record) {
                            /*console.log(val);
                             console.log(metaData);
                             console.log(record);*/
                            if (record.get('type') == 'flag') {
                                if (parseInt(record.get('value_id'))) {
                                    return "Да"
                                } else {
                                    return "Нет"
                                }
                            }

                            if (record.get('unit') && val) {
                                val = val + record.get('unit_title');
                            }

                            return val;
                        }
                    },{
                        text: 'Описание',
                        dataIndex: 'descr'
                    }
                ],
                defaults: {
                    sortable: false,
                    hideable: false
                }
            },
            listeners: {
                selectionchange: {
                    fn: function (sm, rec) {

                    },
                    scope: this
                }
            }
        });

    },

    reloadAdditionsMain: function(){
        this.parametersGrid.getStore().load({
            params: {
                categoryId: this.getMainModelRecordId()
            }
        });

        this.fasetGrid.getStore().load({
            params: {
                categoryId: this.getMainModelRecordId()
            }
        });
    },

    reloadAdditionsChild: function(){
        this.imagesView.getStore().load({
            params: {
                projectId: this.getChildModelRecordId()
            }
        });
        this.priceView.getStore().load({
            params: {
                productId: this.getChildModelRecordId()
            }
        });
        this.parametersProductGrid.getStore().load({
            params: {
                productId: this.getChildModelRecordId(),
                categoryId: this.getMainModelRecordId()
            }
        });
    },

    onAdd: function (recordId, model, newRecord) {
        this.callParent(arguments);
        if (model == this.childModel){
            this.reloadAdditionsChild();
        }else if(model == this.mainModel){
            this.reloadAdditionsMain();
        }
        this.editorForm.tabPanel.items.getAt(1).tab.show();
        this.editorForm.tabPanel.items.getAt(2).tab.show();
    },

    getFormConfigMain: function(){
        this.initAdditionsMain();
        this.reloadAdditionsMain();

        var
            node = null,
            me = this;

        if (NextIndexApp.getApplication().treeView.getSelectionModel().getCount()) {
            node = NextIndexApp.getApplication().treeView.getSelectionModel().getSelection()[0];
        }

        return {
            overrideFields: {
                id_parent: function(config, field, record, editor){
                    var
                        url = '/admin/' + field.get_data_command + (field.get_data_command.search(/\?/) >= 0 ? '&' : '?') + 'ajax=1&answer_type=json&forSelect=1&allTree=1'+(record ? '&filter=id<>'+record['id'] : ''),
                        width = field.settings.width,
                        ret = Ext.create("Ext.ux.form.TreeCombo", {
                            fieldLabel: 'Родительская категория',
                            labelAlign: 'top',
                            displayField: 'title',
                            valueField: 'id',
                            width: 600,
                            editable: false,
                            rootVisible: false,
                            store: Ext.create('Ext.data.TreeStore', {
                                autoload: true,
                                fields:[ 'id', 'title'],
                                proxy: {
                                    type: 'ajax',
                                    url: url,
                                    actionMethods:  {read: "POST"},
                                    reader: {
                                        type: 'json',
                                        root: 'list'
                                    }
                                },
                                root: {
                                    expanded: true,
                                    title: 'Категории',
                                    id: 0
                                },
                                listeners: {
                                    load: {
                                        fn: function (t) {
                                            if (record) {
                                                if (parseInt(record['id_parent'])) {
                                                    ret.setValue(record['id_parent'] + "");
                                                } else {
                                                    ret.setValue("");
                                                    //ret.setValue(node['raw']['object_id'] + "");
                                                }
                                            } else {
                                                if (parseInt(field.default)) {
                                                    ret.setValue(field.default+"");
                                                } else {
                                                    //ret.setValue( node['raw']['object_id'] + "");
                                                    ret.setValue("");
                                                }
                                            }
                                        }
                                    }
                                }
                            }),
                            name: 'ff_'+field.name,
                            id: this._id + "_" + field.name,
                            readOnly: (record ? (field.readonly == 1 || field.readonly == '1') : false),
                            allowBlank: (field.required == 0)
                        });

                    return ret;
                }
            },
            formType: 'tab',
            formTitle: 'Основные свойства',
            tabs: [{
                title: 'Параметры',
                layout: 'fit',
                bodyCls: 'ni-images-view',
                items: [
                    this.parametersGrid
                ],
                hidden: (this.getMainModelRecordId() ? false : true)
            },{
                title: 'Фасетные выборки',
                layout: 'fit',
                bodyCls: 'ni-images-view',
                items: [
                    this.fasetGrid
                ],
                hidden: (this.getMainModelRecordId() ? false : true)
            }]
        };
    },

    getFormConfigChild: function () {
        this.initAdditionsChild();
        this.reloadAdditionsChild();
        var
            node = null,
            me = this;
        if (NextIndexApp.getApplication().treeView.getSelectionModel().getCount()) {
            node = NextIndexApp.getApplication().treeView.getSelectionModel().getSelection()[0];
        }

        return {
            overrideFields: {
                id_category: function(config, field, record, editor){
                    var
                        url = '/admin/' + field.get_data_command + (field.get_data_command.search(/\?/) >= 0 ? '&' : '?') + 'ajax=1&answer_type=json&forSelect=1&allTree=1',
                        width = field.settings.width,
                        value = (editor._mode != 'add' ? record['id_category'] + "" : me.getMainModelRecordId() + "");
                    var ret = Ext.create("Ext.ux.form.TreeCombo", {
                        fieldLabel: 'Категория',
                        labelAlign: 'top',
                        displayField: 'title',
                        valueField: 'id',
                        width: 600,
                        editable: false,
                        rootVisible: false,
                        store: Ext.create('Ext.data.TreeStore', {
                            autoload: true,
                            fields:[ 'id', 'title'],
                            proxy: {
                                type: 'ajax',
                                url: url,
                                actionMethods:  {read: "POST"},
                                reader: {
                                    type: 'json',
                                    root: 'list'
                                }
                            },
                            root: {
                                expanded: true,
                                title: 'Категории',
                                id: 0
                            },
                            listeners: {
                                load: {
                                    fn: function (t) {
                                        ret.setValue(value);
                                    }
                                }
                            }
                        }),
                        name: 'ff_'+field.name,
                        id: this._id + "_" + field.name,
                        readOnly: (record ? (field.readonly == 1 || field.readonly == '1') : false),
                        allowBlank: (field.required == 0)
                    });

                    return ret;
                }
            },
            formType: 'tab',
            formTitle: 'Основные свойства',
            tabs: [{
                title: 'Изображения',
                layout: 'fit',
                bodyCls: 'ni-images-view',
                items: [
                    this.imagesView
                ],
                hidden: (this.getChildModelRecordId() ? false : true)
            },{
                title: 'Цены',
                layout: 'fit',
                bodyCls: 'ni-images-view',
                items: [
                    this.priceView
                ],
                hidden: (this.getChildModelRecordId() ? false : true)
            },{
                title: 'Параметры',
                layout: 'fit',
                bodyCls: 'ni-images-view',
                items: [
                    this.parametersProductGrid
                ],
                hidden: (this.getChildModelRecordId() ? false : true)
            }]
        };
    }
});

function createEditor(editorType, editorName, config) {
    if (NextIndexApp.getApplication().removeModuleFromMainPanel()) {
        var config  = Ext.apply({
            name: editorName
        }, config);

        var module = Ext.create('Classes.CCatalogCategoriesEditor', config);
        NextIndexApp.getApplication().addModule(module, true);
        return module;
    }
}
