<?php
namespace Digital\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Process\Process;

class Download extends Command
{
    protected function configure()
    {
        $this
            ->setName('download')
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
                $process = new Process('vendor/bin/drush dl ' . $module . ' --destination=' . $_SERVER['HOME']. '/.drush -y');
                $process->run();

                // executes after the command finishes
                if (!$process->isSuccessful()) {
                    throw new \RuntimeException($process->getErrorOutput());
                }

                $output->writeln($process->getOutput());
            }
        }

        $process = new Process('vendor/bin/drush cc drush');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output->writeln($process->getOutput());

        if (file_exists('output/commands.json')) {
          $process = new Process('rm output/commands.json');
          $process->run();
        }

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output->writeln($process->getOutput());

        $process = new Process('vendor/bin/drush help --format=json > output/commands.json');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output->writeln($process->getOutput());

        $output->writeln($text);
    }
}
