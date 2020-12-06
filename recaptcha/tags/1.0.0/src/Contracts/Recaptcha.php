<?php declare(strict_types=1);

namespace Pollen\Recaptcha\Contracts;

use Exception;
use Psr\Container\ContainerInterface as Container;
use ReCaptcha\ReCaptcha as ReCaptchaDriver;
use ReCaptcha\Response as ReCaptchaResponse;
use tiFy\Contracts\Filesystem\LocalFilesystem;
use tiFy\Contracts\Support\ParamsBag;

interface Recaptcha
{
    /**
     * Récupération de l'instance courante.
     *
     * @return static
     *
     * @throws Exception
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
     *
     * @throws Exception
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
     * Récupération de l'instance du gestionnaire d'injection de dépendances.
     *
     * @return Container|null
     */
    public function getContainer(): ?Container;

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
     * Résolution de service fourni par le gestionnaire.
     *
     * @param string $alias
     *
     * @return object|mixed|null
     */
    public function resolve(string $alias);

    /**
     * Vérification de résolution possible d'un service fourni par le gestionnaire.
     *
     * @param string $alias
     *
     * @return bool
     */
    public function resolvable(string $alias): bool;

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
     * Définition du conteneur d'injection de dépendances.
     *
     * @param Container $container
     *
     * @return static
     */
    public function setContainer(Container $container): ReCaptcha;

    /**
     * Récupération de la réponse à l'issue de la soumission.
     *
     * @return ReCaptchaResponse
     */
    public function response(): ReCaptchaResponse;
}