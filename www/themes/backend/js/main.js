var cpHeadContent = document.getElementById("cp_header").innerHTML;
document.getElementById("cp_header").innerHTML = '';

Ext.application({
    name: 'IndexNextApp',

    _mainMenuStore: null,
    _mainMenuTree: null,
    _mainPanel: null,
    _currentModule: null,
    staticData: null,
    getMainPanel: function () {
        return this._mainPanel;
    },
    loadModule: function (config) {
        var can = true, runAction, params, listeners, me = this;

        if (config['runAction']) runAction = config['runAction'];

        if (config['listeners']) {
            listeners = config['listeners'];
        }

        if (!runAction) {
            me.showErrorMessage('', 'Действие не распознано');
        }

        if (!config['modal']) {
            if (this._currentModule) {
                if (!me._currentModule.canDestroy()) {
                    can = false;
                }
            }
        }

        if (can) {
            //var wait = Ext.Msg.wait('Пожалуйста подождите', 'Загрузка модуля');

            if (!config['modal']) {
                if (me._currentModule) {
                    me._currentModule.destroy();
                    // Удаление всех элементов в панели приложения
                    me._mainPanel.removeAll(true);
                }
            }

            params = {};
            if (config['modelName']) params['modelName'] = config['modelName'];
            if (config['id']) params['id'] = config['id'];
            if (config['modal']) params['modal'] = 1;

            // Получаем конфигурацию
            Ext.Ajax.request({
                url: $url(runAction[0], runAction[1], runAction[2], params, 'js'),
                params: {
                    params: Ext.JSON.encode(config['params'] ? config['params'] : null)
                },
                success: function (response) {
                    var code = response.responseText, eventName;
                    eval(code);
                    if (module != undefined) {
                        module.on('ready', function () {
                            if (module.setCurrentMainMenuNode && config.mainMenuNode) {
                                module.setCurrentMainMenuNode(config.mainMenuNode);
                            }
                            //wait.close();
                        });
                        module.on('initfail', function () {
                            //wait.close();
                        });

                        if (listeners) {
                            for (eventName in listeners) {
                                if (listeners[eventName]['fn']) {
                                    if (listeners[eventName]['scope']) {
                                        module.on(eventName, listeners[eventName]['fn'], listeners[eventName]['scope']);
                                    } else {
                                        module.on(eventName, listeners[eventName]['fn']);
                                    }
                                } else if (listeners[eventName] instanceof Function) {
                                    module.on(eventName, listeners[eventName]);
                                }
                            }
                        }

                        module.init();
                    }
                    if (!config['modal']) {
                        me._currentModule = module;
                    }
                }
            });
        }
    },

    launch: function () {
        Ext.Loader.setConfig({enabled: true});
        Ext.Loader.setPath('Ext.ux', $themeUrl('/js/ext-ux'));
        Ext.Loader.setPath('App.core', $themeUrl('/js/core'));
        Ext.Loader.setPath('App.modules', '/admin/getJS');

        doOverride();

        Ext.Ajax.on('requestcomplete', function (conn, response) {
            var type = '';
            if (response.getResponseHeader) {
                type = response.getResponseHeader('content-type');
            }
            if (/^application\/json/.test(type)) {
                var data = response.responseText;

                data = Ext.JSON.decode(data);
                if (data && data.success != undefined && !data.success) {
                    // Ошибка
                    IndexNextApp.getApplication().showErrorMessage(
                      (data.error != undefined ? data.error : 0),
                      (data.message != undefined ? data.message : '')
                    )
                }
                return false;
            }
        });

        this.staticData = Ext.create('App.core.StaticData', {});

        this._mainMenuStore = Ext.create('Ext.data.TreeStore', {
            //autoLoad: true,
            fields: ['id', 'modelName', 'recordId', 'runAction', 'getSubTreeAction', 'title', 'isRootNode', 'icon'],
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
                        var getSubTreeAction, modelName, idRecord;
                        getSubTreeAction = operation.node.get("getSubTreeAction");
                        modelName = operation.node.get("modelName");
                        idRecord = operation.node.get("recordId");
                        if (getSubTreeAction) {
                            store.getProxy().url = $url(
                              getSubTreeAction[0],
                              getSubTreeAction[1],
                              getSubTreeAction[2],
                              {
                                  modelName: modelName,
                                  id: idRecord
                              }
                            );
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
                        this._onMainMenuNodeSelect(record);
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

    },

    showErrorMessage: function (code, message) {
        Ext.Msg.show({
            title: 'Ошибка'+(code ? " #"+code : ""),
            msg: (message ? message : "Не установленная ошибка, обратитесь в техническую поддержку"),
            buttons: Ext.Msg.OK,
            icon: Ext.window.MessageBox.ERROR
        });
    },
    refreshMainMenuNode: function(id){
        var node;
        if (typeof id == 'integer') {
            node = this._mainMenuStore.getNodeById(id);
        } else {
            node = id;
        }
        if (node){
            this._mainMenuStore.load({node:node});
        }
    },
    _onMainMenuNodeSelect: function (record) {
        var runAction = record.get('runAction');
        if (runAction) {
            var modelName = record.get('modelName');
            var recordId = record.get('recordId');

            this.loadModule({
                runAction: runAction,
                modelName: modelName,
                id: recordId,
                mainMenuNode: record
            });
        }
    },
    selectMainMenuNode: function(id){
        var node, sm = this._mainMenuTree.getSelectionModel();
        if (typeof id == 'integer') {
            node = this._mainMenuStore.getNodeById(id);
        } else {
            node = id;
        }
        if (node){
            sm.select([node]);
            this._onMainMenuNodeSelect(node);
        }
    }
});