# Theme Suite PresstiFy Plugin

[![Latest Version](https://img.shields.io/badge/release-1.0.0-blue?style=for-the-badge)](https://svn.tigreblanc.fr/pollen-solutions/recaptcha/tags/1.0.0)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)](LICENSE.md)

**Recaptcha** Component.

## Installation

```bash
composer require pollen-solutions/recaptcha
```

## Setup

### Declaration

```php
// config/app.php
return [
      //...
      'providers' => [
          //...
          \Pollen\Recaptcha\RecaptchaServiceProvider::class,
          //...
      ];
      // ...
];
```

### Configuration

```php
// config/recptcha.php
// @see /vendor/pollen-solutions/recaptcha/config/recaptcha.php
return [
      //...

      // ...
];
```
