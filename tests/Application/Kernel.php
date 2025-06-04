<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Application;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct(
        string $environment,
        bool $debug
    ) {
        parent::__construct($environment, $debug);

        if (class_exists(AnnotationReader::class)) {
            AnnotationReader::addGlobalIgnoredName('MappedSuperclass');
        }
    }
}
