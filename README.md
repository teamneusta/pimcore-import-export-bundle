# Pimcore Import Export Bundle

## Installation

1.  **Require the bundle**

    ```shell
    composer require teamneusta/pimcore-import-export-bundle
    ```

2. **Enable the bundle**

    Add the Bundle to your `config/bundles.php`:

   ```php
   Neusta\Pimcore\ImportExportBundle\NeustaPimcoreImportExportBundle::class => ['all' => true],
   ```

## Usage

After enabling the bundle you should see a new menu item in the context menu of Pimcore Admin Backend - Section Documents:

![context_menu_import_export.png](docs/images/context_menu_import_export.png)

(german translation)

### Page Export
The selected page will be exported into YAML format:
```yaml
page:
    id: 123
    parentId: 1
    type: page
    published: true
    path: /
    language: de
    navigation_name: my-site
    navigation_title: 'My Site'
    key: my-site
    title: 'My Site'
    controller: 'App\DefaultController::indexAction'
    editables:
        main:
            type: areablock
            name: main
            data: [{ key: '1', type: text-editor, hidden: false }]
...
```  

In the same way you can re-import your yaml file again by selecting: `Import from YAML` in the context menu.

## Configuration

### Page Import

The import process will create a new page with the given data.

## Contribution

Feel free to open issues for any bug, feature request, or other ideas.

Please remember to create an issue before creating large pull requests.

### Local Development

To develop on your local machine, the vendor dependencies are required.

```shell
bin/composer install
```

We use composer scripts for our main quality tools. They can be executed via the `bin/composer` file as well.

```shell
bin/composer cs:fix
bin/composer phpstan
bin/composer tests
```
