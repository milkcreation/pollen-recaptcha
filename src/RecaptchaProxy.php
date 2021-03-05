<?php

declare(strict_types=1);

namespace Pollen\Recaptcha;

use Psr\Container\ContainerInterface as Container;
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
            $container = method_exists($this, 'getContainer') ? $this->getContainer() : null;

            if ($container instanceof Container && $container->has(RecaptchaInterface::class)) {
                $this->recaptcha = $container->get(RecaptchaInterface::class);
            } else {
                try {
                    $this->recaptcha = Recaptcha::getInstance();
                } catch(RuntimeException $e) {
                    $this->recaptcha = new Recaptcha();
                }
            }
        }

        return $this->recaptcha;
    }

    /**
     * Définition du gestionnaire reCaptcha.
     *
     * @param RecaptchaInterface $recaptcha
     *
     * @return static
     */
    public function setRecaptcha(RecaptchaInterface $recaptcha): self
    {
        $this->recaptcha = $recaptcha;

        return $this;
    }
}