# Inpsyde PayPal Plus Plugin

Official WordPress plugin for PayPal Plus

## Table Of Contents

* [Installation](#installation)
* [Usage](#usage)
* [Crafted by Inpsyde](#crafted-by-inpsyde)
* [License](#license)
* [Contributing](#contributing)

## Installation

The best way to use this package is through Composer:

```BASH
$ composer require inpsyde/paypal-plus-plugin
```

## Usage

* Activate Plugin
* Go to WooCommcerce -> Settings -> Checkout
* Select "PayPal Plus
* Configure with your PayPal App credentials

## Development Setup

Don't forget to run `composer install` after cloning the repository
Then, run `npm install` to set up the asset and release taskrunners.

## Create a new release

We're using composer's autoloader. So when developing, you probably have all of its devDependencies loaded and added to the autoloader.
You don't want those in the release file (it will in fact produce an error), so either keep a "release instance" of this plugin ready in another folder (-> copy the repo and then `composer install --no-dev`)
Or just checkout the production dependencies before creating a release using `composer update --no-dev`
This will remove all devDependencies, so make sure to roll back to get PHPUnit back.

To create a release, run `grunt compress`. The release will be a timestamped zip file in the `dist/` folder


## Crafted by Inpsyde

The team at [Inpsyde](http://inpsyde.com) is engineering the Web since 2006.

## License

Copyright (c) 2016 Moritz Meißelbach, Inpsyde

Good news, this plugin is free for everyone! Since it's released under the [MIT License](LICENSE) you can use it free of charge on your personal or commercial website.

## Contributing

All feedback / bug reports / pull requests are welcome.