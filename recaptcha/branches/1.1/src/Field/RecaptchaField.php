<?php

declare(strict_types=1);

namespace Pollen\Recaptcha\Field;

use Exception;
use Pollen\Field\FieldDriver;
use Pollen\Field\FieldManagerInterface;
use Pollen\Recaptcha\Exception\RecaptchaConfigException;
use Pollen\Recaptcha\RecaptchaInterface;

class RecaptchaField extends FieldDriver implements RecaptchaFieldInterface
{
    /**
     * Instance du gestionnaire.
     * @var RecaptchaInterface
     */
    protected $recaptchaManager;

    /**
     * @param RecaptchaInterface $recaptchaManager
     * @param FieldManagerInterface $fieldManager
     */
    public function __construct(RecaptchaInterface $recaptchaManager, FieldManagerInterface $fieldManager)
    {
        $this->recaptchaManager = $recaptchaManager;

        parent::__construct($fieldManager);
    }

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        if (!$this->isBooted()) {
            try{
                $this->recaptchaManager->checkConfig();
            } catch(RecaptchaConfigException $e) {
                throw $e;
            }

            parent::boot();
        }
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
    public function recaptchaManager(): RecaptchaInterface
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

        $this->recaptchaManager()->addWidgetRender(
            $this->get('attrs.id'),
            [
                'sitekey' => $siteKey,
                'theme'   => $this->pull('theme'),
            ]
        );

        return parent::render();
    }

    /**
     * @inheritDoc
     */
    public function viewDirectory(): string
    {
        return $this->recaptchaManager()->resources('/views/field/recaptcha');
    }
}