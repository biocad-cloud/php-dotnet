<?php

/**
 * Represents an immutable regular expression.To browse the .NET Framework source code for 
 * this type, see the Reference Source.
*/
class Regex {

    # Regex.Match(String, String) As System.Text.RegularExpressions.Match
    
    /**
     * Searches the input string for the first occurrence of the specified regular expression, 
     * using the specified matching options.
     * 
     * @param string $input The string to search for a match.
     * @param string $pattern The regular expression pattern to match.
     * 
     * @return string An object that contains information about the match.
    */
    public static function Match($input, $pattern, $options = PREG_PATTERN_ORDER, $flags = null) {
        $hit = self::Matches($input, $pattern, $options, $flags);
        
        if (Utils::isDbNull($hit)) {
            return null;
        } else {
            return $hit[0];
        }
    }

    /**
     * Searches the specified input string for all occurrences of a specified regular 
     * expression, using the specified matching options.
     *
     * @param string $input The string to search for a match.
     * @param string $pattern The regular expression pattern to match.
     * @param integer $options A bitwise combination of the enumeration values that 
     *                         specify options for matching.
     * 
     * @return array A collection of the System.Text.RegularExpressions.Match objects 
     *               found by the search. If no matches are found, the method returns 
     *               an empty collection object.
    */
    public static function Matches($input, $pattern, $options = PREG_PATTERN_ORDER, $flags = null) {
        $pattern = "/$pattern/";
        $hits    = 0;
        $matches = null;

        if ($flags) {
            $pattern .= $flags;
        }

        if (empty($options)) {
            $hits = preg_match_all($pattern, $input, $matches);
        } else {
            $hits = preg_match_all($pattern, $input, $matches, $options);
        }

        if ($hits > 0) {
            return $matches[0];
        } else {
            return NULL;
        }
    }

    # Regex.Replace(String, String, String) As String

    /**
     * In a specified input string, replaces all strings that match a specified regular expression 
     * with a specified replacement string.
     * 
     * @param string $input The string to search for a match.
     * @param string $pattern The regular expression pattern to match.
     * @param string $replacement The replacement string.
     *
     * @return string A new string that is identical to the input string, except that the replacement 
     *                string takes the place of each matched string. If pattern is not matched in the 
     *                current instance, the method returns the current instance unchanged.
    */
    public static function Replace($input, $pattern, $replacement) {
        # return $input;
        return preg_replace("#$pattern#", $replacement, $input);
    }

    public static function Split($input, $pattern) {
        return preg_split("#$pattern#", $input);
    }
}
