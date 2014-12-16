var module = Ext.create('App.core.SingleModelEditor', {
    "fields": [{
        "name": "title",
        "title": "Название",
        "type": "string",
        "identify": true
    }, {"name": "text", "title": "Текст", "type": "text", "group": "Группа 1"}, {
        "name": "price",
        "title": "Цена",
        "type": "float",
        "settings": {"round": 2, "min": 0, "max": 20000},
        "required": true,
        "group": "Группа 1"
    }, {"name": "dt", "title": "Дата", "type": "date", "required": true, "group": "Группа 2"}, {
        "name": "flag",
        "title": "Флаг",
        "type": "bool",
        "group": "Группа 1"
    }, {"name": "dtt", "title": "Дата и время", "type": "datetime", "group": "Группа 2"}, {
        "name": "img",
        "title": "Изображение",
        "type": "img",
        "group": "Группа 2",
        "relativeModel": {
            "moduleName": "files",
            "name": "Files",
            "identifyFieldName": "title",
            "identifyFieldType": "string",
            "modalSelect": true,
            "runAction": ["files", "main", "get-interface"]
        }
    }],
    "getDataAction": ["backend", "main", "list"],
    "modelName": "TestTable",
    "userRights": 3,
    "createInterfaceForExistingParentOnly": true,
    "title": "Тестовый справочник",
    "recordTitle": "Какая-то хрень",
    "accusativeRecordTitle": "Какую-то хрень"
});
