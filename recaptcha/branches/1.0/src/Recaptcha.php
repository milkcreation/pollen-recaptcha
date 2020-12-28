<?php

declare(strict_types=1);

namespace Pollen\Recaptcha;

use LogicException;
use RuntimeException;
use Pollen\Recaptcha\Contracts\RecaptchaContract;
use Pollen\Recaptcha\Field\RecaptchaField;
use Pollen\Recaptcha\Form\RecaptchaFormField;
use Psr\Container\ContainerInterface as Container;
use ReCaptcha\ReCaptcha as ReCaptchaDriver;
use ReCaptcha\Response as ReCaptchaResponse;
use ReCaptcha\RequestMethod\SocketPost as ReCaptchaSocket;
use tiFy\Field\Contracts\FieldContract;
use tiFy\Field\Field;
use tiFy\Contracts\Filesystem\LocalFilesystem;
use tiFy\Support\Concerns\BootableTrait;
use tiFy\Support\Concerns\ContainerAwareTrait;
use tiFy\Support\Proxy\Form;
use tiFy\Support\ParamsBag;
use tiFy\Support\Proxy\Request;
use tiFy\Support\Proxy\Storage;

class Recaptcha implements RecaptchaContract
{
    use BootableTrait;
    use ContainerAwareTrait;

    /**
     * Instance de la classe.
     * @var static|null
     */
    private static $instance;

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
     * Instance du gestion de portions d'affichage.
     * @var FieldContract
     */
    protected $fieldManager;

    /**
     * Liste des widgets déclarés.
     * @type array
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

        throw new RuntimeException(sprintf('Unavailable %s instance', __CLASS__));
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
        if (!$this->isBooted()) {
            events()->trigger('recaptcha.booting', [$this]);

            if (!$this->config('sitekey')) {
                throw new LogicException(
                    'Recaptcha v2 Site Key required, please create and configure : https://www.google.com/recaptcha/about/'
                );
            }

            if (!$this->config('secretkey')) {
                throw new LogicException(
                    'Recaptcha v2 Secret Key required, please create and configure : https://www.google.com/recaptcha/about/'
                );
            }

            $fieldManager = $this->containerHas(FieldContract::class)
                ? $this->containerGet(FieldContract::class) : new Field();

            $fieldManager->register(
                'recaptcha',
                $this->containerHas(RecaptchaField::class)
                    ? RecaptchaField::class : new RecaptchaField($this, $fieldManager)
            );

            Form::setFieldDriver(
                'recaptcha',
                $this->containerHas(RecaptchaFormField::class)
                    ? $this->containerGet(RecaptchaFormField::class) : new RecaptchaFormField($this)
            );

            add_action(
                'wp_print_footer_scripts',
                function () {
                    if ($this->widgets) {
                        $js = "let reCaptchaEl = {};";
                        $js .= "function reCaptchaCallback() {";
                        foreach ($this->widgets as $id => $params) {
                            $js .= "reCaptchaEl['{$id}']=document.getElementById('{$id}');";
                            $js .= "if(typeof(reCaptchaEl['{$id}'])!='undefined' && reCaptchaEl['{$id}']!=null){";
                            $js .= "try{grecaptcha.render('{$id}', " . json_encode(
                                    $params
                                ) . ");} catch(error){console.log(error);}";
                            $js .= "}";
                        }
                        $js .= "};";
                        echo '<script type="text/javascript">' . $js . '</script>';
                        echo '<script type="text/javascript"
                         src="https://www.google.com/recaptcha/api.js?hl=' . $this->getLanguage() . '&onload=reCaptchaCallback&render=explicit"
                         async defer></script>';
                    }
                }
            );

            $this->setBooted();

            events()->trigger('recaptcha.booted', [$this]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function config($key = null, $default = null)
    {
        if ($this->configBag === null) {
            $this->configBag = new ParamsBag();
        }

        if (is_string($key)) {
            return $this->configBag->get($key, $default);
        } elseif (is_array($key)) {
            return $this->configBag->set($key);
        } else {
            return $this->configBag;
        }
    }

    /**
     * @inheritDoc
     */
    public function fieldManager(): FieldContract
    {
        if ($this->fieldManager === null) {
            $this->fieldManager = $this->containerHas(FieldContract::class)
                ? $this->containerGet(FieldContract::class) : new Field();
        }

        return $this->fieldManager;
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(): string
    {
        global $locale;

        switch ($locale) {
            default :
                [$lang] = preg_split('/_/', $locale, 1);
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
        return $this->config('sitekey') ?? null;
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
            $this->reCaptchaDriver = new ReCaptchaDriver(
                $this->config('secretkey'), (ini_get('allow_url_fopen') ? null : new ReCaptchaSocket())
            );
        }
        return $this->reCaptchaDriver;
    }

    /**
     * @inheritDoc
     */
    public function resources(?string $path = null)
    {
        if (!isset($this->resources) || is_null($this->resources)) {
            $this->resources = Storage::local(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'resources');
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
    public function setFieldManager(FieldContract $fieldManager): RecaptchaContract
    {
        $this->fieldManager = $fieldManager;

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