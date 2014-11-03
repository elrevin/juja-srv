function mysqlTimeStampToDate(timestamp) {
    var regex = /^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/;
    var parts = timestamp.replace(regex, "$1 $2 $3 $4 $5 $6").split(' ');
    return new Date(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], parts[5]);
}

function getFormFieldLabelsWidth(text) {
    tm = new Ext.util.TextMetrics();
    return tm.getWidth(text + ":");
}

/**
 * Принимает путь относительно базового URL админки и делает из него путь относительно корня
 * @param string path
 * @returns string
 */
function $themeUrl(path) {
    return themeBaseUrl + path;
}

/**
 * Принимает в качестве аргументов путь относительно базового URL админки, дополнительные параметры (строку или объект)
 * и тип ответа (по умолчанию json)
 * @param string path
 * @param []|string params
 * @param string answerType
 * @returns string
 */
function $url(moduleName, controllerName, actionName, params, answerType) {
    var add = [],
        key;

    if (answerType == undefined || answerType == 'json') {
        actionName += ".json";
    } else if (answerType != undefined) {
        actionName += "." + answerType;
    }

    if (params) {
        if (typeof params == 'string') {
            add[add.length] = params;
        } else {
            for (key in params) {
                add[add.length] = key + "=" + params[key];
            }
        }
    }

    if (add.length) {
        add = "?" + add.join("&");
    } else {
        add = "";
    }

    return baseUrl + moduleName + '/' + controllerName + '/' + actionName + "/" + add;
}

