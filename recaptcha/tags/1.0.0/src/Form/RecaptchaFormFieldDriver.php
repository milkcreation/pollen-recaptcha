<?php declare(strict_types=1);

namespace Pollen\Recaptcha\Form;

use Pollen\Recaptcha\Contracts\Recaptcha as RecaptchaManager;
use Pollen\Recaptcha\Contracts\RecaptchaFormFieldDriver as RecaptchaFormFieldDriverContract;
use tiFy\Form\FieldDriver;
use tiFy\Form\FieldValidateException;
use tiFy\Support\Proxy\Field;

class RecaptchaFormFieldDriver extends FieldDriver implements RecaptchaFormFieldDriverContract
{
    /**
     * Instance du gestionnaire.
     * @var RecaptchaManager
     */
    private $recaptchaManager;

    /**
     * Liste des attributs de support.
     * @var array
     */
    protected $supports = ['label', 'request', 'wrapper'];

    /**
     * @param RecaptchaManager $recaptchaManager
     */
    public function __construct(RecaptchaManager $recaptchaManager)
    {
        $this->recaptchaManager = $recaptchaManager;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return Field::get('recaptcha', array_merge($this->getExtras(), [
            'name'  => $this->getName(),
            'attrs' => array_merge([
                'id' => preg_replace('/-/', '_', sanitize_key($this->form()->getAlias()))
            ], $this->params('attrs', []))
        ]))->render();
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