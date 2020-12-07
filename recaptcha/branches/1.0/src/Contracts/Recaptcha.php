<?php declare(strict_types=1);

namespace Pollen\Recaptcha\Contracts;

use ReCaptcha\ReCaptcha as ReCaptchaDriver;
use ReCaptcha\Response as ReCaptchaResponse;
use tiFy\Contracts\Filesystem\LocalFilesystem;
use tiFy\Contracts\Support\ParamsBag;

/**
 * @mixin \tiFy\Support\Concerns\BootableTrait
 * @mixin \tiFy\Support\Concerns\ContainerAwareTrait
 */
interface Recaptcha
{
    /**
     * Récupération de l'instance courante.
     *
     * @return static
     */
    public static function instance(): Recaptcha;

    /**
     * Déclaration d'un widget de rendu.
     *
     * @param string $id Identifiant de qualification HTML de l'élément porteur.
     * @param array $params Liste des paramètres.
     *
     * @return static
     */
    public function addWidgetRender(string $id, array $params = []): Recaptcha;

    /**
     * Initialisation.
     *
     * @return static
     */
    public function boot(): Recaptcha;

    /**
     * Récupération de paramètre|Définition de paramètres|Instance du gestionnaire de paramètre.
     *
     * @param string|array|null $key Clé d'indice du paramètre à récupérer|Liste des paramètre à définir.
     * @param mixed $default Valeur de retour par défaut lorsque la clé d'indice est une chaine de caractère.
     *
     * @return mixed|ParamsBag
     */
    public function config($key = null, $default = null);

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
     * Chemin absolu vers une ressources (fichier|répertoire).
     *
     * @param string|null $path Chemin relatif vers la ressource.
     *
     * @return LocalFilesystem|string|null
     */
    public function resources(?string $path = null);

    /**
     * Définition des paramètres de configuration.
     *
     * @param array $attrs Liste des attributs de configuration.
     *
     * @return static
     */
    public function setConfig(array $attrs): ReCaptcha;

    /**
     * Récupération de la réponse à l'issue de la soumission.
     *
     * @return ReCaptchaResponse
     */
    public function response(): ReCaptchaResponse;
}