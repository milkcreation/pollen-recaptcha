<?php declare(strict_types=1);

namespace Pollen\Recaptcha;

use Pollen\Recaptcha\Contracts\Recaptcha as RecaptchaManagerContract;
use Pollen\Recaptcha\Contracts\RecaptchaField as RecaptchaFieldContract;
use Pollen\Recaptcha\Contracts\RecaptchaFormFieldDriver as RecaptchaFormFieldDriverContract;
use Pollen\Recaptcha\Field\RecaptchaField;
use Pollen\Recaptcha\Form\RecaptchaFormFieldDriver;
use tiFy\Container\ServiceProvider as BaseServiceProvider;

class RecaptchaServiceProvider extends BaseServiceProvider
{
    /**
     * Liste des noms de qualification des services fournis.
     * {@internal Permet le chargement différé des services qualifié.}
     * @var string[]
     */
    protected $provides = [
        RecaptchaManagerContract::class,
        RecaptchaFieldContract::class,
        RecaptchaFormFieldDriverContract::class
    ];

    /**
     * @inheritDoc
     */
    public function boot()
    {
        events()->listen('wp.booted', function () {
            $this->getContainer()->get(RecaptchaManagerContract::class)->boot();
        });
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(RecaptchaManagerContract::class, function (): RecaptchaManagerContract {
            return new Recaptcha(config('recaptcha', []), $this->getContainer());
        });

        $this->getContainer()->share(RecaptchaFieldContract::class, function (): RecaptchaFieldContract {
            return new RecaptchaField($this->getContainer()->get(RecaptchaManagerContract::class));
        });

        $this->getContainer()->add(RecaptchaFormFieldDriverContract::class, function (): RecaptchaFormFieldDriverContract {
            return new RecaptchaFormFieldDriver($this->getContainer()->get(RecaptchaManagerContract::class));
        });
    }
}
