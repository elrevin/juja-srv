var module = Ext.create('App.core.SingleModelEditor', {
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
            "modalSelect": true,
            "runAction": ["backend", "main", "get-interface"]
        },
        "group": "Группа полей"
    }],
    "getDataAction": ["backend", "main", "list"],
    "modelName": "PointTestTable",
    "userRights": 3,
    "createInterfaceForExistingParentOnly": true,
    "title": "Еще один тестовый справочник",
    "recordTitle": "",
    "accusativeRecordTitle": "",
    "tabs": [{
        "fields": [{"name": "title", "title": "Название", "type": "string", "required": true}, {
            "name": "count",
            "title": "Количество",
            "type": "int",
            "settings": {"min": 0, "max": 20000},
            "required": true
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
            "required": true
        }, {"name": "cool", "title": "Большой текст", "type": "text", "required": true}],
        "getDataAction": ["backend", "main", "list"],
        "modelName": "Pttp",
        "userRights": 3,
        "createInterfaceForExistingParentOnly": true,
        "title": "Детализация тестовая",
        "recordTitle": "Штука такая",
        "accusativeRecordTitle": "Штуку такую"
    }]
});
        