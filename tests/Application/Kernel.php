<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Application;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function buildContainer(): ContainerBuilder
    {
        if (class_exists(AnnotationReader::class)) {
            AnnotationReader::addGlobalIgnoredName('MappedSuperclass');
        }

        return parent::buildContainer();
    }
}
