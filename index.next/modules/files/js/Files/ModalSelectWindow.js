Ext.define('App.modules.files.Files.ModalSelectWindow', {
    extend: 'App.core.CustomModalSelectWindow',
    id: Ext.id(),
    listView: null,
    addFilePanel: null,
    windowWidth: 765,
    windowHeight: 500,
    windowResizable: false,
    toolbar: null,
    addForm: null,
    addFormPreview: null,


    getFields: function () {
        var me = this,
            fields;
        fields = me.mixins.modelLoader.getFields.call(me);
        fields[fields.length] = {
            name: 'icon',
            type: 'string',
            title: '',
            group: '',
            identify: false,
            required: true
        };
        fields[fields.length] = {
            name: 'path',
            type: 'string',
            title: '',
            group: '',
            identify: false,
            required: true
        };
        fields[fields.length] = {
            name: 'type',
            type: 'string',
            title: '',
            group: '',
            identify: false,
            required: true
        };
        return fields;
    },
    createToolbar: function () {
        var me = this,
            items = [];

        items[items.length] = Ext.create('Ext.form.field.Text', {
            width: 500,
            emptyText: 'Поиск...'
        });

        items[items.length] = {
            text: 'Добавить',
            enableToggle: true,
            icon: $assetUrl('/images/buttons/plus.png'),
            toggleHandler: function (button, state) {
                me.addFilePanel.setVisible(state);
                me.toolbar.items.getAt(3).setVisible(state);
            }
        };
        items[items.length] = '->';
        items[items.length] = {
            text: 'Загрузить',
            hidden: true,
            handler: function (button) {
                me.save();
            }
        };
        me.toolbar = me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            height: 58,
            style: "background: #f0f0f0",
            defaults: {
                scale: 'medium'
            },
            items: items
        });
    },
    save: function () {
        var me = this,
            form = me.addForm;
        if (form.isValid()) {
            form.getForm().submit({
                url: $url('files', 'main', 'save-record', [], 'tjson'),
                success: function (form, action) {
                    form.reset();
                    Ext.get(me.id + '-fields-preview-innerCt').dom.innerHTML = "";
                    me.toolbar.items.getAt(1).toggle(false);
                    me.store.reload();
                },
                failure: function (form, action) {
                    IndexNextApp.getApplication().showErrorMessage(null, action.result.message);
                }
            });
        } else {
            IndexNextApp.getApplication().showErrorMessage(null, 'Некоторые поля заполнены не правильно или не заполнены совсем.<br>Поля содержащие ошибки отмечены иконкой <img src="' + $assetUrl('/js/ext/resources/ext-theme-neptune/images/form/exclamation.png') + '" /> и красной обводкой.<br/>' +
            'Наведя мышь на иконку <img src="' + $assetUrl('/js/ext/resources/ext-theme-neptune/images/form/exclamation.png') + '" /> рядом с полем, Вы увидите пояснение ошибки.');
        }
    },
    createAddForm: function () {
        var me = this;

        me.addFormPreview = Ext.create('Ext.Panel', {
            border: false,
            header: false,
            height: 255,
            id: me.id + '-fields-preview',
            bodyStyle: "display: table-cell; background-image: url('" + $assetUrl('/images/transparent.png') + "')"
        });

        me.addForm = Ext.create('Ext.form.Panel', {
            bodyStyle: 'padding: 5px',
            items: [
                Ext.create('Ext.form.field.Hidden', {
                    name: 'id',
                    value: 0
                }),
                Ext.create('Ext.form.field.Text', {
                    width: 390,
                    allowBlank: false,
                    msgTarget: 'side',
                    labelAlign: 'top',
                    fieldLabel: 'Название',
                    name: 'title'
                }),
                Ext.create('Ext.form.field.File', {
                    fieldLabel: 'Файл',
                    allowBlank: false,
                    msgTarget: 'side',
                    labelAlign: 'top',
                    width: 390,
                    id: me.id + '-fields-file',
                    name: 'file',
                    listeners: {
                        change: function (field) {
                            me.onSelectFile(field.fileInputEl.dom.files[0]);
                        }
                    }
                }),
                me.addFormPreview
            ]
        });
    },
    onSelectFile: function (file) {
        var me = this,
            extension,
            fileType = null,
            reader;
        extension = file.name.split('.').pop();
        extension = extension.toLowerCase();
        if (me.fileTypes[extension]) {
            fileType = me.fileTypes[extension];
            if (fileType.type == 'img') {
                reader = new FileReader();
                reader.onload = function (e) {
                    // Используем URL изображения для заполнения фона
                    Ext.get(me.id + '-fields-preview-innerCt').dom.innerHTML = "<img src='" + e.target.result + "' style='max-width: 388px; max-height: 300px; background: #FFFFFF'/>";
                };
                reader.readAsDataURL(file);
            } else if (fileType.icon) {
                Ext.get(me.id + '-fields-preview-innerCt').dom.innerHTML = "<img src='/cp-files/images/files/file-types/" + fileType.icon + ".png'/>";
            }
        }
    },
    createItemsPanel: function () {
        var me = this;

        me.listView = Ext.create('Ext.view.View', {
            store: me.store,
            region: 'center',
            tpl: [
                '<tpl for=".">',
                '   <div class="thumb-wrap" id="filesItems_{id}">',
                '       <tpl if="type == \'img\'">',
                '           <div class="thumb"><img src="{icon}&width=150&height=150&bgColor=EFEFEF" title="{title:htmlEncode}" style="width: 150px; height: 150px;"></div>',
                '       <tpl else>',
                '           <div class="thumb"><img src="{icon}" title="{title:htmlEncode}" style="width: 150px; height: 150px; padding: 35px 20px; background-color: #EFEFEF"></div>',
                '       </tpl>',
                '       <span class="x-editable">{shortName:htmlEncode}</span>',
                '   </div>',
                '</tpl>',
                '<div class="x-clear"></div>'
            ],
            autoScroll: true,
            multiSelect: true,
            overItemCls: 'x-item-over',
            itemSelector: 'div.thumb-wrap',
            emptyText: 'Нет файлов',
            plugins: [
                Ext.create('Ext.ux.DataView.DragSelector', {})
            ],
            prepareData: function (data) {
                Ext.apply(data, {
                    shortName: Ext.util.Format.ellipsis(data.title, 45)
                });
                return data;
            }
        });

        me.createToolbar();
        me.createAddForm();
        me.addFilePanel = Ext.create('Ext.Panel', {
            region: 'east',
            layout: 'fit',
            width: 400,
            hideCollapseTool: true,
            //collapsed: true,
            collapsible: false,
            collapseMode: 'mini',
            border: false,
            header: false,
            hidden: true,
            items: me.addForm
        });

        me.itemsPanel = Ext.create('Ext.Panel', {
            bodyCls: 'in-data-view',
            layout: 'border',
            tbar: me.toolbar,
            items: [
                me.listView,
                me.addFilePanel
            ]
        });
    },
    selectButtonClick: function () {
        var me = this,
            sm = me.listView.getSelectionModel(),
            selected;

        if (sm.getCount() > 0) {
            selected = sm.getSelection();
            me.doSelect(selected[0]);
        }
    }
});