<?

class ZipLib {

    private static $ZipStatusString = array(
        ZipArchive::ER_OK          => 'N No error',
        ZipArchive::ER_MULTIDISK   => 'N Multi-disk zip archives not supported',
        ZipArchive::ER_RENAME      => 'S Renaming temporary file failed',
        ZipArchive::ER_CLOSE       => 'S Closing zip archive failed',
        ZipArchive::ER_SEEK        => 'S Seek error',
        ZipArchive::ER_READ        => 'S Read error',
        ZipArchive::ER_WRITE       => 'S Write error',
        ZipArchive::ER_CRC         => 'N CRC error',
        ZipArchive::ER_ZIPCLOSED   => 'N Containing zip archive was closed',
        ZipArchive::ER_NOENT       => 'N No such file',
        ZipArchive::ER_EXISTS      => 'N File already exists',
        ZipArchive::ER_OPEN        => 'S Can\'t open file',
        ZipArchive::ER_TMPOPEN     => 'S Failure to create temporary file',
        ZipArchive::ER_ZLIB        => 'Z Zlib error',
        ZipArchive::ER_MEMORY      => 'N Malloc failure',
        ZipArchive::ER_CHANGED     => 'N Entry has been changed',
        ZipArchive::ER_COMPNOTSUPP => 'N Compression method not supported',
        ZipArchive::ER_EOF         => 'N Premature EOF',
        ZipArchive::ER_INVAL       => 'N Invalid argument',
        ZipArchive::ER_NOZIP       => 'N Not a zip archive',
        ZipArchive::ER_INTERNAL    => 'N Internal error',
        ZipArchive::ER_INCONS      => 'N Zip archive inconsistent',
        ZipArchive::ER_REMOVE      => 'S Can\'t remove file',
        ZipArchive::ER_DELETED     => 'N Entry has been deleted'
    );
    
    public static function ToString($status) {
        if (array_key_exists($status, ZipLib::$ZipStatusString)) {
            return ZipLib::$ZipStatusString[$status];
        } else {
            return sprintf('Unknown status %s', $status);
        }
    }

    /** 
     * Add files and sub-directories in a folder to zip file. 
     * 
     * @param string $folder 
     * @param ZipArchive $zipFile 
     * @param int $exclusiveLength Number of text to be exclusived from the file path. 
     * 
     */ 
    private static function folderToZip($folder, &$zipFile, $exclusiveLength) { 
        $handle = opendir($folder); 

        while (false !== $f = readdir($handle)) { 
            if ($f != '.' && $f != '..') { 

                $filePath = "$folder/$f"; 
                // Remove prefix from file path before add to zip. 
                $localPath = substr($filePath, $exclusiveLength); 

                if (is_file($filePath)) { 
                    $zipFile->addFile($filePath, $localPath); 
                } elseif (is_dir($filePath)) { 
                    // Add sub-directory. 
                    $zipFile->addEmptyDir($localPath); 

                    self::folderToZip(
                        $filePath, 
                        $zipFile, 
                        $exclusiveLength
                    ); 
                }
            }
        }

        closedir($handle); 
    } 

    /** 
     * Zip a folder (include itself). 
     * 
     * http://php.net/manual/en/class.ziparchive.php
     * 
     * Usage: 
     *   ZipLib::ZipDirectory('/path/to/sourceDir', '/path/to/out.zip'); 
     * 
     * @param string $sourcePath Path of directory to be zip. 
     * @param string $outZipPath Path of output zip file. 
     */ 
    public static function ZipDirectory($sourcePath, $outZipPath) { 
        $pathInfo   = pathInfo($sourcePath); 
        $parentPath = $pathInfo['dirname']; 
        $dirName    = $pathInfo['basename']; 

        $z = new ZipArchive(); 
        $z->open($outZipPath, ZIPARCHIVE::CREATE); 
        $z->addEmptyDir($dirName); 
        self::folderToZip($sourcePath, $z, strlen("$parentPath/")); 
        $z->close(); 
    }
}
?>