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
     * Recaptcha manager instance.
     * @var RecaptchaInterface
     */
    protected RecaptchaInterface $recaptchaManager;

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
             * @var array $attrs HTML Attributes.
             */
            'attrs'     => [],
            /**
             * @var string $after After field HTML contents.
             */
            'after'     => '',
            /**
             * @var string $before Before field HTML contents.
             */
            'before'    => '',
            /**
             * @var array $viewer View engine params.
             */
            'viewer'    => [],
            /**
             * @var string $name Field form submit name.
             */
            'name'      => '',
            /**
             * @var string $value Field value.
             */
            'value'     => '',
            /**
             * @var string $sitekey Site key. Use global Recaptcha Manager site key as default.
             */
            'sitekey'   => '',
            /**
             * @var string $secretkey Secret key. Use global Recaptcha Manager secret key as default.
             */
            'secretkey' => '',
            /**
             * @var string $theme Recaptcha theme color. light|dark.
             */
            'theme'     => 'light',
            /**
             * @var int $tabindex Field tab index.
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

        $this->recaptchaManager()->assetsAutoloader();

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