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
                    // Удаление всех элементов в панели приложения
                    me._mainPanel.removeAll(true);
                }
            }

            params = {};

            if (config['modelName']) params['modelName'] = config['modelName'];
            if (config['idRecord']) params['idRecord'] = config['idRecord'];
            if (config['modal']) params['modal'] = 1;


            // Получаем конфигурацию
            Ext.Ajax.request({
                url: $url(runAction[0], runAction[1], runAction[2], params, 'js'),
                success: function (response) {
                    var code = response.responseText, eventName;
                    eval(code);
                    if (module != undefined) {
                        module.on('ready', function () {
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
            if (/^application\/json/.test(response.getResponseHeader('content-type'))) {
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

                            this.loadModule({runAction: runAction, modelName: modelName, idRecord: idRecord});
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

    },

    showErrorMessage: function (code, message) {
        Ext.Msg.show({
            title: 'Ошибка'+(code ? " #"+code : ""),
            msg: (message ? message : "Не установленная ошибка, обратитесь в техническую поддержку"),
            buttons: Ext.Msg.OK,
            icon: Ext.window.MessageBox.ERROR
        });
    }
});