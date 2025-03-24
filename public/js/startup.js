pimcore.registerNS("neusta_pimcore_import_export.plugin");

Ext.onReady(function () {
    Ext.util.CSS.swapStyleSheet(
        "neusta_import_export_styles",
        "/bundles/neustapimcoreimportexport/css/icons.css"
    );
});
