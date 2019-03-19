# WIP: Work in progress
Wirecard Payment Processing Gateway Extension for OXID 6

[![License](https://img.shields.io/badge/license-GPLv3-blue.svg)](https://raw.githubusercontent.com/wirecard/oxid-ee/master/LICENSE)
[![PHP v7.0](https://img.shields.io/badge/php-v7.0-yellow.svg)](http://www.php.net)
[![PHP v7.1](https://img.shields.io/badge/php-v7.1-yellow.svg)](http://www.php.net)
[![OXID v6.1.2](https://img.shields.io/badge/OXID-v6.1-red.svg)](https://www.oxid-esales.com/)

***

## General Information
This is the Wirecard plugin for the OXID 6 e-commerce platform. Currently under development. It should NOT be used in production!

## Install / Update / Uninstall

### Install the Wirecard OXID 6 module via `composer`
To install the Wirecard OXID 6 module via composer, run:

```text
composer require wirecard/paymentgateway
```

### Update the module
To update the module installed via composer, run:

```text
composer update wirecard/paymentgateway
```

### Remove the module
To remove the module, deactivate it from the admin panel and run:

```text
composer remove wirecard/paymentgateway
rm -rf source/modules/wirecard
```
