<?php

declare (strict_types=1);
namespace ECSPrefix20210602;

use ECSPrefix20210602\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ECSPrefix20210602\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;
use ECSPrefix20210602\Symplify\Skipper\ValueObject\Option;
use ECSPrefix20210602\Symplify\SmartFileSystem\Normalizer\PathNormalizer;
return static function (\ECSPrefix20210602\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\ECSPrefix20210602\Symplify\Skipper\ValueObject\Option::SKIP, []);
    $parameters->set(\ECSPrefix20210602\Symplify\Skipper\ValueObject\Option::ONLY, []);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('ECSPrefix20210602\Symplify\Skipper\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Bundle', __DIR__ . '/../src/HttpKernel', __DIR__ . '/../src/ValueObject']);
    $services->set(\ECSPrefix20210602\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker::class);
    $services->set(\ECSPrefix20210602\Symplify\SmartFileSystem\Normalizer\PathNormalizer::class);
};
