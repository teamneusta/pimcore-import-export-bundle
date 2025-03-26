pimcore.registerNS("neusta_pimcore_import_export.plugin.document.import");

neusta_pimcore_import_export.plugin.document.import = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.preMenuBuild, this.preMenuBuild.bind(this));
    },

    preMenuBuild: function (e) {
        let menu = e.detail.menu;

        if (menu.extras) {
            menu.extras.items.push({
                text: t('neusta_pimcore_import_export_import_menu_label'),
                iconCls: 'icon-import-menu pimcore_icon_overlay_upload',
                hideOnClick: false,
                menu: {
                    cls: "pimcore_navigation_flyout",
                    shadow: false,
                    items: [
                        {
                            iconCls: 'pimcore_nav_icon_asset pimcore_icon_overlay_upload',
                            text: t('neusta_pimcore_import_export_import_menu_label_asset'),
                            handler: this.openImportDialog.bind(this, 'asset', 'yaml', '.zip,.yaml,.yml')
                        },
                        {
                            iconCls: 'pimcore_nav_icon_document pimcore_icon_overlay_upload',
                            text: t('neusta_pimcore_import_export_import_menu_label_document'),
                            handler: this.openImportDialog.bind(this, 'document', 'yaml', '.yaml,.yml')
                        },
                        {
                            iconCls: 'pimcore_nav_icon_object pimcore_icon_overlay_upload',
                            text: t('neusta_pimcore_import_export_import_menu_label_object'),
                            handler: this.openImportDialog.bind(this, 'object', 'yaml', '.yaml,.yml')
                        }
                    ]
                }
            });
        }
    },

    openImportDialog: function (type, format, fileTypes) {
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
                            accept: fileTypes
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
                                this.handleImport(type, format, btn);
                            }.bind(this)
                        }
                    ]
                })
            ]
        });

        uploadDialog.show();
    },

    handleImport: function (type, format, btn) {
        let form = btn.up('form').getForm();
        if (!form.isValid()) {
            return;
        }

        form.submit({
            url: Routing.generate('neusta_pimcore_import_export_' + type + '_import', {format: format}),
            method: 'POST',
            waitMsg: t('neusta_pimcore_import_export_import_dialog_wait_message'),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            params: {
                'csrfToken': parent.pimcore.settings["csrfToken"]
            },
            success: this.onImportSuccess.bind(this),
            failure: this.onImportFailure.bind(this)
        });
    },

    onImportSuccess: function (form, action) {
        let response = Ext.decode(action.response.responseText);

        let successDialog = new Ext.Window({
            title: t('neusta_pimcore_import_export_import_dialog_notification_success'),
            width: 300,
            height: 200,
            modal: true,
            layout: 'fit',
            items: [
                {
                    xtype: 'panel',
                    html: `${response.message}`,
                }
            ],
            buttons: [
                {
                    text: 'OK',
                    handler: function () {
                        successDialog.close();
                    }
                }
            ]
        });

        successDialog.show();

        pimcore.globalmanager.get('layout_document_tree').tree.getStore().reload();
        form.owner.up('window').close();
    },

    onImportFailure: function (form, action) {
        let response = Ext.decode(action.response.responseText);
        pimcore.helpers.showNotification(t('neusta_pimcore_import_export_import_dialog_notification_error'), response.message || 'Import failed', 'error');
    }
});

var pimcorePluginPageImport = new neusta_pimcore_import_export.plugin.document.import();
