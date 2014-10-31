Ext.define('Ext.ux.index.MainMenuTreeHeader', {
  extend: 'Ext.panel.Header',
  alias: 'widget.mainmenutreeheader',
  renderTpl: [
    '<div id="{id}-body" class="ni-mainmenu-header',
    '</div>'
  ],

//  renderTpl: [
//    '<div id="{id}-body" class="{headerCls}-body {baseCls}-body {bodyCls} {bodyTargetCls} ni-mainmenu-header',
//    '<tpl for="uiCls"> {parent.baseCls}-body-{parent.ui}-{.}</tpl>"',
//    '<tpl if="bodyStyle"> style="{bodyStyle}"</tpl>>',
//    '{%this.renderContainer(out,values)%}',
//    '</div>'
//  ]
})