<?php

declare(strict_types=1);

namespace Pollen\Recaptcha;

use Pollen\Support\ProxyResolver;
use RuntimeException;

/**
 * @see \Pollen\Recaptcha\RecaptchaProxyInterface
 */
trait RecaptchaProxy
{
    /**
     * Instance du gestionnaire reCaptcha.
     * @var RecaptchaInterface
     */
    private $recaptcha;

    /**
     * Instance du gestionnaire reCaptcha.
     *
     * @return RecaptchaInterface
     */
    public function recaptcha(): RecaptchaInterface
    {
        if ($this->recaptcha === null) {
            try {
                $this->recaptcha = Recaptcha::getInstance();
            } catch (RuntimeException $e) {
                $this->recaptcha = ProxyResolver::getInstance(
                    RecaptchaInterface::class,
                    Recaptcha::class,
                    method_exists($this, 'getContainer') ? $this->getContainer() : null
                );
            }
        }

        return $this->recaptcha;
    }

    /**
     * Définition du gestionnaire reCaptcha.
     *
     * @param RecaptchaInterface $recaptcha
     *
     * @return void
     */
    public function setRecaptcha(RecaptchaInterface $recaptcha): void
    {
        $this->recaptcha = $recaptcha;
    }
}