<?php

declare(strict_types=1);

namespace Pollen\Recaptcha;

interface RecaptchaProxyInterface
{
    /**
     * Instance du gestionnaire reCaptcha.
     *
     * @return RecaptchaInterface
     */
    public function recaptcha(): RecaptchaInterface;

    /**
     * Définition du gestionnaire reCaptcha.
     *
     * @param RecaptchaInterface $recaptcha
     *
     * @return void
     */
    public function setRecaptcha(RecaptchaInterface $recaptcha): void;
}