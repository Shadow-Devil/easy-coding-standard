<?php

declare (strict_types=1);
namespace ECSPrefix20220115;

use ECSPrefix20220115\SebastianBergmann\Diff\Differ;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ECSPrefix20220115\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('ECSPrefix20220115\Symplify\ConsoleColorDiff\\', __DIR__ . '/../src');
    $services->set(\ECSPrefix20220115\SebastianBergmann\Diff\Differ::class);
    $services->set(\ECSPrefix20220115\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
};
