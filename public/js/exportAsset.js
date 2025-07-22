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

                let win = Ext.create('Ext.window.Window', {
                    title: t('neusta_pimcore_import_export_dialog_title'),
                    modal: true,
                    width: 400,
                    layout: 'fit',
                    items: [{
                        xtype: 'form',
                        bodyPadding: 10,
                        defaults: {
                            anchor: '100%',
                            labelAlign: 'top'
                        },
                        items: [
                            {
                                xtype: 'textfield',
                                name: 'filename',
                                fieldLabel: t('neusta_pimcore_import_export_filename_label'),
                                value: defaultFilename,
                                allowBlank: false
                            },
                            {
                                xtype: 'checkbox',
                                name: 'includeIds',
                                boxLabel: t('neusta_pimcore_import_export_exclude_ids_label') +
                                    ' <span class="pimcore_object_label_icon pimcore_icon_gray_info" style="cursor: help;" data-qtip="' +
                                    t('neusta_pimcore_import_export_exclude_ids_info') + '"></span>',
                                inputValue: true
                            }
                        ]
                    }],
                    buttons: [{
                        text: t('neusta_pimcore_import_export_dialog_confirm'),
                        handler: function () {
                            let form = win.down('form').getForm();
                            if (form.isValid()) {
                                let values = form.getValues();
                                pimcore.helpers.download(
                                    Routing.generate(route, {
                                        asset_id: asset.data.id,
                                        filename: values.filename,
                                        format: 'yaml',
                                        ids_included: !!values.includeIds
                                    })
                                );
                                win.close();
                            }
                        }
                    }, {
                        text: t('neusta_pimcore_import_export_dialog_cancel'),
                        handler: function () {
                            win.close();
                        }
                    }]
                });

                win.show();
            }
        }));
    }
});

var pimcorePluginAssetExport = new neusta_pimcore_import_export.plugin.asset.export();
