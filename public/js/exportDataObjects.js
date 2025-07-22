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
                let includeIds = !confirm(t('neusta_pimcore_import_export_exclude_ids_question')); // Yes = false, No = true

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
                                        object_id: object.data.id,
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

var pimcorePluginObjectExport = new neusta_pimcore_import_export.plugin.object.export();
