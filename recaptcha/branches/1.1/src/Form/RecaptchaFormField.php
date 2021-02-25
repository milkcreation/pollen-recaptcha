<?php

declare(strict_types=1);

namespace Pollen\Recaptcha\Form;

use Pollen\Recaptcha\Contracts\RecaptchaContract;
use tiFy\Form\FieldDriver;
use tiFy\Form\FieldValidateException;

class RecaptchaFormField extends FieldDriver implements RecaptchaFormFieldInterface
{
    /**
     * Instance du gestionnaire.
     * @var RecaptchaContract
     */
    private $recaptchaManager;

    /**
     * Liste des attributs de support.
     * @var array
     */
    protected $supports = ['label', 'request', 'wrapper'];

    /**
     * @param RecaptchaContract $recaptchaManager
     */
    public function __construct(RecaptchaContract $recaptchaManager)
    {
        $this->recaptchaManager = $recaptchaManager;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return $this->recaptchaManager->fieldManager()->get(
            'recaptcha',
            array_merge(
                $this->getExtras(),
                [
                    'name'  => $this->getName(),
                    'attrs' => array_merge(
                        [
                            'id' => preg_replace('/-/', '_', sanitize_key($this->form()->getAlias())),
                        ],
                        $this->params('attrs', [])
                    ),
                ]
            )
        )->render();
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