<?php

declare(strict_types=1);

namespace Pollen\Recaptcha;

interface RecaptchaProxyInterface
{
    /**
     * Resolve Recaptcha instance.
     *
     * @return RecaptchaInterface
     */
    public function recaptcha(): RecaptchaInterface;

    /**
     * Set Recaptcha instance.
     *
     * @param RecaptchaInterface $recaptcha
     *
     * @return void
     */
    public function setRecaptcha(RecaptchaInterface $recaptcha): void;
}