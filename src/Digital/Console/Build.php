<?php
namespace Digital\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use sqlite3;
use Twig_Autoloader;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class Build extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Builds html from commands.json')
            ->addOption(
                'funky',
                null,
                InputOption::VALUE_NONE,
                'OH YEAH FUNKAY TIME!'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = json_decode(file_get_contents('output/commands.json'));

        $process = new Process('rm drush.docset/Contents/Resources/docSet.dsidx');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output->writeln($process->getOutput());

        if ($input->getOption('funky')) {
            $output->writeln('<error>Removed database</error>');
        } else {
            $output->writeln('Removed database');
        }
        $db = new sqlite3("drush.docset/Contents/Resources/docSet.dsidx");
        $db->query("CREATE TABLE searchIndex(id INTEGER PRIMARY KEY, name TEXT, type TEXT, path TEXT)");
        $db->query("CREATE UNIQUE INDEX anchor ON searchIndex (name, type, path)");
        if ($input->getOption('funky')) {
            $output->writeln('<info>Created database</info>');
        } else {
            $output->writeln('Created database');
        }
        foreach ($file as $key => $contents) {
            foreach ($contents as $command) {
                if (is_object($command)) {
                    foreach ($command as $name => $desc) {
                        $content = new Parse();
                        $content = $content->parseHtml($name, $desc);
                        $file = fopen('drush.docset/Contents/Resources/Documents/' . $name . '.htm', 'w+');
                        fwrite($file, $content);
                        $filename = $name . '.htm';
                        $db->query("INSERT OR IGNORE INTO searchIndex(name, type, path) VALUES (\"$name\",\"Command\",\"$filename\")");
                    }
                }
            }
        }
        if ($input->getOption('funky')) {
            $output->writeln('<comment>Built html documentation</comment>');
        } else {
            $output->writeln('Built html documentation');
        }

        $process = new Process("tar --exclude='.DS_Store' -cvzf Drush.tgz drush.docset");
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output->writeln($process->getOutput());

        if ($input->getOption('funky')) {
            $output->writeln('<info>Deleting old index.htm</info>');
        } else {
            $output->writeln('Deleting old index.htm');
        }

        $process = new Process("rm drush.docset/Contents/Resources/Documents/index.htm");
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output->writeln($process->getOutput());

        Twig_Autoloader::register();
        $content = array();
        $gentime = new \DateTime("now");
        $content['gentime'] = $gentime->format('l, d-M-y H:i:s T');

        $loader = new Twig_Loader_Filesystem('src/Digital/Console/Views');
        $twig = new Twig_Environment($loader, array());
        $template = $twig->loadTemplate('index.twig');
        $content = $template->render($content);
        $file = fopen('drush.docset/Contents/Resources/Documents/index.htm', 'w+');
        fwrite($file, $content);

        if ($input->getOption('funky')) {
            $output->writeln('<error>Built Drush.tgz</error>');
        } else {
            $output->writeln('Built Drush.tgz');
        }

        if ($input->getOption('funky')) {
            $output->writeln('<info>Complete</info>');
        } else {
            $output->writeln('Completrm drush.docset/Contents/Resources/Documents/index.htm');
        }
    }
}
