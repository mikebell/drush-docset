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

//
//    $content .= '<ul>';
//    foreach ($data->aliases as $alias) {
//      $content .= '<li>' . $alias . '</li>';
//    }
//    $content .= '</ul>';
//
//    $content .= '<h2>Arguments:</h2>';
//    foreach ($data->arguments as $option => $text) {
//      // @TODO there are object that need dealing with here.
//      if (!is_object($text)) {
//        $content .= '<code>' . $option . ' : ' . $text . '</code>';
//      }
//    }
//    $content .= '<h2>Options:</h2>';
//    foreach ($data->options as $option => $text) {
//      if (!is_object($text)) {
//        $content .= '<code>--' . $option . ' : ' . $text . '</code>';
//      }
//    }
//    $content .= '<h2>Examples:</h2>';
//    foreach ($data->examples as $option => $text) {
//      if (!is_object($text)) {
//        $content .= '<p>' . $text . '</p>';
//        $content .= '<code>drush ' . $option . '</code>';
//      }
//    }
//
//    $content .= '</body>';
//    $content .= '</html>';
        $content = array();
        Twig_Autoloader::register();

        $loader = new Twig_Loader_Filesystem('src/Digital/Console/Views');
        $twig = new Twig_Environment($loader, array());
        $template = $twig->loadTemplate('index.htm.twig');

        $content['title'] = $name;
        $content['aliases'] = $data->aliases;

        $content['arguments'] = $data->arguments;
        $content['options'] = $data->options;
        $content['examples'] = $data->examples;

        $content = $template->render($content);
        return $content;
    }
}
