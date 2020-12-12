<?php declare(strict_types=1);

namespace Pollen\Recaptcha\Contracts;

use tiFy\Contracts\Field\FieldDriver;

interface RecaptchaField extends FieldDriver
{
    /**
     * Instance du gestionnaire reCaptcha
     *
     * @return Recaptcha
     */
    public function recaptchaManager(): Recaptcha;
}