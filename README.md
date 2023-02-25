<div align="center">

<!-- Module Image Here -->

</div>

<h1 align="center">element119 | Indexer Deploy Config</h1>

<h4 align="center">A Magento 2 module that allows developers to lock indexer modes via deployment config.</h4>

<br>

<div align="center">

![github release](https://img.shields.io/github/v/release/pykettk/module-indexer-deploy-config?color=ffbf00&label=version)
![github release date](https://img.shields.io/github/release-date/pykettk/module-indexer-deploy-config?color=8b32a8&label=last%20release)
![license](https://img.shields.io/badge/license-OSL-ff00dd.svg)
![packagist downloads](https://img.shields.io/packagist/dt/element119/module-indexer-deploy-config?color=ff0000)

</div>

## 📝 Features
✔️ Allows you to selectively lock indexer modes via the `app/etc/config.php` file

✔️ Indexer configuration validated and imported as part of `app:config:import`

✔️ Supports custom indexers

✔️ Provides messaging for admins to see which indexers are locked via deploy config

✔️ Informs admins when they try to change indexer modes that are locked via deploy config

✔️ Supports Magento Open Source and Adobe Commerce

✔️ Supports Hyvä and Luma based themes

✔️ Seamless integration with Magento

✔️ Built with developers and extensibility in mind to make customisations as easy as possible

✔️ Installable via Composer

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

### Indexer Mode Locking Cron Fallback
A new system configuration option allows you to enable a cron job that will ensure indexers are in the mode they are
supposed to be in, according to deployment config. This option can be found in `Stores -> Configuration -> Advanced ->
System -> Indexer Mode Locking`.

![indexer-mode-locking-cron-config](https://user-images.githubusercontent.com/40261741/221367876-d04e812d-9628-4bb2-a335-8532dd27299e.png)

<br>

### `indexer:lock-all` Command
The module adds a new `indexer:lock-all` command that you can use to lock the indexer modes via the command line.

```
Description:
  Lock all indexers

Usage:
  indexer:lock-all [options]

Options:
  -m, --mode=MODE       Passing one of two modes (schedule, realtime) will lock all indexers to that mode.
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

> **Note**
> 
> You will need to run `app:config:import` after indexer modes have been set, unset, or changed in the deploy config.
> 
> Due to the fact that this new command writes to the deploy config files and this module makes
[additions to the deploy config pool](https://github.com/pykettk/module-indexer-deploy-config/blob/master/etc/di.xml#L26-L39),
any automated deployment pipelines will need to run `app:config:import` in non-interactive mode by passing either
`-n` or `--no-interaction` as command options to avoid [the usual prompt](https://github.com/pykettk/module-indexer-deploy-config/blob/master/Model/Config/Importer.php#L72-L144).

<br>

![no command arguments](https://user-images.githubusercontent.com/40261741/200428379-36934940-cf7a-43f7-9ba3-3358dd97a0de.png)

*No arguments locks the indexer modes to their current state.*

<br>

![realtime argument](https://user-images.githubusercontent.com/40261741/200428676-cdb44054-19a8-4421-a4f8-bf9fbc93cbb6.png)

*Passing `-m realtime` as the argument sets all indexers to `Update on Save`.*

<br>

![schedule argument](https://user-images.githubusercontent.com/40261741/200428778-f4441b0d-67ec-4911-b612-ad1a47a96558.png)

*Passing `-m schedule` as the argument sets all indexers to `Update by Schedule`.*

<br>

### Restricted Admin Controls
![restrictded-admin-controls](https://user-images.githubusercontent.com/40261741/200190327-5e9f5204-d294-4a27-a27e-74fb6ea6b968.png)

<br>
