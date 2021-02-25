<?php

declare(strict_types=1);

namespace Pollen\Recaptcha\Exception;

use LogicException;

class RecaptchaConfigException extends LogicException
{
    /**
     * @var string
     */
    protected $onlineConfig = 'https://www.google.com/recaptcha/about/';
}