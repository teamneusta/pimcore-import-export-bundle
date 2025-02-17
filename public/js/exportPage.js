pimcore.registerNS("neusta_pimcore_import_export.plugin.page.export");

neusta_pimcore_import_export.plugin.page.export = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.prepareDocumentTreeContextMenu, this.onPrepareDocumentTreeContextMenu.bind(this));
    },

    onPrepareDocumentTreeContextMenu: function (e) {
        let menu = e.detail.menu;
        let document = e.detail.document;
        // Export page into yaml file
        menu.add("-");
        menu.add(new Ext.menu.Item({
            text: t('neusta_pimcore_import_export_export_menu_label'),
            iconCls: "pimcore_icon_export",
            handler: function () {
                pimcore.helpers.download(Routing.generate('neusta_pimcore_import_export_page_export', {page_id: document.data.id}));
            }
        }));
    },

});

var pimcorePluginPageExport = new neusta_pimcore_import_export.plugin.page.export();
