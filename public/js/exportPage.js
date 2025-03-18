pimcore.registerNS("neusta_pimcore_import_export.plugin.page.export");

neusta_pimcore_import_export.plugin.page.export = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.prepareDocumentTreeContextMenu, this.onPrepareDocumentTreeContextMenu.bind(this));
    },

    onPrepareDocumentTreeContextMenu: function (e) {
        let menu = e.detail.menu;
        let document = e.detail.document;

        // Add menu items
        menu.add("-");
        this.addMenuItem(menu, document, 'neusta_pimcore_import_export_export_menu_label', 'neusta_pimcore_import_export_page_export');
        this.addMenuItem(menu, document, 'neusta_pimcore_import_export_export_with_children_menu_label', 'neusta_pimcore_import_export_page_export_with_children');
    },

    addMenuItem: function (menu, document, label, route) {
        menu.add(new Ext.menu.Item({
            text: t(label),
            iconCls: "pimcore_icon_export",
            handler: function () {
                let defaultFilename = document.data.key + '.yaml';
                let filename = prompt(t('neusta_pimcore_import_export_enter_filename'), defaultFilename);
                if (filename) {
                    pimcore.helpers.download(Routing.generate(route, {page_id: document.data.id, filename: filename}));
                }
            }
        }));
    }
});

var pimcorePluginPageExport = new neusta_pimcore_import_export.plugin.page.export();
