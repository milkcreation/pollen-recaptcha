<?php

declare(strict_types=1);

namespace Pollen\Recaptcha;

use Pollen\Recaptcha\Contracts\RecaptchaContract;
use Pollen\Recaptcha\Field\RecaptchaField;
use Pollen\Recaptcha\Form\RecaptchaFormField;
use tiFy\Container\ServiceProvider as BaseServiceProvider;
use tiFy\Field\Contracts\FieldContract;

class RecaptchaServiceProvider extends BaseServiceProvider
{
    /**
     * Liste des noms de qualification des services fournis.
     * {@internal Permet le chargement différé des services qualifié.}
     * @var string[]
     */
    protected $provides = [
        RecaptchaContract::class,
        RecaptchaField::class,
        RecaptchaFormField::class,
    ];

    /**
     * @inheritDoc
     */
    public function boot()
    {
        events()->listen(
            'wp.booted',
            function () {
                $this->getContainer()->get(RecaptchaContract::class)->boot();
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(
            RecaptchaContract::class,
            function () {
                return new Recaptcha(config('recaptcha', []), $this->getContainer());
            }
        );

        $this->getContainer()->add(
            RecaptchaField::class,
            function () {
                return new RecaptchaField(
                    $this->getContainer()->get(RecaptchaContract::class),
                    $this->getContainer()->get(FieldContract::class)
                );
            }
        );

        $this->getContainer()->add(
            RecaptchaFormField::class,
            function () {
                return new RecaptchaFormField($this->getContainer()->get(RecaptchaContract::class));
            }
        );
    }
}
