<?php
namespace Digital\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

class Generate extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generates commands.json from local drush installation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $text = 'Generating help documentation';

        $yaml = new Parser();

        $modules = $yaml->parse(file_get_contents('modules.yml'));
        if ($modules['modules'] != null) {
            foreach ($modules['modules'] as $module) {
                exec('vendor/bin/drush dl ' . $module . ' --destination=' . $_SERVER['HOME']. '/.drush -y');
            }
        }

        exec('vendor/bin/drush cc drush');
        exec('rm output/commands.json');
        exec('vendor/bin/drush help --format=json > output/commands.json');

        $output->writeln($text);
    }
}
