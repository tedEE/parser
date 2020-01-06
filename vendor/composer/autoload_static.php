<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit861fe7ed47768dbc0c11bac1683cbcbd
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Pars\\' => 5,
        ),
        'H' => 
        array (
            'Hellpers\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Pars\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
        'Hellpers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/hellper',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit861fe7ed47768dbc0c11bac1683cbcbd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit861fe7ed47768dbc0c11bac1683cbcbd::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
