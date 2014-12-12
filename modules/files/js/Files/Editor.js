Ext.define('App.modules.files.Files.Editor', {
    extend: 'App.core.Module',
    mixins: {
        modelLoader: 'Ext.ux.index.mixins.ModelLoaderWithStore'
    },
    listView: null,
    toolbar:null,
    editWindow: null,
    id: Ext.id(),
    fileTypes: {},
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
        return fields;
    },
    createEditWindow: function () {
        var me = this;
        if (me.editWindow) {
            return;
        }
        me.editWindow = Ext.create('Ext.Window', {
            title: '',
            width: 400,
            height: 550,
            modal: true,
            resizable: false,
            closeAction: 'hide',
            layout: 'fit',
            bodyStyle: 'padding: 5px',
            items: [
                {
                    xtype: 'form',
                    border: false,
                    header: false,
                    items: [
                        Ext.create('Ext.form.field.Hidden', {
                            name: 'id',
                            value: 0,
                            id: me.id+'-window-fields-id'
                        }),
                        Ext.create('Ext.form.field.Text', {
                            fieldLabel: 'Название',
                            labelAlign: 'top',
                            allowBlank: false,
                            msgTarget: 'side',
                            width: 388,
                            id: me.id+'-window-fields-title',
                            name: 'title'
                        }),
                        Ext.create('Ext.form.field.File', {
                            fieldLabel: 'Файл',
                            labelAlign: 'top',
                            width: 388,
                            id: me.id+'-window-fields-file',
                            name: 'file',
                            listeners: {
                                change: function (field, value) {
                                    me.onSelectFile(field.fileInputEl.dom.files[0]);
                                }
                            }
                        }),
                        Ext.create('Ext.Panel', {
                            border: false,
                            header: false,
                            height: 300,
                            id: me.id+'-window-fields-img',
                            bodyStyle: "display: table-cell; background-image: url('"+$themeUrl('/images/transparent.png')+"')"
                        })
                    ]
                }
            ],
            buttons: [
                {
                    text: 'Сохранить',
                    handler: function () {
                        me.save();
                    }
                }
            ]
        });
    },
    save: function () {
        var me = this,
            form = me.editWindow.items.getAt(0);
        if (form.isValid()) {
            form.getForm().submit({
                url: $url('files', 'main', 'save-record', [], 'tjson'),
                success: function(form, action) {
                    form.reset();
                    Ext.get(me.id+'-window-fields-img-innerCt').dom.innerHTML = "";
                    me.editWindow.hide();
                    me.store.reload();
                },
                failure: function(form, action) {
                    IndexNextApp.getApplication().showErrorMessage(null, action.result.message);
                }
            });
        } else {
            IndexNextApp.getApplication().showErrorMessage(null, 'Некоторые поля заполнены не правильно или не заполнены совсем.<br>Поля содержащие ошибки отмечены иконкой <img src="'+$themeUrl('/js/ext/resources/ext-theme-neptune/images/form/exclamation.png')+'" /> и красной обводкой.<br/>'+
                'Наведя мышь на иконку <img src="'+$themeUrl('/js/ext/resources/ext-theme-neptune/images/form/exclamation.png')+'" /> рядом с полем, Вы увидите пояснение ошибки.');
        }
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
                    Ext.get(me.id+'-window-fields-img-innerCt').dom.innerHTML = "<img src='"+e.target.result + "' style='max-width: 388px; max-height: 300px; background: #FFFFFF'/>";
                };
                reader.readAsDataURL(file);
            } else if (fileType.icon){
                Ext.getCmp(me.id+'-window-fields-img').getEl().innerHTML = "<img src='/cp-files/images/files/file-types/" + fileType.icon + ".png'/>";
            }
        }
    },
    addRecord: function () {
        var me = this;
        me.editWindow.setTitle('Загрузить файл');
        Ext.getCmp(me.id+'-window-fields-file').allowBlank = false;
        Ext.getCmp(me.id+'-window-fields-img').getEl().innerHTML = "<img src='/cp-files/images/files/file-types/" + fileType.icon + ".png'/>";
        me.editWindow.show();
    },
    editRecord: function () {
        var me = this,
            sm = me.listView.getSelectionModel(),
            selected;

        if (sm.getCount() == 1) {
            selected = sm.getSelection();

            me.editWindow.setTitle('Изменить файл');
            me.editWindow.show();
            Ext.getCmp(me.id+'-window-fields-file').allowBlank = false;
            Ext.getCmp(me.id+'-window-fields-file')
        }
    },
    createToolbar: function () {
        var me = this,
            buttons = [];

        if (me.userRights > 1) {
            buttons[buttons.length] = {
                xtype: 'button',
                text: 'Добавить',
                icon: $themeUrl('/images/buttons/plus.png'),
                scope: this,
                itemId: 'add',
                handler: function () {
                    me.addRecord();
                }
            };
            buttons[buttons.length] = { xtype: 'tbspacer' };
            buttons[buttons.length] = {
                xtype: 'button',
                icon: $themeUrl('/images/buttons/edit.png'),
                scope: this,
                itemId: 'edit',
                disabled: true,
                handler: function () {
                    me.editRecord();
                }
            };

            if (me.userRights > 2) {

                buttons[buttons.length] = {
                    xtype: 'button',
                    icon: $themeUrl('/images/buttons/del.png'),
                    scope: this,
                    itemId: 'del',
                    disabled: true,
                    handler: function () {
                        me.deleteRecord();
                    }
                };
            }

            me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
                height: 58,
                style: "background: #f0f0f0",
                defaults: {
                    scale: 'medium'
                },
                items: buttons
            });
            return me.toolbar;
        }
    },
    createListView: function () {
        var me = this;

        me.listView = Ext.create('Ext.view.View', {
            store: me.store,
            tpl: [
                '<tpl for=".">',
                '<div class="thumb-wrap" id="filesItems_{id}">',
                '<div class="thumb"><img src="{icon}&width=150&height=150&bgColor=EFEFEF" title="{title:htmlEncode}" style="width: 150px; height: 150px;"></div>',
                '<span class="x-editable">{shortName:htmlEncode}</span>',
                '</div>',
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
            prepareData: function(data) {
                Ext.apply(data, {
                    shortName: Ext.util.Format.ellipsis(data.title, 45)
                });
                return data;
            },
            listeners: {
                selectionchange: {
                    fn: function(dv, selected ){
                        me.toolbar.getComponent('edit').setDisabled(selected.length == 1 && me.userRights > 1);
                        me.toolbar.getComponent('del').setDisabled(selected.length && me.userRights > 2);
                    }
                }
            }
        });
    },
    init: function () {
        var me = this;

        if (me.userRights > 1) {
            if (me.getDataAction.length) {
                me.createActions();
                me.createModelClass();
                if (me.modelClassName) {
                    me.createStore();
                    me.createListView();
                    me.createEditWindow();
                    me._mainPanel = Ext.create('Ext.Panel', {
                        layout: 'fit',
                        tbar: me.createToolbar(),
                        bodyCls: 'in-data-view',
                        items: [
                            me.listView
                        ]
                    });
                    me.callParent();
                    return;
                }
            }
        }

        me.fireEvent('initfail');
    },
    destroy: function () {
        this.editWindow.closeAction = 'destroy';
        this.editWindow.close();
    }
});