<?php declare(strict_types=1);

namespace Pollen\Recaptcha\Field;

use Pollen\Recaptcha\Contracts\RecaptchaField as RecaptchaFieldContract;
use Pollen\Recaptcha\Contracts\Recaptcha as RecaptchaManager;
use tiFy\Field\FieldDriver;

class RecaptchaField extends FieldDriver implements RecaptchaFieldContract
{
    /**
     * Instance du gestionnaire.
     * @var RecaptchaManager|null
     */
    protected $recaptchaManager;

    /**
     * @param RecaptchaManager $recaptchaManager
     */
    public function __construct(RecaptchaManager $recaptchaManager)
    {
        $this->recaptchaManager = $recaptchaManager;
    }

    /**
     * {@inheritDoc}
     *
     * @see https://developers.google.com/recaptcha/docs/display
     *
     * @return array {
     * @var string $before Contenu placé avant le champ.
     * @var string $after Contenu placé après le champ.
     * @var string $name Clé d'indice de la valeur de soumission du champ.
     * @var string $value Valeur courante de soumission du champ.
     * @var array $attrs Attributs HTML du champ.
     * @var array $viewer Liste des attributs de configuration du controleur de gabarit d'affichage.
     * @var string $theme Couleur d'affichage du captcha. light|dark.
     * @var string $sitekey Clé publique. Optionnel si l'API $recaptcha est active.
     * @var string $secretkey Clé publique. Optionnel si l'API $recaptcha est active.
     * }
     */
    public function defaults(): array
    {
        return [
            'attrs'     => [],
            'after'     => '',
            'before'    => '',
            'name'      => '',
            'value'     => '',
            'viewer'    => [],
            'sitekey'   => '',
            'secretkey' => '',
            'theme'     => 'light',
            'tabindex'  => 0,
        ];
    }

    /**
     * @inheritDoc
     */
    public function recaptchaManager(): RecaptchaManager
    {
        return $this->recaptchaManager;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        if (!$this->get('attrs.id')) {
            $this->set('attrs.id', 'Field-recapcha--' . $this->getIndex());
        }

        $this->set('attrs.data-tabindex', $this->get('tabindex'));

        if (!$this->get('sitekey')) {
            $this->set('sitekey', $this->recaptchaManager()->getSiteKey());
        }

        $this->recaptchaManager()->addWidgetRender($this->get('attrs.id'), [
            'sitekey' => $this->get('sitekey'),
            'theme'   => $this->get('theme'),
        ]);

        return parent::render();
    }

    /**
     * @inheritDoc
     */
    public function viewDirectory(): string
    {
        return class_info($this)->getDirname() . '/views/';
    }
}