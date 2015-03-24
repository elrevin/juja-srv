Ext.define('App.core.StaticData', {
  stores: {},
  constructor: function (config) {
    var me = this;
    Ext.Ajax.request({
      url: $url('backend', 'main', 'get-static-data'),
      success: function(response){
        var text = response.responseText;
        if (text) {
          var obj = Ext.JSON.decode(text);

          if (obj) {
            me.stores = obj;
          }
        }
      }
    });
  },
  get: function (moduleName) {
    var me = this;
    if (me.stores[moduleName]) {
      return me.stores[moduleName];
    }

    return {};
  }
});