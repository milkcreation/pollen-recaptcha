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
use Pollen\Support\Concerns\ContainerAwareTrait;
use Pollen\Support\Concerns\EventDispatcherAwareTrait;
use Pollen\Support\Concerns\FieldManagerAwareTrait;
use Pollen\Support\Concerns\FormManagerAwareTrait;
use Pollen\Support\Concerns\HttpRequestAwareTrait;
use Pollen\Support\Filesystem;
use Psr\Container\ContainerInterface as Container;
use ReCaptcha\ReCaptcha as ReCaptchaDriver;
use ReCaptcha\Response as ReCaptchaResponse;
use ReCaptcha\RequestMethod\SocketPost as ReCaptchaSocket;
use RuntimeException;

class Recaptcha implements RecaptchaInterface
{
    use BootableTrait;
    use ConfigBagAwareTrait;
    use ContainerAwareTrait;
    use EventDispatcherAwareTrait;
    use FieldManagerAwareTrait;
    use FormManagerAwareTrait;
    use HttpRequestAwareTrait;

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
     * Chemin vers le répertoire des ressources.
     * @var string|null
     */
    protected $resourcesBaseDir;

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
        throw new RuntimeException(sprintf('Unavailable [%s] instance', __CLASS__));
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
            $this->eventDispatcher()->trigger('recaptcha.booting', [&$this]);

            $this->fieldManager()->register(
                'recaptcha',
                $this->containerHas(RecaptchaField::class)
                    ? RecaptchaField::class : new RecaptchaField($this, $this->fieldManager())
            );

            $this->formManager()->registerFieldDriver(
                'recaptcha',
                $this->containerHas(RecaptchaFormField::class)
                    ? RecaptchaFormField::class : new RecaptchaFormField($this)
            );

            $this->setBooted();

            $this->eventDispatcher()->trigger('recaptcha.booted', [&$this]);
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

                $js .= "reCaptchaEl['{$id}']=document.getElementById('{$id}');";
                $js .= "if(typeof(reCaptchaEl['{$id}'])!='undefined' && reCaptchaEl['{$id}']!=null){";
                $js .= "try{grecaptcha.render('{$id}', {$params});} catch(error){console.log(error);}";
                $js .= "}";
            }
            $js .= "};";
            $output = "<script type=\"text/javascript\">{$js}</script>";
            $output .= "<script type=\"text/javascript\" src=\"https://www.google.com/recaptcha/api.js?hl={$lang}&onload=reCaptchaCallback&render=explicit\" async defer></script>";

            return $output;
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

    /**
     * @inheritDoc
     */
    public function resources(?string $path = null): string
    {
        if ($this->resourcesBaseDir === null) {
            $this->resourcesBaseDir = Filesystem::normalizePath(
                realpath(
                    dirname(__DIR__) . '/resources/'
                )
            );

            if (!file_exists($this->resourcesBaseDir)) {
                throw new RuntimeException('Recaptcha ressources directory unreachable');
            }
        }

        return is_null($path) ? $this->resourcesBaseDir : $this->resourcesBaseDir . Filesystem::normalizePath($path);
    }

    /**
     * @inheritDoc
     */
    public function setResourcesBaseDir(string $resourceBaseDir): RecaptchaInterface
    {
        $this->resourcesBaseDir = Filesystem::normalizePath($resourceBaseDir);

        return $this;
    }
}