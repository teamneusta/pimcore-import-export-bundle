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
        this.addMenuItem(menu, document, 'neusta_pimcore_import_export_export_menu_label', 'pimcore_icon_document pimcore_icon_overlay_download', 'neusta_pimcore_import_export_documents_export');
        this.addMenuItem(menu, document, 'neusta_pimcore_import_export_export_with_children_menu_label', 'pimcore_icon_document pimcore_icon_overlay_download', 'neusta_pimcore_import_export_documents_export_with_children');
    },

    addMenuItem: function (menu, document, label, icon, route) {
        menu.add(new Ext.menu.Item({
            text: t(label),
            iconCls: icon,
            handler: function () {
                let defaultFilename = document.data.key + '.yaml';

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
                                        doc_id: document.data.id,
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

        // let filename = prompt(t('neusta_pimcore_import_export_enter_filename'), defaultFilename);
        //         if (filename) {
        //             pimcore.helpers.download(Routing.generate(route, {doc_id: document.data.id, filename: filename, format: 'yaml', ids_included: includeIds}));
        //         }
        //     }
        // }));
    }
});

var pimcorePluginPageExport = new neusta_pimcore_import_export.plugin.document.export();
