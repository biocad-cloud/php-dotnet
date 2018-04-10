<?php

class Regex {

    # Regex.Match(String, String) As System.Text.RegularExpressions.Match
    public static function Match($input, $pattern) {

    }

    # Regex.Replace(String, String, String) As String

    /**
     * In a specified input string, replaces all strings that match a specified regular expression 
     * with a specified replacement string.
     * 
     * @param input: The string to search for a match.
     * @param pattern: The regular expression pattern to match.
     * @param replacement: The replacement string.
     *
     * @return string: A new string that is identical to the input string, except that the replacement 
     *                 string takes the place of each matched string. If pattern is not matched in the 
     *                 current instance, the method returns the current instance unchanged.
     */
    public static function Replace($input, $pattern, $replacement) {
        # return $input;
        return preg_replace("#$pattern#", $replacement, $input);
    }
}

?>