<?php

namespace DietcubeInstaller;

use Composer\Script\Event;

class Installer
{
    public static function initialize(Event $event)
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $config = $composer->getConfig();

        $currentNamespace = self::camelize(basename(getcwd()));

        $io->write("Initialize $currentNamespace ...");

        $targets = self::globRecursive('app/*.php');
        $targets = array_merge($targets, self::globRecursive('app/*.html.twig'));
        $targets = array_merge($targets, self::globRecursive('tests/*.php'));
        $targets[] = 'composer.json';
        $targets[] = 'webroot/index.php';

        foreach ($targets as $target) {
            $sourceText = file_get_contents($target);
            $newSource = str_replace('SampleApp', $currentNamespace, $sourceText);
            file_put_contents($target, $newSource);
        }
        rename('app/config/config_development.php.sample', 'app/config/config_development.php');
        chmod('tmp', 0777);


        // dumpautoload
        $generator = $composer->getAutoloadGenerator();
        $generator->setDevMode(true);
        $generator->setRunScripts(true);
        $generator->setClassMapAuthoritative(
            $config->get('classmap-authoritative')
        );

        $generator->dump(
            $config,
            $composer->getRepositoryManager()->getLocalRepository(),
            $composer->getPackage(),
            $composer->getInstallationManager(),
            'composer',
            $config->get('optimize-autoloader')
        );
                                                         
        $io->write('-------------------------------------------------------------------------');
        $io->write('');
        $io->write('Dietcube setup completed.');
        $io->write('');
        $io->write('Try now with built-in server:');
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
