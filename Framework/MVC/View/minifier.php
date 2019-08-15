<?php

namespace DonCadavona\HtmlMinifier;

class HtmlMinifier
{
    /**
     * Create a new HtmlMinifier Instance
     */
    public function __construct()
    {
        // constructor body
    }

    /**
     * Returns a minified version of the given HTML.
     * @param  string  $html       The original HTML.
     * @param  boolean $remove_css Defines whether to remove CSS or not.
     * @param  boolean $remove_js  Defines whether to remove Javascripts or not.
     * @return string              The minified version of the given HTML.
     */
    public static function minify($html, $remove_js = false, $remove_css = false)
    {
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

        return preg_replace(array_keys($filters), array_values($filters), $html);
    }
}
