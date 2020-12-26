# Pollen Solutions Recaptcha Component

[![Latest Version](https://img.shields.io/badge/release-1.0.0-blue?style=for-the-badge)](https://www.presstify.com/pollen-solutions/recaptcha/)
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
// config/recaptcha.php
// @see /vendor/pollen-solutions/recaptcha/resources/config/recaptcha.php
return [
      //...

      // ...
];
```
