<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit61c7d30b9ee5737a70b281a9ebbaed63
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit61c7d30b9ee5737a70b281a9ebbaed63', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit61c7d30b9ee5737a70b281a9ebbaed63', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit61c7d30b9ee5737a70b281a9ebbaed63::getInitializer($loader));

        $loader->setClassMapAuthoritative(true);
        $loader->register(true);

        $filesToLoad = \Composer\Autoload\ComposerStaticInit61c7d30b9ee5737a70b281a9ebbaed63::$files;
        $requireFile = \Closure::bind(static function ($fileIdentifier, $file) {
            if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
                $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;

                require $file;
            }
        }, null, null);
        foreach ($filesToLoad as $fileIdentifier => $file) {
            $requireFile($fileIdentifier, $file);
        }

        return $loader;
    }
}
