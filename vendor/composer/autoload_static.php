<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb85ae86c6076b6c2808333b4cbaae479
{
    public static $files = array (
        'a4ecaeafb8cfb009ad0e052c90355e98' => __DIR__ . '/..' . '/beberlei/assert/lib/Assert/functions.php',
    );

    public static $prefixesPsr0 = array (
        'A' => 
        array (
            'Assert' => 
            array (
                0 => __DIR__ . '/..' . '/beberlei/assert/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInitb85ae86c6076b6c2808333b4cbaae479::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
