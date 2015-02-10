Ext.define('Ext.ux.index.form.FieldSet', {
    extend: 'Ext.form.FieldSet',
    alias: 'widget.indexfieldset',
    createLegendCt: function () {
        var me = this,
            items = [],
            legend = {
                xtype: 'container',
                baseCls: me.baseCls + '-header',
                id: me.id + '-legend',
                autoEl: 'div',
                items: items,
                ownerCt: me,
                shrinkWrap: true,
                ownerLayout: me.componentLayout
            };

        // Title
        items.push(me.createTitleCmp());

        // Checkbox
        if (me.checkboxToggle) {
            items.push(me.createCheckboxCmp());
        } else if (me.collapsible) {
            // Toggle button
            items.push(me.createToggleCmp());
        }

        return legend;
    },
    doRenderLegend: function (out, renderData) {
        // Careful! This method is bolted on to the renderTpl so all we get for context is
        // the renderData! The "this" pointer is the renderTpl instance!

        var me = renderData.$comp,
            legend = me.legend,
            tree;

        // Create the Legend component if needed
        if (legend) {
            legend.ownerLayout.configureItem(legend);
            tree = legend.getRenderTree();
            Ext.DomHelper.generateMarkup(tree, out);
        }
    }
});
