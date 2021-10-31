<?php

declare (strict_types=1);
namespace ECSPrefix20211031\Symplify\SymfonyContainerBuilder;

use ECSPrefix20211031\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use ECSPrefix20211031\Symfony\Component\DependencyInjection\ContainerBuilder;
use ECSPrefix20211031\Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use ECSPrefix20211031\Symplify\SymfonyContainerBuilder\Config\Loader\ParameterMergingLoaderFactory;
use ECSPrefix20211031\Symplify\SymfonyContainerBuilder\DependencyInjection\LoadExtensionConfigsCompilerPass;
use ECSPrefix20211031\Webmozart\Assert\Assert;
final class ContainerBuilderFactory
{
    /**
     * @var \Symplify\SymfonyContainerBuilder\Config\Loader\ParameterMergingLoaderFactory
     */
    private $parameterMergingLoaderFactory;
    public function __construct()
    {
        $this->parameterMergingLoaderFactory = new \ECSPrefix20211031\Symplify\SymfonyContainerBuilder\Config\Loader\ParameterMergingLoaderFactory();
    }
    /**
     * @param ExtensionInterface[] $extensions
     * @param CompilerPassInterface[] $compilerPasses
     * @param string[] $configFiles
     */
    public function create(array $extensions, array $compilerPasses, array $configFiles) : \ECSPrefix20211031\Symfony\Component\DependencyInjection\ContainerBuilder
    {
        \ECSPrefix20211031\Webmozart\Assert\Assert::allString($configFiles);
        \ECSPrefix20211031\Webmozart\Assert\Assert::allFile($configFiles);
        $containerBuilder = new \ECSPrefix20211031\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->registerExtensions($containerBuilder, $extensions);
        $this->registerCompilerPasses($containerBuilder, $compilerPasses);
        $this->registerConfigFiles($containerBuilder, $configFiles);
        // this calls load() method in every extensions
        // ensure these extensions are implicitly loaded
        $compilerPassConfig = $containerBuilder->getCompilerPassConfig();
        $compilerPassConfig->setMergePass(new \ECSPrefix20211031\Symplify\SymfonyContainerBuilder\DependencyInjection\LoadExtensionConfigsCompilerPass());
        return $containerBuilder;
    }
    /**
     * @param ExtensionInterface[] $extensions
     */
    private function registerExtensions(\ECSPrefix20211031\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, array $extensions) : void
    {
        foreach ($extensions as $extension) {
            $containerBuilder->registerExtension($extension);
        }
    }
    /**
     * @param CompilerPassInterface[] $compilerPasses
     */
    private function registerCompilerPasses(\ECSPrefix20211031\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, array $compilerPasses) : void
    {
        foreach ($compilerPasses as $compilerPass) {
            $containerBuilder->addCompilerPass($compilerPass);
        }
    }
    /**
     * @param string[] $configFiles
     */
    private function registerConfigFiles(\ECSPrefix20211031\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, array $configFiles) : void
    {
        $delegatingLoader = $this->parameterMergingLoaderFactory->create($containerBuilder, \getcwd());
        foreach ($configFiles as $configFile) {
            $delegatingLoader->load($configFile);
        }
    }
}