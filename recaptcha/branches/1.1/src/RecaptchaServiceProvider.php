<?php

declare(strict_types=1);

namespace Pollen\Recaptcha;

use Pollen\Field\FieldManagerInterface;
use Pollen\Recaptcha\Field\RecaptchaField;
use Pollen\Recaptcha\Form\RecaptchaFormField;
use Pollen\Container\BaseServiceProvider;

class RecaptchaServiceProvider extends BaseServiceProvider
{
    /**
     * @var string[]
     */
    protected $provides = [
        RecaptchaInterface::class,
        RecaptchaField::class,
        RecaptchaFormField::class,
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(
            RecaptchaInterface::class,
            function () {
                return new Recaptcha([], $this->getContainer());
            }
        );

        $this->getContainer()->add(
            RecaptchaField::class,
            function () {
                return new RecaptchaField(
                    $this->getContainer()->get(RecaptchaInterface::class),
                    $this->getContainer()->get(FieldManagerInterface::class)
                );
            }
        );

        $this->getContainer()->add(
            RecaptchaFormField::class,
            function () {
                return new RecaptchaFormField($this->getContainer()->get(RecaptchaInterface::class));
            }
        );
    }
}