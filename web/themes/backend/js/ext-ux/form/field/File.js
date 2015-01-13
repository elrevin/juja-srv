Ext.define('Ext.ux.form.field.File', {
    extend: 'Ext.form.FieldContainer',
    alias: 'widget.newfileselectfield',
    mixins: {
        field: 'Ext.form.field.Field'
    },
    emptyText: 'Файл не выбран',
    selButtonText: "Выбрать файл на сервере",
    uploadButtonText: "Загрузить с компьютера",
    downloadButtonText: "Скачать файл",
    seeImageButtonText: "Посмотреть изображение",

    uploadInProgressMsg: "Идет загрузка файла",
    uploadInProgressErrMsg: "Идет загрузка файла",

    allowBlank: true,
    blankText: "Это поле обязательно для заполнения",

    selButton: null,
    downloadButton: null,
    seeButton: null,
    uploadButton: null,
    uploadForm: null,

    modelName: '',
    runAction: [],
    height: 200,
    width: 500,

    uploadState: false,

    seeWindow: null,

    value: null,

    mainPanel: null,
    previewPanel: null,
    buttonsPanel: null,
    titlePanel: null,

    getFileType: function (fileName) {
        var extension,
            fileTypes = IndexNextApp.getApplication().staticData.get('files')['fileTypes'];
        extension = fileName.split('.').pop();
        extension = extension.toLowerCase();
        if (fileTypes[extension]) {
            return fileTypes[extension];
        }
        return null;
    },

    getPreviewHtml: function () {
        var me = this,
            ret = '',
            fileType,
            el = Ext.get(me.id+'-preview-cont');

        if (me.value) {
            fileType = me.getFileType(me.value.fileName);
            if (fileType && fileType.type == 'img') {
                ret = '<img src="'+$url('files', 'main', 'thumbnail', {name: me.value.fileName, width: me.previewPanel.getWidth(), height: me.previewPanel.getHeight()}, fileType.name)+'" style="width: '+me.previewPanel.getWidth()+'px; heght: '+me.previewPanel.getHeight()+'px"/>';
            } else if (fileType && fileType.icon) {
                ret = '<img src="/cp-files/images/files/file-types/' + fileType.icon + '.png" style="width: 80px; height: 60px; background-color: #FFFFFF">';
            } else if (fileType && fileType.icon) {
                ret = '<img src="/cp-files/images/files/file-types/some.png" style="width: 80px; height: 60px; background-color: #FFFFFF">';
            }

            if (el) {
                el.setWidth(me.previewPanel.getWidth());
                el.setHeight(me.previewPanel.getHeight());
            }
        }
        return ret;
    },

    onSelectFileToUpload: function (file) {
        var me = this,
            extension,
            fileTypes = IndexNextApp.getApplication().staticData.get('files')['fileTypes'],
            fileType,
            reader,
            el = Ext.get(me.id+'-preview-cont');

        if (el) {
            el.setWidth(me.previewPanel.getWidth());
            el.setHeight(me.previewPanel.getHeight());
        }

        extension = file.name.split('.').pop();
        extension = extension.toLowerCase();
        fileType = fileTypes[extension];
        if (fileType) {
            if (fileType.type == 'img') {
                reader = new FileReader();
                reader.onload = function (e) {
                    // Используем URL изображения для заполнения фона
                    Ext.get(me.id + '-preview-cont').dom.innerHTML = "<img src='" + e.target.result + "' style='max-width: " + me.previewPanel.getWidth() + "px; max-height: " + me.previewPanel.getHeight() + "px; background: #FFFFFF'/>";
                };
                reader.readAsDataURL(file);
            } else if (fileType.icon) {
                Ext.get(me.id + '-preview-cont').dom.innerHTML = "<img src='/cp-files/images/files/file-types/" + fileType.icon + ".png'/>";
            }
        } else {
            Ext.get(me.id + '-preview-cont').dom.innerHTML = "<img src='/cp-files/images/files/file-types/some.png'/>";
        }

        me.titlePanel.el.setHTML(file.name);
        me.uploadForm.items.getAt(1).setValue(file.name);
    },

    uploadFile: function () {
        var me = this,
            form = me.uploadForm,
            loader;

        me.uploadState = true;
        loader = new Ext.LoadMask(me.mainPanel.el, {msg: uploadInProgressMsg});
        loader.show();
        form.getForm().submit({
            url: $url('files', 'main', 'save-record', [], 'tjson'),
            success: function(frm, action) {
                loader.hide();
                form.getForm().reset();
                if (action.result.data) {
                    me.setValue({
                        id: action.result.data.id,
                        fileName: action.result.data.name,
                        value: action.result.data.title
                    });
                }
            },
            failure: function(form, action) {
                loader.hide();
                IndexNextApp.getApplication().showErrorMessage(null, action.result.message);
            }
        });
    },

    buildField: function () {
        var me = this;
        me.selButton = Ext.create('Ext.Button', {
            text: me.selButtonText,
            handler: function () {
                IndexNextApp.getApplication().loadModule({
                    runAction: me.runAction,
                    listeners: {
                        select: function (record) {
                            me.setValue({
                                id: record.get('id'),
                                value: record.get(me.modelField.relativeModel.identifyFieldName),
                                fileName: record.get('name')
                            });
                        }
                    },
                    modal: true,
                    modelName: me.modelName
                });
            }
        });

        me.downloadButton = Ext.create('Ext.Button', {
            text: me.downloadButtonText,
            handler: function () {
                var iframe,
                    value = me.getValue(),
                    fileType;
                if (value) {
                    // Скачиваем файл
                    iframe = Ext.get(me.id + '-download-file-frame');
                    fileType = me.getFileType(value.fileName);
                    if (iframe) {
                        iframe.dom.src = $url('files', 'main', 'get-file', {
                            name: value.fileName
                        }, fileType.name)
                    }
                }
            }
        });

        me.uploadButton = Ext.create('Ext.form.field.File', {
            buttonText: me.uploadButtonText,
            hideLabel: true,
            buttonOnly: true,
            left: 0,
            top: 0,
            name: 'file',
            listeners: {
                change: function (field) {
                    me.onSelectFileToUpload(field.fileInputEl.dom.files[0]);
                    me.uploadFile();
                }
            }
        });

        me.uploadForm = Ext.create('Ext.form.Panel', {
            header: false,
            border: false,
            height: 30,
            layout: 'absolute',
            bodyStyle: 'padding: 0; margin: 0',
            items: [
                me.uploadButton,
                Ext.create('Ext.form.field.Hidden', {
                    name: 'title'
                }),
                Ext.create('Ext.form.field.Hidden', {
                    name: 'tmp',
                    value: 1
                })
            ]
        });

        me.previewPanel = Ext.create('Ext.Panel', {
            header: false,
            border: false,
            flex: 60,
            html: '<iframe width="0" height="0" style="display: none" id="'+me.id+'-download-file-frame"></iframe><div id="'+me.id+'-preview-cont" class="in2-file-field-preview-cont">'+me.getPreviewHtml()+'</div>',
            bodyStyle: "background: url('"+$themeUrl('/images/transparent.png')+"') repeat 0 0"
        });

        me.buttonsPanel = Ext.create('Ext.Panel', {
            header: false,
            border: false,
            flex: 40,
            layout: 'anchor',
            bodyStyle: 'padding: 2px 0 2px 5px',
            items: [
                {
                    height: 30,
                    items: me.selButton
                }, {
                    height: 30,
                    items: me.downloadButton
                }, {
                    height: 30,
                    items: me.uploadForm
                }
            ]
        });
        me.titlePanel = Ext.create('Ext.Panel', {
            header: false,
            border: false,
            height: 15,
            region: 'south',
            html: (me.value ? me.value.value : me.emptyText)
        });
        me.layout = 'fit';
        me.mainPanel = Ext.create('Ext.Panel', {
            layout: 'border',
            header: false,
            bodyCls: 'in2-file-field',
            items: [
                {
                    layout: {
                        type: 'hbox',
                        align: 'stretch'
                    },
                    header: false,
                    border: false,
                    region: 'center',
                    items: [
                        me.previewPanel,
                        me.buttonsPanel
                    ]
                },
                me.titlePanel
            ]
        });
        me.items = [
            me.mainPanel
        ];
    },

    initComponent: function () {
        var me = this;
        me.buildField();
        me.callParent();
        me.initField();
    },

    isValid: function () {
        var me = this;
        if (me.uploadState) {
            me.setActiveError(me.uploadInProgressMsg);
            return false;
        }
        if (!me.value && !me.allowBlank) {
            me.setActiveError(me.blankText);
            return false;
        }
        return true;
    },

    getValue: function () {
        var me = this;
        return me.value;
    },

    setValue: function (value) {
        var me = this, el = Ext.get(me.id+'-preview-cont');
        me.value = value;

        if (value && el) {
            el.dom.innerHTML = me.getPreviewHtml();
            me.titlePanel.el.setHTML(value.value);
        } else if (el) {
            el.dom.innerHTML = '';
            me.titlePanel.el.setHTML(me.emptyText);
        }
    },

    getSubmitData: function () {
        var me = this,
            data = undefined,
            value;
        if (!me.disabled && me.submitValue) {
            var data = {};
            value = me.getValue(),
                data[me.getName()] = value ? Ext.JSON.encode(value) : null;
        }
        return data;
    }
});