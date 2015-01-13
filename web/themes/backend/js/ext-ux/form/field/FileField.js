Ext.define('Ext.ux.form.field.FileField', {
    extend: 'Ext.form.field.Base',
    alias: 'widget.fileselectfield',
    selButtonText: "Выбрать файл на сервере",
    uploadButtonText: "Загрузить с компьютера",
    downloadButtonText: "Скачать файл",
    seeImageButtonText: "Посмотреть изображение",

    modelName: '',
    runAction: [],
    height: 120,
    width: 400,

    uploadState: true,

    seeWindow: null,

    fieldSubTpl: [
        '<div style="width: {width}px; height: {height}px;" id="{cmpId}" class="in2-file-field">' +
        '   <div class="loader" style="width: {width}px; height: {height}px;" id="{cmpId}-loader"></div>'+
        '   <iframe width="0" height="0" style="display: none" id="{cmpId}-download-file-frame"></iframe>' +
        '   <input id="{id}" type="hidden" name="" value="" />' +
        '   <div id="{cmpId}-preview-cont" class="in2-file-field-preview-cont" style="height: {innerHeight}px;">' +
        '       <tpl if="fileName">' +
        '           <img src="/admin/files/main/thumbnail.png?name={fileName}&width={imgWidth}&height={innerHeight}">' +
        '       </tpl>' +
        '   </div>' +
        '   <div class="in2-file-field-buttons-cont" id="{cmpId}-buttons-cont">' +
        '       <div id="{cmpId}-select-file-from-server-button-cont"></div>' +
        '       <div id="{cmpId}-upload-file-button-cont"></div>' +
        '       <div id="{cmpId}-download-file-button-cont"></div>' +
        '       <div id="{cmpId}-see-image-button-cont" style="display: none"></div>' +
        '   </div>' +
        '   <br clear="all" />' +
        '   <tpl if="title">' +
        '       <div class="in2-file-field-title" id="{cmpId}-title">{title}</div>' +
        '   <tpl else>' +
        '       <div class="in2-file-field-title" id="{cmpId}-title">Файл не выбран</div>' +
        '   </tpl>' +
        '</div>'
    ],
    inputType: 'hidden',
    constructor: function (conf) {
        var me = this;
        me.callParent(arguments);
        if (me.value) {
            me.value = parseInt(me.value);
        }
    },

    selButton: null,
    downloadButton: null,
    seeButton: null,
    uploadButton: null,
    uploadForm: null,

    isValid: function () {
        var me = this;
        if (me.uploadState) {
            me.setActiveError('ERROR');
            return false;
        }
        return me.callParent(arguments);
    },

    getSubTplData: function () {
        var me = this,
            type = me.inputType,
            inputId = me.getInputId(),
            data,
            value = me.getValue(),
            fileName = null,
            title = '';

        if (value) {
            fileName = value.fileName
            title = value.value
        }
        data = Ext.apply({
            id: inputId,
            cmpId: me.id,
            name: me.name || inputId,
            disabled: me.disabled,
            readOnly: me.readOnly,
            value: value,
            fileName: fileName,
            type: type,
            tabIdx: me.tabIndex,
            width: me.width,
            height: parseInt(me.height),
            innerHeight: parseInt(me.height) - 18,
            imgWidth: Math.floor(me.width * 0.4),
            title: title
        }, me.subTplData);

        me.getInsertionRenderData(data, me.subTplInsertions);

        return data;
    },
    onSelectFileToUpload: function (file) {
        var me = this,
            extension,
            fileTypes = IndexNextApp.getApplication().staticData.get('files')['fileTypes'],
            fileType,
            reader;
        extension = file.name.split('.').pop();
        extension = extension.toLowerCase();
        fileType = fileTypes[extension];
        if (fileType) {
            if (fileType.type == 'img') {
                reader = new FileReader();
                reader.onload = function (e) {
                    // Используем URL изображения для заполнения фона
                    Ext.get(me.id + '-preview-cont').dom.innerHTML = "<img src='" + e.target.result + "' style='max-width: " + Math.floor(me.width * 0.4) + "px; max-height: " + (me.height - 18) + "px; background: #FFFFFF'/>";
                };
                reader.readAsDataURL(file);
            } else if (fileType.icon) {
                Ext.get(me.id + '-preview-cont').dom.innerHTML = "<img src='/cp-files/images/files/file-types/" + fileType.icon + ".png'/>";
            }
        } else {
            Ext.get(me.id + '-preview-cont').dom.innerHTML = "<img src='/cp-files/images/files/file-types/some.png'/>";
        }

        Ext.get(me.id + '-title').dom.innerHTML = file.name;
        me.uploadForm.items.getAt(1).setValue(file.name);
    },
    uploadFile: function () {
        var me = this,
            form = me.uploadForm,
            loader = Ext.get(me.id+'-loader');

        me.uploadState = true;
        loader.setVisible(true);
        form.getForm().submit({
            url: $url('files', 'main', 'save-record', [], 'tjson'),
            success: function(frm, action) {
                loader.setVisible(false);
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
                loader.setVisible(false);
                IndexNextApp.getApplication().showErrorMessage(null, action.result.message);
            }
        });
    },
    onRender: function () {
        var me = this,
            value = me.getValue(),
            fileType = null;

        me.callParent();

        if (value) {
            fileType = me.getFileType(value.fileName);
        }
        me.selButton = Ext.create('Ext.Button', {
            text: me.selButtonText,
            renderTo: me.id + '-select-file-from-server-button-cont',
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
            renderTo: me.id + '-download-file-button-cont',
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
            width: Math.floor(me.width * 0.6) - 5,
            renderTo: me.id + '-upload-file-button-cont',
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
        me.seeButton = Ext.create('Ext.Button', {
            text: me.seeImageButtonText,
            renderTo: me.id + '-see-image-button-cont',
            hidden: true,//!(fileType && fileType.type == 'img'),
            handler: function () {
                var value = me.getValue(),
                    fileType,
                    window;
                if (value) {
                    fileType = me.getFileType(value.fileName);

                    if (fileType.type == 'img') {
                        window = Ext.create('Ext.Window', {
                            modal: true,
                            html: '<div id="' + me.id + '-wnd-preview"></div>',
                            listeners: {
                                render: function () {

                                }
                            }
                        });
                    }
                }
            }
        });
    },
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
    setPreview: function (value) {
        var me = this,
            previewCont,
            titleCont,
            fileType;
        previewCont = Ext.get(me.id + '-preview-cont');
        titleCont = Ext.get(me.id + '-title');
        if (titleCont && value) {
            titleCont.dom.innerHTML = value.value;
        }
        if (previewCont) {
            if (value) {
                fileType = me.getFileType(value.fileName);
                if (fileType && fileType.type == 'img') {
                    previewCont.dom.innerHTML = '<img src="/admin/files/main/thumbnail.png?name=' + value.fileName + '&width=' + Math.floor(me.width * 0.4) + '&height=' + (me.height - 18) + '" style="margin: 0 auto;">';
                } else if (fileType && fileType.icon) {
                    previewCont.dom.innerHTML = '<img src="/cp-files/images/files/file-types/' + fileType.icon + '.png" style="width: 80px; height: 60px; background-color: #FFFFFF">';
                } else {
                    previewCont.dom.innerHTML = '<img src="/cp-files/images/files/file-types/some.png" style="width: 80px; height: 60px; background-color: #FFFFFF">';
                }
            } else {
                // Пустое значение
                previewCont.dom.innerHTML = '';
            }
        }
    },
    setValue: function (value) {
        var me = this;
        me.setPreview(value);
        me.callParent(arguments);
    },
    getValue: function () {
        var me = this;
        return me.value;
    }
});