# Netgen Rich Text datatype bundle

This bundle implements legacy eZ Publish datatype counterpart of rich text field type (`ezrichtext`) from eZ Platform.

The point of the datatype is to enable eZ Publish Legacy to edit and publish content which has a rich text field.

As it is not possible to use Alloy Editor outside of eZ Platform UI, this bundle only shows the raw XML content of the field in a text area.

It is, however, possible to manually change the data in the text area and it will be persisted (together with relations and links to embedded content).

The bundle is still in prototype phase, but basic tests showed it working correctly.

## Install instructions

The `5.x` branch (`se7enxweb/richtext-datatype-bundle` 5.0) is for Exponential Platform 5.x with the
Legacy Bridge (`se7enxweb/legacy-bridge` 5.x, `se7enxweb/ngsymfonytools` v5.0).

1. Install the bundle via Composer:

    ```
    $ composer require se7enxweb/richtext-datatype-bundle:^5.0
    ```

2. Symfony Flex registers the bundle in `config/bundles.php`. Without Flex, add it there yourself:

    ```php
    Netgen\Bundle\RichTextDataTypeBundle\NetgenRichTextDataTypeBundle::class => ['all' => true],
    ```

3. Link the legacy extension and regenerate the legacy autoloads (a project's Composer scripts usually do both):

    ```
    $ php bin/console ezpublish:legacybundles:install_extensions --relative
    $ php bin/console ezpublish:legacy:script bin/php/ezpgenerateautoloads.php
    ```

   The Legacy Bridge activates the `ezrichtext` legacy extension of a registered bundle by itself;
   `ngsymfonytools` must be active as well.

4. Clear the caches:

    ```
    $ php bin/console cache:clear
    ```

5. The bundle is ready for use in Exponential Legacy: adding an `ezrichtext` attribute to classes,
   editing content with the attribute and rendering it via `attribute_view_gui`.

## Changelog

[See all changes here](CHANGELOG.md).
