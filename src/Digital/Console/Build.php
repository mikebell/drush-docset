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
            ->setDescription('Builds html from commands.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = json_decode(file_get_contents('output/commands.json'));

        if (file(drush.docset/Contents/Resources/docSet.dsidx)) {
            $process = new Process('rm drush.docset/Contents/Resources/docSet.dsidx');
            $process->run();
        }

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output->writeln($process->getOutput());

        $output->writeln('<info>Removed database</info>');

        $db = new sqlite3("drush.docset/Contents/Resources/docSet.dsidx");
        $db->query("CREATE TABLE searchIndex(id INTEGER PRIMARY KEY, name TEXT, type TEXT, path TEXT)");
        $db->query("CREATE UNIQUE INDEX anchor ON searchIndex (name, type, path)");

        $output->writeln('<info>Created database</info>');

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

        $output->writeln('<info>Built html documentation</info>');

        $process = new Process("tar --exclude='.DS_Store' -cvzf Drush.tgz drush.docset");
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output->writeln($process->getOutput());

        $output->writeln('<info>Deleting old index.htm</info>');

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

        $output->writeln('<info>Built Drush.tgz</info>');

        $output->writeln('<info>Complete</info>');
    }
}
