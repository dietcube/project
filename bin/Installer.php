<?php

namespace DietcubeInstaller;

use Composer\Script\Event;

class Installer
{
    const PLACEHOLDER = 'SampleApp';
    public static function initialize(Event $event)
    {
        $io = $event->getIO();
        $rootDir = getcwd();
        $currentNamespace = self::camelize(basename($rootDir));

        $io->write("Initialize $currentNamespace ...");

        $targets = self::globRecursive('app/*.php');
        $targets = array_merge($targets, self::globRecursive('app/*.html.twig'));
        $targets = array_merge($targets, self::globRecursive('tests/*.php'));
        $targets[] = 'composer.json';
        $targets[] = 'webroot/index.php';

        foreach ($targets as $target) {
            $sourceText = file_get_contents($target);
            $newSource = str_replace(self::PLACEHOLDER, $currentNamespace, $sourceText);
            file_put_contents($target, $newSource);
        }
        copy('app/config/config_development.php.sample', 'app/config/config_development.php');
        chmod('tmp', 0777);

        // dumpautoload
        $composer = $event->getComposer();

        $gen = $composer->getAutoloadGenerator();
        $gen->setDevMode(true);

        // rename "autoload"
        $package = $composer->getPackage();
        $autoload = $package->getAutoload();
        $autoload['psr-4'][$currentNamespace . '\\'] =
            $autoload['psr-4'][self::PLACEHOLDER . '\\'];
        unset($autoload['psr-4'][self::PLACEHOLDER . '\\']);
        $package->setAutoload($autoload);

        $gen->dump(
            $composer->getConfig(),
            $composer->getRepositoryManager()->getLocalRepository(),
            $package,
            $composer->getInstallationManager(),
            'composer',
            false //optimize
        );

        $io->write('-------------------------------------------------------------------------');
        $io->write('');
        $io->write('<comment>Dietcube setup completed.</comment>');
        $io->write('');
        $io->write('Try now with built-in server:');
        $io->write("$ cd $rootDir");
        $io->write('$ DIET_ENV=development php -d variables_order=EGPCS -S 0:8999 -t webroot/');
        $io->write('');
        $io->write('-------------------------------------------------------------------------');
    }

    private static function globRecursive($pattern)
    {
        $files = glob($pattern, GLOB_NOSORT);

        $basePattern = basename($pattern);
        $baseDir = dirname($pattern);
        if ($baseDir) {
            $basepath = "$baseDir/*";
        } else {
            $basepath = '*';
        }

        foreach (glob($basepath, GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, self::globRecursive("$dir/$basePattern"));
        }
        return $files;
    }

    private static function camelize($str)
    {
        $str = strtr($str, '_-', '  ');
        $str = ucwords($str);
        return str_replace(' ', '', $str);
    }
}
