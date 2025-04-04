pimcore.registerNS("neusta_pimcore_import_export.plugin.asset.export");

neusta_pimcore_import_export.plugin.asset.export = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.prepareAssetTreeContextMenu, this.onPrepareAssetTreeContextMenu.bind(this));
    },

    onPrepareAssetTreeContextMenu: function (e) {
        let menu = e.detail.menu;
        let asset = e.detail.asset;

        // Add menu items
        menu.add("-");
        this.addMenuItem(menu, asset, 'neusta_pimcore_import_export_export_menu_label', 'neusta_pimcore_import_export_assets_export');
        this.addMenuItem(menu, asset, 'neusta_pimcore_import_export_export_with_children_menu_label', 'neusta_pimcore_import_export_assets_export_with_children');
    },

    addMenuItem: function (menu, asset, label, route) {
        menu.add(new Ext.menu.Item({
            text: t(label),
            iconCls: "pimcore_icon_asset pimcore_icon_overlay_download",
            handler: function () {
                let defaultFilename = asset.data.key + '.yaml';
                let filename = prompt(t('neusta_pimcore_import_export_enter_filename'), defaultFilename);
                if (filename) {
                    pimcore.helpers.download(Routing.generate(route, {asset_id: asset.data.id, filename: filename, format: 'yaml'}));
                }
            }
        }));
    }
});

var pimcorePluginAssetExport = new neusta_pimcore_import_export.plugin.asset.export();
