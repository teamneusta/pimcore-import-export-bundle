# Changelog

## 2.1.0

- feature: [With or Without IDs] Export and Import should be possible without IDs (#20)

## 2.0.1

- bugfix: add controller field to converters_populators (#18)

## 2.0.0

- feature: Import Pimcore DataObjects via `/admin/neusta/import-export/object/import`
- feature: Import Pimcore Documents via `/admin/neusta/import-export/document/import`
- feature: Overwrite option for existing elements during import
- feature: Detailed result messages after import (number of replaced, not replaced, and newly imported elements)
- improvement: changed YAML format using full-qualified Pimcore type as key
- improvement: Refactored `AbstractImportBaseController` for better reusability and statistics
- improvement: Improved error handling and logging during import
- bugfix: Correct HTTP status codes and error messages for failed imports


## 1.1.0

- chore: remove teamneusta/pimcore-fixture-bundle dependency (#11)
- BC-break: If you used on of the AbstractAssetFixture or AbstractPageFixture classes from this bundle,
  use teamneusta/pimcore-fixture-bundle instead.

## 1.0.1

- bugfix: CSRF Protection is safe again and don't need to be disabled for the import route (#9)

## 1.0.0

- feature: Initial release
- feature: Export Pimcore pages to YAML files
- feature: Import Pimcore pages from YAML files
