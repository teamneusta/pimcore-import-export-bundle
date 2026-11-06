# Changelog

## Unreleased

- feature: Relations are exported and imported consistently for all element types
  - With IDs a relation is referenced by the ID of the related element, without IDs by its type, path and key
  - Data object relation fields, document properties and `relation`/`relations` editables are supported
  - Relation collections (incl. advanced relations with metadata columns) are supported
  - Import resolves relations by ID if one is given, otherwise by path and key
- bugfix: Import of data objects was not wired up (missing converter/repository/merge strategy registrations)
- bugfix: `ContextBaseTargetFactory` rejected the class name of the data object class
- bugfix: Export of relation fields no longer loses the exported relation
- bugfix: Export of assets without a concrete asset subtype no longer fails

## 3.0.0

- BC-break: bugfix: Import of exported Page Snippet documents by changing type to `Pimcore\Model\Document\Snippet`
  You have to change the key of your already exported YAML files 
  from `Pimcore\Model\Document\PageSnippet` to `Pimcore\Model\Document\Snippet`
- improvement: Refactoring of PageImportPopulator - separated into three new Populators

## 2.1.0

- feature: [With or Without IDs] Export and Import should be possible without IDs (#20)
- change: You have to change the key of already exported YAML files from `Pimcore\Model\Document` to `Pimcore\Model\Document\Page`

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
