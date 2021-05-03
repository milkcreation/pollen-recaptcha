<?php

declare(strict_types=1);

namespace Pollen\Recaptcha\Form;

use Pollen\Form\Exception\FieldValidateException;
use Pollen\Form\FormFieldDriver;
use Pollen\Form\FormFieldDriverInterface;
use Pollen\Recaptcha\Exception\RecaptchaConfigException;
use Pollen\Recaptcha\RecaptchaInterface;

class RecaptchaFormField extends FormFieldDriver implements RecaptchaFormFieldInterface
{
    /**
     * Instance du gestionnaire.
     * @var RecaptchaInterface
     */
    private $recaptchaManager;

    /**
     * Liste des attributs de support.
     * @var array
     */
    protected $supports = ['label', 'request', 'wrapper'];

    /**
     * @param RecaptchaInterface $recaptchaManager
     */
    public function __construct(RecaptchaInterface $recaptchaManager)
    {
        $this->recaptchaManager = $recaptchaManager;
    }

    /**
     * @inheritDoc
     */
    public function boot(): FormFieldDriverInterface
    {
        if (!$this->isBooted()) {
            try{
                $this->recaptchaManager->checkConfig();
            } catch(RecaptchaConfigException $e) {
                throw $e;
            }

            parent::boot();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return (string)$this->recaptchaManager->field(
            'recaptcha',
            array_merge(
                $this->getExtras(),
                [
                    'name'  => $this->getName(),
                    'attrs' => array_merge(
                        [
                            'id' => str_replace("-", '_', sanitize_key($this->form()->getAlias())),
                        ],
                        $this->params('attrs', [])
                    ),
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function validate($value = null): void
    {
        if (!$this->recaptchaManager->isValidated()) {
            throw new FieldValidateException(
                $this, 'Invalid Recaptcha response.', ['recaptcha']
            );
        }
    }
}