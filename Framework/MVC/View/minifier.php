<?php

namespace MVC\Views;

/** 
 * A PHP package to minify HTML strings and remove CSS and Javascript.
 * 
 * > https://github.com/doncadavona/html_minifier
*/
class HtmlMinifier {

    /**
     * Returns a minified version of the given HTML.
     * 
     * @param  string  $html       The original HTML content string or its file path.
     * @param  boolean $remove_css Defines whether to remove CSS or not.
     * @param  boolean $remove_js  Defines whether to remove Javascripts or not.
     * 
     * @return string              The minified version of the given HTML.
    */
    public static function minify($html, $remove_js = false, $remove_css = false) {
        if (\FileSystem::isValidPath($html)) {
            \console::log("The given html resource is a file path.");
            # Read html text if resource is a valid file path.
            $html = \file_get_contents($html);
        } 

        $sizeOf = strlen($html);
        \console::log("data size before minify: " . $sizeOf);

        /**
         * The set of regular expressions to match against
         * the given HTML and their respective replacements.
         * Reference: https://github.com/ogheo/yii2-htmlcompress
         * @var array
        */
        $filters = [
            // remove javascript comments
            '/(?:<script[^>]*>|\G(?!\A))(?:[^\'"\/<]+|"(?:[^\\"]+|\\.)*"|\'(?:[^\\\']+|\\.)*\'|\/(?!\/)|<(?!\/script))*+\K\/\/[^\n|<]*/xsu' => '',
            // remove html comments except IE conditions
            '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/su' => '',
            // remove comments in the form /* */
            '/\/+?\s*\*[\s\S]*?\*\s*\/+/u' => '',
            // shorten multiple white spaces
            '/>\s{2,}</u' => '><',
            // shorten multiple white spaces
            '/\s{2,}/u' => ' ',
            // collapse new lines
            '/(\r?\n)/u' => '',
        ];

        // Add javascript remover if specified.
        if ($remove_js) {
            $filters['/<script\b[^>]*>([\s\S]*?)<\/script>/'] = '';
            
            // Unset the javascript comments remover when the entire script is already removed.
            unset($filters['/(?:<script[^>]*>|\G(?!\A))(?:[^\'"\/<]+|"(?:[^\\"]+|\\.)*"|\'(?:[^\\\']+|\\.)*\'|\/(?!\/)|<(?!\/script))*+\K\/\/[^\n|<]*/xsu']);
        }

        // Add CSS remover if specified.
        if ($remove_css) {
            $filters['/<style\b[^>]*>([\s\S]*?)<\/style>/'] = '';
        }

        $raw  = $html;
        $html = preg_replace(array_keys($filters), array_values($filters), $html);

        if (strlen($html) == 0 && $sizeOf > 0) {
            \console::log("The page is not minify as there is a syntax error in your html file, please check for your file.");
            return $raw;
        } else {
            return $html;
        }
    }
}
