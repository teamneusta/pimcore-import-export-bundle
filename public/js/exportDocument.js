pimcore.registerNS("neusta_pimcore_import_export.plugin.document.export");

neusta_pimcore_import_export.plugin.document.export = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.prepareDocumentTreeContextMenu, this.onPrepareDocumentTreeContextMenu.bind(this));
    },

    onPrepareDocumentTreeContextMenu: function (e) {
        let menu = e.detail.menu;
        let document = e.detail.document;

        // Add menu items
        menu.add("-");
        this.addMenuItem(menu, document, 'neusta_pimcore_import_export_export_menu_label', 'neusta_pimcore_import_export_documents_export');
        this.addMenuItem(menu, document, 'neusta_pimcore_import_export_export_with_children_menu_label', 'neusta_pimcore_import_export_documents_export_with_children');
    },

    addMenuItem: function (menu, document, label, route) {
        menu.add(new Ext.menu.Item({
            text: t(label),
            iconCls: "pimcore_icon_export",
            handler: function () {
                let defaultFilename = document.data.key + '.yaml';
                let filename = prompt(t('neusta_pimcore_import_export_enter_filename'), defaultFilename);
                if (filename) {
                    pimcore.helpers.download(Routing.generate(route, {doc_id: document.data.id, filename: filename, format: 'yaml'}));
                }
            }
        }));
    }
});

var pimcorePluginPageExport = new neusta_pimcore_import_export.plugin.document.export();
