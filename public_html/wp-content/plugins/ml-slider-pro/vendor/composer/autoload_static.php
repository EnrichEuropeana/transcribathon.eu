<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbecbf11456fd9673a9ffea7bab297ee6
{
    public static $files = array (
        '7166494aeff09009178f278afd86c83f' => __DIR__ . '/..' . '/yahnis-elsts/plugin-update-checker/load-v4p13.php',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitbecbf11456fd9673a9ffea7bab297ee6::$classMap;

        }, null, ClassLoader::class);
    }
}
