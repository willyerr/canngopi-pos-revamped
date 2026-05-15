<?php

namespace App\Helpers;

class ToastHelper
{
    private $toast = null;

    public function __construct($toast)
    {
        $this->toast = $toast;
    }

    public function success(string $message, bool $flash = false)
    {
        return $flash ? 
            $this->toast->success($message)->flash()->send() :
            $this->toast->success($message)->send();
    }

    public function error(string $message)
    {
        return $this->toast->error($message)->send();
    }

    public function info(string $message)
    {
        return $this->toast->info($message)->send();
    }

    public function warning(string $message)
    {
        return $this->toast->warning($message)->send();
    }
}
