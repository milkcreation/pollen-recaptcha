<?php declare(strict_types=1);

namespace Pollen\Recaptcha;

use Exception;
use Pollen\Recaptcha\Contracts\Recaptcha as RecaptchaContract;
use Pollen\Recaptcha\Field\RecaptchaField;
use Psr\Container\ContainerInterface as Container;
use ReCaptcha\ReCaptcha as ReCaptchaDriver;
use ReCaptcha\Response as ReCaptchaResponse;
use ReCaptcha\RequestMethod\SocketPost as ReCaptchaSocket;
use RuntimeException;
use tiFy\Contracts\Filesystem\LocalFilesystem;
use tiFy\Support\Proxy\Field;
use tiFy\Support\ParamsBag;
use tiFy\Support\Proxy\Request;
use tiFy\Support\Proxy\Storage;

class Recaptcha implements RecaptchaContract
{
    /**
     * Instance de la classe.
     * @var static|null
     */
    private static $instance;

    /**
     * Indicateur d'initialisation.
     * @var bool
     */
    private $booted = false;

    /**
     * Instance du pilote associé.
     * @var ReCaptchaDriver
     */
    private $reCaptchaDriver;

    /**
     * Instance du gestionnaire des ressources
     * @var LocalFilesystem|null
     */
    private $resources;

    /**
     * Instance du gestionnaire de configuration.
     * @var ParamsBag
     */
    private $configBag;

    /**
     * Instance du conteneur d'injection de dépendances.
     * @var Container|null
     */
    private $container;

    /**
     * Liste des widgets déclarés.
     * @var array
     */
    protected $widgets = [];

    /**
     * @param array $config
     * @param Container|null $container
     *
     * @return void
     */
    public function __construct(array $config = [], Container $container = null)
    {
        $this->setConfig($config);

        if (!is_null($container)) {
            $this->setContainer($container);
        }

        if (!self::$instance instanceof static) {
            self::$instance = $this;
        }
    }

    /**
     * @inheritDoc
     */
    public static function instance(): RecaptchaContract
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        throw new Exception(sprintf('Unavailable %s instance', __CLASS__));
    }

    /**
     * @inheritDoc
     */
    public function addWidgetRender(string $id, array $params = []): RecaptchaContract
    {
        $this->widgets[$id] = $params;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function boot(): RecaptchaContract
    {
        if (!$this->booted) {

            //try {
            //    //parent::__construct($attrs['secretkey'], (ini_get('allow_url_fopen') ? null : new ReCaptchaSocket()));
//
            //    $this->attributes = $attrs;
//
            //    Field::register('recaptcha', new RecaptchaField($this));
//
            //    add_action('wp_print_footer_scripts', function () {
            //             if ($this->widgets) {
            //                $js = "let reCaptchaEl = {};";
            //                $js .= "function reCaptchaCallback() {";
            //                foreach ($this->widgets as $id => $params) {
            //                    $js .= "reCaptchaEl['{$id}']=document.getElementById('{$id}');";
            //                    $js .= "if(typeof(reCaptchaEl['{$id}'])!='undefined' && reCaptchaEl['{$id}']!=null){";
            //                    $js .= "try{grecaptcha.render('{$id}', " . json_encode($params) . ");} catch(error){/**console.log(error); */}";
            //                    $js .= "}";
            //                }
            //                $js .= "};";
            //                echo '<script type="text/javascript">' . $js . '</script>';
            //                echo '<script type="text/javascript"
            //                      src="https://www.google.com/recaptcha/api.js?hl=' . $this->getLanguage() . '&onload=reCaptchaCallback&render=explicit"
            //                      async defer></script>';
            //            }
            //        }
            //    });
//
            //} catch (RuntimeException $e) {
            //    throw $e;
            //}

            $this->booted = true;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function config($key = null, $default = null)
    {
        if (!isset($this->config) || is_null($this->config)) {
            $this->config = new ParamsBag();
        }

        if (is_string($key)) {
            return $this->config->get($key, $default);
        } elseif (is_array($key)) {
            return $this->config->set($key);
        } else {
            return $this->config;
        }
    }

    /**
     * @inheritDoc
     */
    public function getContainer(): ?Container
    {
        return $this->container;
    }


    /**
     * @inheritDoc
     */
    public function getLanguage(): string
    {
        global $locale;

        switch ($locale) {
            default :
                [$lang, $indice] = preg_split('/_/', $locale, 2);
                break;
            case 'zh_CN':
                $lang = 'zh-CN';
                break;
            case 'zh_TW':
                $lang = 'zh-TW';
                break;
            case 'en_GB' :
                $lang = 'en-GB';
                break;
            case 'fr_CA' :
                $lang = 'fr-CA';
                break;
            case 'de_AT' :
                $lang = 'de-AT';
                break;
            case 'de_CH' :
                $lang = 'de-CH';
                break;
            case 'pt_BR' :
                $lang = 'pt-BR';
                break;
            case 'pt_PT' :
                $lang = 'pt-PT';
                break;
            case 'es_AR' :
            case 'es_CL' :
            case 'es_CO' :
            case 'es_MX' :
            case 'es_PE' :
            case 'es_PR' :
            case 'es_VE' :
                $lang = 'es-419';
                break;
        }

        return $lang;
    }

    /**
     * @inheritDoc
     */
    public function getSiteKey(): ?string
    {
        return $this->attributes['sitekey'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function isValidated(): bool
    {
        return $this->response()->isSuccess();
    }

    /**
     * Instance du pilote associé.
     *
     * @return ReCaptchaDriver
     */
    public function reCaptchaDriver(): ReCaptchaDriver
    {
        if ($this->reCaptchaDriver === null) {
            $this->reCaptchaDriver = new ReCaptchaDriver();
        }

        return $this->reCaptchaDriver;
    }

    /**
     * @inheritDoc
     */
    public function resolve(string $alias): ?object
    {
        return ($container = $this->getContainer()) ? $container->get($alias) : null;
    }

    /**
     * @inheritDoc
     */
    public function resolvable(string $alias): bool
    {
        return ($container = $this->getContainer()) && $container->has($alias);
    }

    /**
     * @inheritDoc
     */
    public function resources(?string $path = null)
    {
        if (!isset($this->resources) ||is_null($this->resources)) {
            $this->resources = Storage::local(__DIR__ . '/Resources');
        }

        return is_null($path) ? $this->resources : $this->resources->path($path);
    }

    /**
     * @inheritDoc
     */
    public function setConfig(array $attrs): RecaptchaContract
    {
        $this->config($attrs);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setContainer(Container $container): RecaptchaContract
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function response(): ReCaptchaResponse
    {
        return $this->reCaptchaDriver()->verify(Request::input('g-recaptcha-response'), Request::server('REMOTE_ADDR'));
    }
}