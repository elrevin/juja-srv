var module = Ext.create('App.modules.backend.PointTestTable.Editor', {
    "fields": [{
        "name": "title",
        "title": "Название",
        "type": "string",
        "identify": true
    }, {
        "name": "price",
        "title": "Цена",
        "type": "float",
        "settings": {"round": 2, "min": 0, "max": 20000},
        "required": true,
        "group": "Группа полей"
    }, {
        "name": "test_table_id",
        "title": "Указатель",
        "type": "pointer",
        "relativeModel": {
            "moduleName": "backend",
            "name": "TestTable",
            "identifyFieldName": "title",
            "identifyFieldType": "string",
            "modalSelect": false,
            "runAction": ["backend", "main", "get-interface"]
        },
        "group": "Группа полей"
    }, {
        "name": "calc_test",
        "title": "НДС",
        "type": "float",
        "calc": true,
        "expression": "`point_test_table`.`price` * 0.18"
    }, {"name": "html_text", "title": "Какой-то текст HTML", "type": "html"}, {
        "name": "select_field",
        "title": "Тестовое поле Select",
        "type": "select",
        "selectOptions": {"option1": "Опция первая", "option2": "Опция вторая", "option3": "Опция третья"}
    }],
    "getDataAction": ["backend", "main", "list"],
    "linkModelRunAction": null,
    "linkModelName": "",
    "modelName": "PointTestTable",
    "userRights": 3,
    "createInterfaceForExistingParentOnly": true,
    "title": "Еще один тестовый справочник",
    "recordTitle": "",
    "accusativeRecordTitle": "",
    "params": {"recordId": 0},
    "masterRecordId": 0,
    "sortable": false,
    "recursive": false,
    "tabClassName": "Ext.ux.index.tab.DetailPanel",
    "typeGrid": "button",
    "childModelConfig": null,
    "parentModelName": "",
    "tabs": [{
        "fields": [{"name": "title", "title": "Название", "type": "string", "required": true}, {
            "name": "count",
            "title": "Количество",
            "type": "int",
            "settings": {"min": 0, "max": 20000},
            "required": true
        }, {
            "name": "price",
            "title": "Цена",
            "type": "float",
            "settings": {"min": 0, "max": 20000},
            "required": true
        }, {
            "name": "sum",
            "title": "Сумма",
            "type": "float",
            "calc": true,
            "expression": "`pttp`.`price` * `pttp`.`count`"
        }, {
            "name": "point",
            "title": "Ссылка",
            "type": "pointer",
            "relativeModel": {
                "moduleName": "backend",
                "name": "TestTable",
                "identifyFieldName": "title",
                "identifyFieldType": "string",
                "modalSelect": false,
                "runAction": ["backend", "main", "get-interface"]
            },
            "required": false,
            "showCondition": {"count": {"operation": ">", "value": 5}}
        }, {
            "name": "cool",
            "title": "Большой текст",
            "type": "text",
            "required": false,
            "showCondition": {"point": [{"operation": "set"}, {"operation": "==", "value": "Супертест"}]}
        }],
        "getDataAction": ["backend", "main", "list"],
        "linkModelRunAction": null,
        "linkModelName": "",
        "modelName": "Pttp",
        "userRights": 3,
        "createInterfaceForExistingParentOnly": true,
        "title": "Детализация тестовая",
        "recordTitle": "Штука такая",
        "accusativeRecordTitle": "Штуку такую",
        "params": [],
        "masterRecordId": 0,
        "sortable": false,
        "recursive": false,
        "tabClassName": "Ext.ux.index.tab.DetailPanel",
        "typeGrid": "button",
        "childModelConfig": null,
        "parentModelName": "",
        "className": "App.modules.backend.PointTestTable.tabs.Pttp"
    }]
});
            