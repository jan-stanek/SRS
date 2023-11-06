<?php

declare(strict_types=1);

namespace App\WebModule\Components;

/**
 * Factory komponenty obsahu s přihláškou.
 */
interface IApplicationContentControlFactory
{
    public function create(): ApplicationContentControl;
}
