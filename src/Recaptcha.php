<?php

declare(strict_types=1);

namespace Pollen\Recaptcha;

use Exception;
use Pollen\Recaptcha\Exception\RecaptchaSecretKeyException;
use Pollen\Recaptcha\Exception\RecaptchaSiteKeyException;
use Pollen\Recaptcha\Field\RecaptchaField;
use Pollen\Recaptcha\Form\RecaptchaFormField;
use Pollen\Support\Concerns\BootableTrait;
use Pollen\Support\Concerns\ConfigBagAwareTrait;
use Pollen\Support\Concerns\ResourcesAwareTrait;
use Pollen\Support\Exception\ManagerRuntimeException;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\EventProxy;
use Pollen\Support\Proxy\FieldProxy;
use Pollen\Support\Proxy\FormProxy;
use Pollen\Support\Proxy\HttpRequestProxy;
use Psr\Container\ContainerInterface as Container;
use ReCaptcha\ReCaptcha as ReCaptchaDriver;
use ReCaptcha\Response as ReCaptchaResponse;
use ReCaptcha\RequestMethod\SocketPost as ReCaptchaSocket;

class Recaptcha implements RecaptchaInterface
{
    use BootableTrait;
    use ConfigBagAwareTrait;
    use ResourcesAwareTrait;
    use ContainerProxy;
    use EventProxy;
    use FieldProxy;
    use FormProxy;
    use HttpRequestProxy;

    /**
     * Instance principale.
     * @var static|null
     */
    private static $instance;

    /**
     * Instance du pilote associé.
     * @var ReCaptchaDriver
     */
    private $reCaptchaDriver;

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
    public function __construct(array $config = [], ?Container $container = null)
    {
        $this->setConfig($config);

        if ($container !== null) {
            $this->setContainer($container);
        }

        $this->setResourcesBaseDir(dirname(__DIR__) . '/resources');

        if ($this->config('boot_enabled', true)) {
            $this->boot();
        }

        if (!self::$instance instanceof static) {
            self::$instance = $this;
        }
    }

    /**
     * Récupération de l'instance principale.
     *
     * @return static
     */
    public static function getInstance(): RecaptchaInterface
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        throw new ManagerRuntimeException(sprintf('Unavailable [%s] instance', __CLASS__));
    }

    /**
     * @inheritDoc
     */
    public function addWidgetRender(string $id, array $params = []): RecaptchaInterface
    {
        $this->widgets[$id] = $params;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function boot(): RecaptchaInterface
    {
        if (!$this->isBooted()) {
            $this->event()->trigger('recaptcha.booting', [&$this]);

            $this->field()->register(
                'recaptcha',
                $this->containerHas(RecaptchaField::class)
                    ? RecaptchaField::class : new RecaptchaField($this, $this->field())
            );

            $this->form()->registerFormFieldDriver(
                'recaptcha',
                $this->containerHas(RecaptchaFormField::class)
                    ? RecaptchaFormField::class : new RecaptchaFormField($this)
            );

            $this->setBooted();

            $this->event()->trigger('recaptcha.booted', [&$this]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function checkConfig(): bool
    {
        if (!$this->config('sitekey')) {
            throw new RecaptchaSiteKeyException();
        }

        if (!$this->config('secretkey')) {
            throw new RecaptchaSecretKeyException();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function defaultConfig(): array
    {
        return [
            /**
             * @var string $sitekey Recaptcha v2 Site key (required)
             */
            'sitekey'   => null,
            /**
             * @var string $secretkey Recaptcha v2 Secret key (required)
             */
            'secretkey' => null,
            /**
             * @var string $locale format ISO 15897.
             */
            'locale'    => null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getHandleResponse(): ReCaptchaResponse
    {
        $request = $this->httpRequest();

        return $this->reCaptchaDriver()->verify($request->get('g-recaptcha-response'), $request->getClientIp());
    }

    /**
     * @inheritDoc
     */
    public function getJsScripts(): string
    {
        if ($this->widgets) {
            $lang = $this->getLanguage();

            $js = "let reCaptchaEl = {};";
            $js .= "function reCaptchaCallback() {";
            foreach ($this->widgets as $id => $params) {
                try {
                    $params = json_encode($params, JSON_THROW_ON_ERROR);
                } catch (Exception $e) {
                    $params = '{}';
                }

                $js .= "reCaptchaEl['$id']=document.getElementById('$id');";
                $js .= "if(typeof(reCaptchaEl['$id'])!='undefined' && reCaptchaEl['$id']!=null){";
                $js .= "try{grecaptcha.render('$id', $params);} catch(error){console.log(error);}";
                $js .= "}";
            }
            $js .= "};";
            $js .= "let recaptchaScriptInitialized = false;";
            $js .= "const recaptchaObserver = new IntersectionObserver(";
            $js .= "entries => {";
            $js .= "for(const entry of entries){";
            $js .= "    if (recaptchaScriptInitialized) {";
            $js .= "        recaptchaObserver.unobserve(entry.target);";
            $js .= "        return;";
            $js .= "    }";
            $js .= "    if (entry.isIntersecting) {";
            $js .= "        let recaptchaScript = document.createElement('script');";
            $js .= "        recaptchaScript.src = 'https://www.google.com/recaptcha/api.js?hl=$lang&onload=reCaptchaCallback&render=explicit';";
            $js .= "        recaptchaScript.defer = true;";
            $js .= "        document.body.appendChild(recaptchaScript);";
            $js .= "        recaptchaScriptInitialized = true;";
            $js .= "        console.log('Recaptcha script is initialized');";
            $js .= "    }";
            $js .= "}";
            $js .= "},";
            $js .= "{";
            $js .= "root: document.querySelector('.page-wrapper'),";
            $js .= "rootMargin: \"0px\",";
            $js .= "threshold: 1.0,";
            $js .= "}";
            $js .= ");";
            foreach ($this->widgets as $id => $params) {
                $js .= "recaptchaObserver.observe(document.getElementById('$id'));";
            }

            return "<!-- Recaptcha Scripts -->" .
                "<script type=\"text/javascript\">/* <![CDATA[ */$js/* ]]> */</script>" .
                "<!-- / Recaptcha Scripts -->";
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(): string
    {
        switch ($locale = (string)$this->config('locale', 'en_US')) {
            default :
                [$lang] = explode("_", $locale, 1);
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
        return $this->config('sitekey');
    }

    /**
     * @inheritDoc
     */
    public function isValidated(): bool
    {
        return $this->getHandleResponse()->isSuccess();
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
}