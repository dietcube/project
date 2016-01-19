<?php

namespace DietcubeInstaller;

use Composer\Script\Event;
use Composer\Factory;
use Composer\Json\JsonFile;

class Installer
{
    const PLACEHOLDER = 'SampleApp';
    public static function initialize(Event $event)
    {
        $io = $event->getIO();
        $project_dirname = basename(getcwd());
        $current_namespace = self::camelize($project_dirname);

        $io->write("Initialize $current_namespace ...");

        $targets = self::globRecursive('app/*.php');
        $targets = array_merge($targets, self::globRecursive('app/*.html.twig'));
        $targets = array_merge($targets, self::globRecursive('tests/*.php'));
        $targets[] = 'composer.json';
        $targets[] = 'webroot/index.php';

        foreach ($targets as $target) {
            $source_text = file_get_contents($target);
            $new_source = str_replace(self::PLACEHOLDER, $current_namespace, $source_text);
            file_put_contents($target, $new_source);
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
        $autoload['psr-4'][$current_namespace . '\\'] =
            $autoload['psr-4'][self::PLACEHOLDER . '\\'];
        unset($autoload['psr-4'][self::PLACEHOLDER . '\\']);
        unset($autoload['psr-4']['DietcubeInstaller\\']);
        $package->setAutoload($autoload);

        // rewrite json file
        $json = new JsonFile(Factory::getComposerFile());
        $composer_definition = $json->read();
        unset($composer_definition['autoload']['psr-4']['DietcubeInstaller\\']);
        unset($composer_definition['scripts']);
        $json->write($composer_definition);

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
        $io->write("$ cd $project_dirname");
        $io->write('$ DIET_ENV=development php -d variables_order=EGPCS -S 0:8999 -t webroot/');
        $io->write('');
        $io->write('-------------------------------------------------------------------------');

        self::removeMe();
    }

    private static function removeMe()
    {
        unlink(__FILE__);
        rmdir(__DIR__);
    }

    private static function globRecursive($pattern)
    {
        $files = glob($pattern, GLOB_NOSORT);

        $base_pattern = basename($pattern);
        $base_dir = dirname($pattern);
        if ($base_dir) {
            $basepath = "$base_dir/*";
        } else {
            $basepath = '*';
        }

        foreach (glob($basepath, GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, self::globRecursive("$dir/$base_pattern"));
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
