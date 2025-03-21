pimcore.registerNS("neusta_pimcore_import_export.plugin.document.import");

neusta_pimcore_import_export.plugin.document.import = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.preMenuBuild, this.preMenuBuild.bind(this));
    },

    preMenuBuild: function (e) {
        let menu = e.detail.menu;

        menu.neusta_pimcore_import_export = {
            label: t('neusta_pimcore_import_export_import_menu_label'),
            iconCls: 'pimcore_icon_import',
            priority: 50,
            handler: this.openImportDialog.bind(this),
            noSubmenus: true
        };
    },

    openImportDialog: function () {
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
                            handler: this.handleImport.bind(this)
                        }
                    ]
                })
            ]
        });

        uploadDialog.show();
    },

    handleImport: function (btn) {
        let form = btn.up('form').getForm();
        if (!form.isValid()) {
            return;
        }

        form.submit({
            url: Routing.generate('neusta_pimcore_import_export_document_import', {format: 'yaml'}),
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
