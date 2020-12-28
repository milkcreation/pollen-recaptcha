<?php

declare(strict_types=1);

namespace Pollen\Recaptcha\Field;

use Pollen\Recaptcha\Contracts\RecaptchaContract;
use tiFy\Field\FieldDriverInterface;

interface RecaptchaFieldInterface extends FieldDriverInterface
{
    /**
     * Instance du gestionnaire reCaptcha
     *
     * @return RecaptchaContract
     */
    public function recaptchaManager(): RecaptchaContract;
}