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
        $template = $twig->loadTemplate('index.htm.twig');

        $content['title'] = $name;
        $content['description'] = $data->description;
        $content['aliases'] = $data->aliases;

        // Parse arguments so they make sense to twig.
        $key = 0;
        foreach ($data->arguments as $option => $text) {
            // @TODO there are object that need dealing with here.
            if (!is_object($text) && $text != null) {
                $content['arguments'][$key]['argument'] = $option;
                $content['arguments'][$key]['argumenttext'] = $text;
                $key++;
            }
        }

        $key = 0;
        foreach ($data->options as $option => $text) {
            // @TODO there are object that need dealing with here.
            if (!is_object($text) && $text != null) {
                $content['options'][$key]['option'] = $option;
                $content['options'][$key]['optiontext'] = $text;
                $key++;
            }
        }

        $key = 0;
        foreach ($data->examples as $option => $text) {
            // @TODO there are object that need dealing with here.
            if (!is_object($text) && $text != null) {
                $content['examples'][$key]['example'] = $option;
                $content['examples'][$key]['exampletext'] = $text;
                $key++;
            }
        }

        $content = $template->render($content);
        return $content;
    }
}
