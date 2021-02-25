<?php

declare(strict_types=1);

namespace Pollen\Recaptcha\Exception;

use Throwable;

class RecaptchaSecretKeyException extends RecaptchaConfigException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        if ($message === "") {
            $message = sprintf(
                'Recaptcha v2 Secret Key required, please create and configure : %s',
                $this->onlineConfig
            );
        }

        parent::__construct($message, $code, $previous);
    }
}