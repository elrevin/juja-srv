var module = Ext.create('App.core.RelatedModelsEditor', {
    "fields": [{
        "name": "title",
        "title": "Название",
        "type": "string",
        "identify": true
    }, {"name": "text", "title": "Текст", "type": "html"}],
    "getDataAction": ["backend", "main", "list"],
    "linkModelRunAction": null,
    "linkModelName": "",
    "modelName": "MainTable",
    "userRights": 3,
    "createInterfaceForExistingParentOnly": true,
    "title": "Тестовая главная модель",
    "recordTitle": "Главная запись",
    "accusativeRecordTitle": "Главную запись",
    "params": {"recordId": 1},
    "masterRecordId": 0,
    "sortable": false,
    "recursive": false,
    "tabClassName": "Ext.ux.index.tab.DetailPanel",
    "typeGrid": "button",
    "childModelConfig": {
        "fields": [{
            "name": "title",
            "title": "Название",
            "type": "string",
            "identify": true
        }, {"name": "price", "title": "Текст", "type": "float"}],
        "getDataAction": ["backend", "main", "list"],
        "linkModelRunAction": null,
        "linkModelName": "",
        "modelName": "",
        "userRights": 3,
        "createInterfaceForExistingParentOnly": true,
        "title": "Тестовая дочерняя модель",
        "recordTitle": "Дочерняя запись",
        "accusativeRecordTitle": "Дочернюю запись",
        "params": [],
        "masterRecordId": 0,
        "sortable": false,
        "recursive": false,
        "tabClassName": "Ext.ux.index.tab.DetailPanel",
        "typeGrid": "button",
        "childModelConfig": null
    },
    "data": {"id": "1", "title": "Категрия 1", "text": null}
});
