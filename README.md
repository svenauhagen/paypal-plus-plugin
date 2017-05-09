# PayPal Plus for WooCommerce 

The official PayPal Plus Plugin for WooCommerce - adds payment via PayPal, Direct debit, Credit card and Pay upon invoice to your WooCommerce Shop.

## Development Setup

The best way to use this package is through Composer:

```BASH
$ composer require inpsyde/paypalplus-woocommerce
```

Don't forget to run `composer install` after cloning the repository
Then, run `npm install` to set up the asset and release taskrunners.

## Create a new release

We're using composer's autoloader. So when developing, you probably have all of its devDependencies loaded and added to the autoloader.
You don't want those in the release file (it will in fact produce an error), so either keep a "release instance" of this plugin ready in another folder (-> copy the repo and then `composer install --no-dev`)
Or just checkout the production dependencies before creating a release using `composer update --no-dev`
This will remove all devDependencies, so make sure to roll back to get PHPUnit back.

To create a release, run `grunt compress`. The release will be a timestamped zip file in the `dist/` folder
 

## Description 

PayPal Plus for WooCommerce is the official [PayPal Plus](https://www.paypal.com/de/webapps/mpp/paypal-plus) plugin. Without much effort, it integrates all PayPal Plus payment methods into your WooCommerce shop: PayPal, Direct debit, Credit card and Pay upon invoice

On the checkout page, the plugin loads an iFrame which is provided by PayPal. It offers all PayPal Plus payment methods to the buyer. Depending on their choice, the buyers are, after clicking the buy-button, guided to the PayPal form or to forms where they can enter the data to pay via Direct debit, Credit card or upon invoice. 


### Benefits 

* More payment methods: PayPal Plus enables to integrate the four most popular payment methods into your shop: PayPal, Direct debit, Credit card, pay upon invoice.
* Easier integration: all payment methods in only one plugin.
* More safety: PayPal vendor protection protects against from losing money to chargebacks and reversals for all transactions.
* Attract more customers: with or without PayPal account.
* Userfriendly and responsive checkout: made by PayPal.
* Transaction amount is directly credited to your PayPal account after transaction
* Transparent and the same fees for all payment methods - without signup- or monthly fees. 
* [PCI conformity](https://wikipedia.org/wiki/Payment_Card_Industry_Data_Security_Standard): On the checkout page, our plugin loads an iFrame being hosted by PayPal in which all transaction data are entered.


### Countries 
At the moment, **PayPal Plus** is only available for customers having their registered office in Germany. Nonetheless, international transactions work. Customers not being in Germany can only choose between the payment methods PayPal and Credit card. 


### More information about PayPal Plus 

You want to have more information about PayPal Plus? You can find all details on [PayPal’s pages](https://www.paypal.com/de/webapps/mpp/paypal-plus).


### Support 

You can find technical support for this plugin in the wordpress.org forum: [https://wordpress.org/support/plugin/woo-paypalplus](https://wordpress.org/support/plugin/woo-paypalplus)

Please read the FAQ (frequently asked questions) first and make sure you have installed the newest version of the plugin before contacting us.

**Made by [Inpsyde](https://inpsyde.com) &middot; We love WordPress**


## Installation 


### Minimum Requirements 

* WooCommerce >= 3.0
Further minimum requirements are determined by the requirements for WooCommerce:
* PHP 5.6 or greater
* MySQL 5.6 or greater
* WordPress 4.4+
* WP Memory limit of 64 MB or greater (128 MB or higher is preferred)

Furthermore, you need a PayPal business account which is activated for PayPal. [You submit the application to PayPal.](https://www.paypal.com/de/webapps/mpp/paypal-plus).


### Automatic Installation 

This is the easiest way to install the PayPal Plus plugin.

1. Log into your WordPress installation.

2. Go to the menu item *Plugins* and then to *Install*.

3. Search for *PayPal Plus for WooCommerce*. In case several plugins are listed, check if *Inpsyde* is the plugin author.

4. Click *Install Now* and wait until WordPress reports the successful installation.

5. Activate the plugin. You can find the settings here: *WooCommerce => Settings => Checkout => PayPal Plus*.


**Attention:** You need WooCommerce 3.0 or higher to use PayPal Plus. Otherwise, the setting page of the plugin is not available. You will get a notification in your WordPress backend if you don’t use the correct WooCommerce version.
 

### Manual Installation 
In case the automatic installation doesn’t work, download the plugin from here via the *Download*-button. Unpack the archive and load the folder via FTP into the directory `wp-content\plugins` of your WordPress installation. Go to *Plugins => Installed plugins* and click *Activate* on *PayPal Plus für WooCommerce*.


## Frequently Asked Questions 
 

### I installed WooCommerce in a lower version. Nevertheless, can I use PayPal Plus for WooCommerce? 
 
No, the plugin is only compatible with WooCommerce versions >= 3.0. We advise to make an update. But don’t forget to make a backup of your installation before. For making a backup use our free WordPress backup plugin [BackWPup](https://wordpress.org/plugins/backwpup/).


### What do I have to pay attention to when I use a PayPal account for several shops? 

It’s mandatory to assign a unique invoice prefix for each shop in the *PayPal Plus for WooCommerce* settings. Otherwise, PayPal won’t accept orders with the same invoice number.


### In which countries can I use PayPal Plus for WooCommerce? 

At the moment, you can use PayPal Plus for WooCommerce only in Germany.


### With PayPal Plus for WooCommerce, which payment methods can I integrate into my shop? 

With our plugin, you can integrate all those payment methods offered by PayPal: paying with your PayPal account, via direct debit, via credit card or paying via invoice.


### In my shop, the payment method pay upon invoice is not offered. What do I have to do? 

Your PayPal vendor account needs to be activated by PayPal in order to offer the payment method pay upon invoice. This takes place after verification by PayPal and may last a couple of weeks.
 

## Screenshots 
 
### 1. PayPal Plus for WooCommerce - plugin settings among WooCommerce => Settings => Checkout => PayPal Plus.
[missing image]

### 2. The four PayPal Plus payment methods: PayPal, Direct debit, credit card, pay upon invoice.
[missing image]

### 3. The PayPal Login form when paying via PayPal.
[missing image]

### 4. The PayPal Plus form when paying via Direct debit.
[missing image]

### 5. The PayPal Plus form when paying via credit card.
[missing image]

### 6. The PayPal Plus form when paying upon invoice.
[missing image]

 

 

## Changelog 
 

### 1.0 
Initial Release
 

## Upgrade Notice 
 

### 1.0 
This is the first version. Update your system when updates are available in order to ensure that the plugin works proper.

## Contributing

All feedback / bug reports / pull requests are welcome.

## Licence
License: GPLv3

License URI: http://www.gnu.org/licenses/gpl-3.0.html