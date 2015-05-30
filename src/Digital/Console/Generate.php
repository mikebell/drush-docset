<?php
namespace Digital\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Digital\Console\Parse;

class Generate extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generate data for all modules')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $text = 'Generating help documentation';

        $yaml = new Parser();

        $modules = $yaml->parse(file_get_contents('modules.yml'));
        foreach ($modules['modules'] as $module) {
            exec('vendor/bin/drush dl ' . $module . ' --destination=' . $_SERVER['HOME']. '/.drush -y');
        }
        exec('vendor/bin/drush help --format=json > output/commands.json');

        $output->writeln($text);
    }
}
