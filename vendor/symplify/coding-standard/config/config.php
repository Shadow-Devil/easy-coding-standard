<?php

declare (strict_types=1);
namespace ECSPrefix20220612;

use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use ECSPrefix20220612\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\Caching\ChangedFilesDetector;
use ECSPrefix20220612\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    $services->load('Symplify\\CodingStandard\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/TokenRunner/ValueObject', __DIR__ . '/../src/TokenRunner/Exception', __DIR__ . '/../src/Fixer', __DIR__ . '/../src/ValueObject']);
    $services->set(NamespaceUsesAnalyzer::class);
    $services->set(FunctionsAnalyzer::class);
    $services->set(PrivatesAccessor::class);
    $services->set(ChangedFilesDetector::class);
};
