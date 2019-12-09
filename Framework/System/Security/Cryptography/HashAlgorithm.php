<?php

imports("System.IO.File");

class HashAlgorithm {

    /**
     * Compute file md5 hash value
     * 
     * @param string $path The file path string value
     * @param integer $largeFile The file size cutoff value for large file in unit byte.
     * 
     * @return string|boolean File md5 string text, false for file not exists.
    */
    public static function file_md5($path, $largeFile = 1024 * 1024 * 64) {
        if (!file_exists($path)) {
            return false;
        } else if (filesize($path) <= $largeFile) {
            return md5_file($path);
        }

        return using(FileStream::OpenReadOnly($path), function($file) {
            return self::largeFile_md5($file);
        });
    }

    /**
     * Compute md5 hash value for a ultra large file object
     * 
     * @param FileStream $file A file stream wrapper object
     * 
     * @return string The md5 string value of the given file object
    */
    private static function largeFile_md5($file) {
        $minLen  = 10 * 1024 * 1024;
        $readLen = intval(max($file->Length() / 10, $minLen)); 
        $section = intval($file->Length() / 6);

        # A B C D E
        # assuming that first 8 bytes is the magic bytes
        $file->Seek(8);
        $A = md5($file->Read($readLen));
        $file->Seek($section);
        $B = md5($file->Read($readLen));
        $file->Seek($section * 2);
        $C = md5($file->Read($readLen));
        $file->Seek($section * 3);
        $D = md5($file->Read($readLen));
        $file->Seek(-$section, 2);
        $E = md5($file->Read($readLen));

        $all = md5("$A|$B|$E|$D|$C ~ " . $file->Length());
        
        return $all;
    }
}