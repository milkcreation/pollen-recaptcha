<?php

declare(strict_types=1);

namespace Pollen\Recaptcha;

use ReCaptcha\ReCaptcha as ReCaptchaDriver;
use ReCaptcha\Response as ReCaptchaResponse;

/**
 * @mixin \Pollen\Support\Concerns\BootableTrait
 * @mixin \Pollen\Support\Concerns\ConfigBagAwareTrait
 * @mixin \Pollen\Support\Concerns\ContainerAwareTrait
 * @mixin \Pollen\Support\Concerns\FieldManagerAwareTrait
 * @mixin \Pollen\Support\Concerns\FormManagerAwareTrait
 * @mixin \Pollen\Support\Concerns\HttpRequestAwareTrait
 */
interface RecaptchaInterface
{
    /**
     * Déclaration d'un widget de rendu.
     *
     * @param string $id Identifiant de qualification HTML de l'élément porteur.
     * @param array $params Liste des paramètres.
     *
     * @return static
     */
    public function addWidgetRender(string $id, array $params = []): RecaptchaInterface;

    /**
     * Initialisation.
     *
     * @return static
     */
    public function boot(): RecaptchaInterface;

    /**
     * Vérification de la configuration.
     *
     * @return true
     *
     * @throws \Pollen\Recaptcha\Exception\RecaptchaSiteKeyException
     * @throws \Pollen\Recaptcha\Exception\RecaptchaSecretKeyException
     */
    public function checkConfig(): bool;

    /**
     * Récupération de la réponse HTTP à l'issue de la soumission.
     *
     * @return ReCaptchaResponse
     */
    public function getHandleResponse(): ReCaptchaResponse;

    /**
     * Récupération des scripts JS.
     *
     * @return string
     */
    public function getJsScripts(): string;

    /**
     * Récupération de la langue.
     *
     * @return string
     */
    public function getLanguage(): string;

    /**
     * Récupération de la clé publique.
     *
     * @return string|null
     */
    public function getSiteKey(): ?string;

    /**
     * Récupération de  la réponse à l'issue de la soumission.
     *
     * @return bool
     */
    public function isValidated(): bool;

    /**
     * Instance du pilote associé.
     *
     * @return ReCaptchaDriver
     */
    public function reCaptchaDriver(): ReCaptchaDriver;

    /**
     * Chemin absolu vers une ressource (fichier|répertoire).
     *
     * @param string|null $path Chemin relatif vers la ressource.
     *
     * @return string
     */
    public function resources(?string $path = null): string;

    /**
     * Définition du chemin absolu vers le répertoire des ressources.
     *
     * @var string $resourceBaseDir
     *
     * @return static
     */
    public function setResourcesBaseDir(string $resourceBaseDir): RecaptchaInterface;
}