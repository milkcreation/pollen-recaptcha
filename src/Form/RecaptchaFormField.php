<?php

declare(strict_types=1);

namespace Pollen\Recaptcha\Form;

use Pollen\Form\FieldDriver;
use Pollen\Form\Exception\FieldValidateException;
use Pollen\Form\FieldDriverInterface;
use Pollen\Recaptcha\Exception\RecaptchaConfigException;
use Pollen\Recaptcha\RecaptchaInterface;

class RecaptchaFormField extends FieldDriver implements RecaptchaFormFieldInterface
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
    public function boot(): FieldDriverInterface
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
        return (string)$this->recaptchaManager->fieldManager()->get(
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
    public function validate(): void
    {
        if (!$this->recaptchaManager->isValidated()) {
            throw (new FieldValidateException(__('La saisie de la protection antispam est incorrecte.', 'tify')))
                ->setField($this)->setAlias('recaptcha');
        }
    }
}