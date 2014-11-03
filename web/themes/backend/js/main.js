var cpHeadContent = document.getElementById("cp_header").innerHTML;
document.getElementById("cp_header").innerHTML = '';

Ext.application({
    name: 'IndexNextApp',

    _mainMenuStore: null,
    _mainMenuTree: null,
    _mainPanel: null,
    _currentModule: null,
    getMainPanel: function () {
        return this._mainPanel;
    },
    loadModule: function (runAction, modelName, idRecord) {
        var can = true;
        if (this._currentModule) {
            if (!this._currentModule.canDestroy()) {
                can = false;
            }
        }

        if (can) {
            //var wait = Ext.Msg.wait('Пожалуйста подождите', 'Загрузка модуля');

            if (this._currentModule) {
                // Удаление всех элементов в панели приложения
                this._mainPanel.removeAll(true);
            }

            // Получаем конфигурацию
            Ext.Ajax.request({
                url: $url(runAction[0], runAction[1], runAction[2], '', 'js'),
                params: {
                    modelName: modelName,
                    idRecord: idRecord
                },
                success: function (response) {
                    var code = response.responseText;
                    eval(code);
                    if (module != undefined) {
                        module.on('ready', function () {
                            //wait.close();
                        });
                        module.on('initfail', function () {
                            //wait.close();
                        });
                        module.init();
                    }
                    this._currentModule = module;
                },
                scope: this
            });
        }
    },
    launch: function () {
        Ext.Loader.setConfig({enabled: true});
        Ext.Loader.setPath('Ext.ux', $themeUrl('/js/ext-ux'));
        Ext.Loader.setPath('App.core', $themeUrl('/js/core'));

        doOverride();

        this._mainMenuStore = Ext.create('Ext.data.TreeStore', {
            //autoLoad: true,
            fields: ['id', 'modelName', 'idRecord', 'runAction', 'getSubTreeAction', 'title', 'isRootNode', 'icon'],
            proxy: {
                type: 'ajax',
                url: $url('backend', 'main', 'cp-menu'),
                actionMethods: {read: "POST"},
                reader: {
                    type: 'json',
                    root: 'list'
                }
            },
            listeners: {
                beforeload: {
                    fn: function (store, operation, eOpts) {
                        var param;
                        if (param = operation.node.get("modelName")) {
                            operation.params.modelName = param;
                        }

                        if (param = operation.node.get("idRecord")) {
                            operation.params.idRecord = param;
                        }

                        if (param = operation.node.get("getSubTreeAction")) {
                            store.getProxy().url = $url(param);
                        }
                    }
                }
            }
        });

        this._mainMenuTree = Ext.create('Ext.ux.index.MainMenuTree', {
            header: false,
            width: 250,
            minWidth: 50,
            collapseMode: 'mini',
            store: this._mainMenuStore,
            rootVisible: false,
            collapsible: true,
            split: true,
            region: 'west',
            hideHeaders: true,
            listeners: {
                itemclick: {
                    fn: function (p, record) {
                        var runAction = record.get('runAction');
                        if (runAction) {
                            var modelName = record.get('modelName');
                            var idRecord = record.get('idRecord');

                            this.loadModule(runAction, modelName, idRecord);
                        }
                    },
                    scope: this
                }
            }
        });

        this._mainPanel = Ext.create('Ext.panel.Panel', {
            region: 'center',
            border: false,
            header: false,
            layout: 'fit'
        });

        this.viewport = Ext.create('Ext.container.Viewport', {
            layout: 'border',
            items: [
                Ext.create('Ext.panel.Panel', {
                    region: 'north',
                    html: cpHeadContent,
                    height: 62,
                    border: false,
                    header: false
                }),
                this._mainMenuTree,
                this._mainPanel
            ],
            listeners: {
                add: function (c, i) {
                    if (i.xtype == 'bordersplitter') {
                        i.width = 13;
                        i.style = "background: #1f2021";
                    }
                }
            }
        });

    }

});
