<?php

declare(strict_types=1);

namespace Pollen\Recaptcha\Field;

use Pollen\Field\FieldDriverInterface;
use Pollen\Recaptcha\RecaptchaInterface;

interface RecaptchaFieldInterface extends FieldDriverInterface
{
    /**
     * Recaptcha manager instance.
     *
     * @return RecaptchaInterface
     */
    public function recaptchaManager(): RecaptchaInterface;
}