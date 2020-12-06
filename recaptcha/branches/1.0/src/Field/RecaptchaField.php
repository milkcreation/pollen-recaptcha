<?php declare(strict_types=1);

namespace Pollen\Recaptcha\Field;

use Exception;
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
     * @inheritDoc
     */
    public function defaults(): array
    {
        return [
            /**
             * @var array $attrs Attributs HTML du champ.
             */
            'attrs'     => [],
            /**
             * @var string $after Contenu placé après le champ.
             */
            'after'     => '',
            /**
             * @var string $before Contenu placé avant le champ.
             */
            'before'    => '',
            /**
             * @var array $viewer Liste des attributs de configuration du pilote d'affichage.
             */
            'viewer'    => [],
            /**
             * @var string $name Clé d'indice de la valeur de soumission du champ
             */
            'name'      => '',
            /**
             * @var string $value Valeur courante de soumission du champ.
             */
            'value'     => '',
            /**
             * @var string $sitekey Clé publique. Optionnel si l'API $recaptcha est active.
             */
            'sitekey'   => '',
            /**
             * @var string $secretkey Clé publique. Optionnel si l'API $recaptcha est active.
             */
            'secretkey' => '',
            /**
             * @var string $theme Couleur d'affichage du captcha. light|dark.
             */
            'theme'     => 'light',
            /**
             * @var int $tabindex Indice du champ dans le formulaire
             */
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
     *
     * @throws Exception
     */
    public function render(): string
    {
        if (!$this->get('attrs.id')) {
            $this->set('attrs.id', 'Field-recapcha--' . $this->getIndex());
        }

        $this->set('attrs.data-tabindex', $this->pull('tabindex'));

        if (!$siteKey = $this->pull('sitekey')) {
            $siteKey = $this->recaptchaManager()->getSiteKey();
        }

        $this->recaptchaManager()->addWidgetRender($this->get('attrs.id'), [
            'sitekey' => $siteKey,
            'theme'   => $this->pull('theme'),
        ]);

        return parent::render();
    }

    /**
     * @inheritDoc
     */
    public function viewDirectory(): string
    {
        return $this->recaptchaManager()->resources('views/field/recaptcha');
    }
}