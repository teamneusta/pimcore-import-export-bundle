pimcore.registerNS("neusta_pimcore_import_export.plugin.object.export");

neusta_pimcore_import_export.plugin.object.export = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.prepareObjectTreeContextMenu, this.onPrepareObjectTreeContextMenu.bind(this));
    },

    onPrepareObjectTreeContextMenu: function (e) {
        let menu = e.detail.menu;
        let object = e.detail.object;

        // Add menu items
        menu.add("-");
        this.addMenuItem(menu, object, 'neusta_pimcore_import_export_export_menu_label', 'pimcore_icon_object pimcore_icon_overlay_download', 'neusta_pimcore_import_export_objects_export');
        this.addMenuItem(menu, object, 'neusta_pimcore_import_export_export_with_children_menu_label', 'pimcore_icon_manyToManyObjectRelation pimcore_icon_overlay_download', 'neusta_pimcore_import_export_objects_export_with_children');
    },

    addMenuItem: function (menu, object, label, icon, route) {
        menu.add(new Ext.menu.Item({
            text: t(label),
            iconCls: icon,
            handler: function () {
                let defaultFilename = object.data.key + '.yaml';
                let filename = prompt(t('neusta_pimcore_import_export_enter_filename'), defaultFilename);
                if (filename) {
                    pimcore.helpers.download(Routing.generate(route, {object_id: object.data.id, filename: filename, format: 'yaml'}));
                }
            }
        }));
    }
});

var pimcorePluginObjectExport = new neusta_pimcore_import_export.plugin.object.export();
