<div align="center">

<!-- Module Image Here -->

</div>

<h1 align="center">element119 | Indexer Deploy Config</h1>

<div align="center">

![github release](https://img.shields.io/github/v/release/pykettk/module-indexer-deploy-config?color=ffbf00&label=version)
![github release date](https://img.shields.io/github/release-date/pykettk/module-indexer-deploy-config?color=8b32a8&label=last%20release)
![license](https://img.shields.io/badge/license-OSL-ff00dd.svg)
![packagist downloads](https://img.shields.io/packagist/dt/element119/module-indexer-deploy-config?color=ff0000)

</div>

---

## 📝 Features
✔️ Allows you to selectively lock indexer modes via the `app/etc/config.php` file

✔️ Indexer configuration validated and imported as part of `app:config:import`

✔️ Supports custom indexers

✔️ Provides messaging for admins to see which indexers are locked via deploy config

✔️ Informs admins when they try to change indexer modes that are locked via deploy config

<br/>

## 🔌 Installation
Run the following command to *install* this module:
```bash
composer require element119/module-indexer-deploy-config
php bin/magento setup:upgrade
```

<br/>

## ⏫ Updating
Run the following command to *update* this module:
```bash
composer update element119/module-indexer-deploy-config
php bin/magento setup:upgrade
```

<br/>

## ❌ Uninstallation
Run the following command to *uninstall* this module:
```bash
composer remove element119/module-indexer-deploy-config
php bin/magento setup:upgrade
```

<br/>

## 📚 User Guide
### Locking Indexer Modes
1. Add a new `indexers` array to the `app/etc/config.php` file
2. Add the `realtime` or `schedule` arrays to the `indexers` array as required
3. Specify the indexer IDs you want to lock to a specific mode within the respective mode array

### Example
```php
'indexers' => [
    'realtime' => [
        'catalogrule_rule',
        'design_config_grid',
    ],
    'schedule' => [
        'catalog_category_product',
        'catalog_product_category',
        'catalog_product_attribute',
        'catalog_product_price',
    ],
],
```

<br>

> **Note**
> 
> Empty indexer mode arrays may be omitted in the cases where you don't want to lock any indexers to that mode.

<br>

## 📸 Screenshots & GIFs
### Indexer Config Validation
Coming soon...

<br>

### Indexer Config Importing
Coming soon...

<br>

### Restricted Admin Controls
![Screenshot 2022-11-06 at 19 13 09](https://user-images.githubusercontent.com/40261741/200190327-5e9f5204-d294-4a27-a27e-74fb6ea6b968.png)

<br>

### `indexer:set-mode` Messaging
Coming soon...
