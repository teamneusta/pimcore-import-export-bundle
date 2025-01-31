// /Resources/public/js/exportPage.js
pimcore.registerNS("pimcore.plugin.page.export");

pimcore.plugin.page.export = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.prepareDocumentTreeContextMenu, this.onPrepareDocumentTreeContextMenu.bind(this));
    },

    onPrepareDocumentTreeContextMenu: function (e) {
        let menu = e.detail.menu;
        let document = e.detail.document;
        // Export page into yaml file
        menu.add("-");
        menu.add(new Ext.menu.Item({
            text: 'Export to yaml',
            iconCls: "pimcore_icon_export",
            handler: function () {
                pimcore.helpers.download(Routing.generate('page_export', {page_id: document.data.id}));
            }
        }));
    },

});

var pimcorePluginPageExport = new pimcore.plugin.page.export();
