pimcore.registerNS("neusta_pimcore_import_export.plugin.page.import");

neusta_pimcore_import_export.plugin.page.import = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.prepareDocumentTreeContextMenu, this.onPrepareDocumentTreeContextMenu.bind(this));
    },

    onPrepareDocumentTreeContextMenu: function (e) {
        let menu = e.detail.menu;
        let document = e.detail.document;

        menu.add(new Ext.menu.Item({
            text: t('neusta_pimcore_import_export_import_menu_label'),
            iconCls: "pimcore_icon_import",
            handler: function () {
                let uploadDialog = new Ext.Window({
                    title: t('neusta_pimcore_import_export_import_dialog_title'),
                    width: 600,
                    layout: 'fit',
                    modal: true,
                    items: [
                        new Ext.form.Panel({
                            bodyPadding: 10,
                            items: [
                                {
                                    xtype: 'filefield',
                                    name: 'file',
                                    width: 450,
                                    fieldLabel: t('neusta_pimcore_import_export_import_dialog_file_label'),
                                    labelWidth: 100,
                                    allowBlank: false,
                                    buttonText: t('neusta_pimcore_import_export_import_dialog_file_button'),
                                    accept: '.yaml,.yml'
                                },
                                {
                                    xtype: 'checkbox',
                                    name: 'overwrite',
                                    fieldLabel: t('neusta_pimcore_import_export_import_dialog_overwrite_label'),
                                }
                            ],
                            buttons: [
                                {
                                    text: 'Import',
                                    handler: function (btn) {
                                        let form = btn.up('form').getForm();
                                        if (!form.isValid()) {
                                            return;
                                        }

                                        form.submit({
                                            url: Routing.generate('neusta_pimcore_import_export_page_import'),
                                            method: 'POST',
                                            waitMsg: t('neusta_pimcore_import_export_import_dialog_wait_message'),
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest' // ✅ important for AJAX-Requests
                                            },
                                            params: {
                                                'csrfToken': parent.pimcore.settings["csrfToken"]
                                            },
                                            success: function (form, action) {
                                                let response = Ext.decode(action.response.responseText);
                                                pimcore.helpers.showNotification(t('neusta_pimcore_import_export_import_dialog_notification_success'), response.message, 'success');
                                                pimcore.globalmanager.get('layout_document_tree').tree.getStore().reload();
                                                uploadDialog.close();
                                            },
                                            failure: function (form, action) {
                                                let response = Ext.decode(action.response.responseText);
                                                pimcore.helpers.showNotification(t('neusta_pimcore_import_export_import_dialog_notification_error'), response.message || 'Import failed', 'error');
                                            }
                                        });
                                    }
                                }
                            ]
                        })
                    ]
                });

                uploadDialog.show();
            }
        }));
    }
});

var pimcorePluginPageImport = new neusta_pimcore_import_export.plugin.page.import();
