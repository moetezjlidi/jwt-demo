<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        if (isset($_ENV['NOW_REGION']) || isset($_ENV['VERCEL_REGION'])) {
            return '/tmp/symfony/cache/' . $this->environment;
        }

        return parent::getCacheDir();
    }

    public function getLogDir(): string
    {
        if (isset($_ENV['NOW_REGION']) || isset($_ENV['VERCEL_REGION'])) {
            return '/tmp/symfony/log';
        }

        return parent::getLogDir();
    }
}