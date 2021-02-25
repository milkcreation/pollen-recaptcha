# Pollen Recaptcha Component

[![Latest Version](https://img.shields.io/badge/release-1.0.0-blue?style=for-the-badge)](https://www.presstify.com/pollen-solutions/recaptcha/)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)](LICENSE.md)

Pollen **Recaptcha** Component provides a solution to integrate Google ReCaptcha v2 into your application forms.

## Installation

```bash
composer require pollen-solutions/recaptcha
```

## Basic Usage with Pollen Form

```php
use Pollen\Form\FormManager;
use Pollen\Recaptcha\Recaptcha;

$recaptcha = new Recaptcha();
$recaptcha->config(
    [
        /**
         * @var string $sitekey Recaptcha v2 Site key (required).
         */
        'sitekey'   => '=====sitekey=====',
        /**
         * @var string $secretkey Recaptcha v2 Secret key (required).
         */
        'secretkey' => '=====secretkey=====',
        /**
         * @var string|null $locale format ISO 15897.
         */
        'locale'    => 'fr_FR'
    ]
);

$forms = new FormManager();

$form = $forms->buildForm(
    [
        'alias'  => 'recaptcha-form',
        'fields' => [
            'email'   => [
                'type' => 'text',
            ],
            'captcha' => [
                'type' => 'recaptcha',
            ],
        ],
    ]
)->get();

echo <<< HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
$form
HTML;

// A ReCaptcha widget was automatically added by from render
$jsScripts = $recaptcha->getJsScripts();

echo <<< HTML
$jsScripts
</body>
</html>
HTML;
exit;
```

### Wordpress required Usage

```php
use Pollen\Recaptcha\Recaptcha;

$recaptcha = Recaptcha::getInstance();

// Add Js scripts into the footer
add_action('wp_print_footer_scripts', function () use ($recaptcha) {
  echo $recaptcha->getJsScripts();
});
```

## Pollen Framework Setup

### Declaration

```php
// config/app.php
use Pollen\Recaptcha\RecaptchaServiceProvider;

return [
      //...
      'providers' => [
          //...
          RecaptchaServiceProvider::class,
          //...
      ]
      // ...
];
```

### Configuration

```php
// config/recaptcha.php
// @see /vendor/pollen-solutions/recaptcha/resources/config/recaptcha.php.stub
return [
    /**
     * @var string $sitekey Recaptcha v2 Site key (required).
     */
    'sitekey'   => '=====sitekey=====',
    /**
     * @var string $secretkey Recaptcha v2 Secret key (required).
     */
    'secretkey' => '=====secretkey=====',
    /**
     * @var string|null $locale format ISO 15897.
     */
    'locale'    => null,
];
```
