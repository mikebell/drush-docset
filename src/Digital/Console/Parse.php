<?php
/**
 * Created by PhpStorm.
 * User: mbell
 * Date: 29/05/15
 * Time: 09:41
 */

namespace Digital\Console;

use Twig_Autoloader;
use Twig_Loader_Filesystem;
use Twig_Environment;

class Parse
{
    public function parseHtml($name, $data)
    {
        // Take php object/array and parse it to html then return.
        $content = array();
        Twig_Autoloader::register();

        $loader = new Twig_Loader_Filesystem('src/Digital/Console/Views');
        $twig = new Twig_Environment($loader, array());
        $template = $twig->loadTemplate('command.twig');

        $content['title'] = $name;
        $content['description'] = $data->description;
        $content['aliases'] = $data->aliases;

        $options = array(
            'arguments' => 'argument',
            'options' => 'option',
            'examples' => 'example'
        );

        foreach ($options as $option_plural => $option) {
            // Parse arguments so they make sense to twig.
            foreach ($data->$option_plural as $option => $text) {
                // @TODO there are object that need dealing with here.
                if (!is_object($text) && $text != null) {
                    $content[$option_plural][$option] = $option;
                    $content[$option_plural][$option] = $text;
                }
            }
        }

        $content = $template->render($content);
        return $content;
    }
}
