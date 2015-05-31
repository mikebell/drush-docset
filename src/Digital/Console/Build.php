<?php
namespace Digital\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use sqlite3;

class Build extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Builds html from commands.json')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = json_decode(file_get_contents('output/commands.json'));
        exec("rm drush.docset/Contents/Resources/docSet.dsidx");
        print 'Removed database' . PHP_EOL;
        $db = new sqlite3("drush.docset/Contents/Resources/docSet.dsidx");
        $db->query("CREATE TABLE searchIndex(id INTEGER PRIMARY KEY, name TEXT, type TEXT, path TEXT)");
        $db->query("CREATE UNIQUE INDEX anchor ON searchIndex (name, type, path)");
        print 'Created database' . PHP_EOL;
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
        print 'Built html documentation' . PHP_EOL;
        exec("tar --exclude='.DS_Store' -cvzf Drush.tgz drush.docset");
        print 'Built Drush.tgz' . PHP_EOL;

        $text = 'Complete'
        $output->writeln($text);
    }
}
