<?php


namespace AbmmHasan\Toolbox\Storage;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class File
{
    /**
     * Get the List of files for a location
     *
     * @param $scan_dir
     * @param bool $replace
     * @return array
     */
    public static function list($scan_dir, $replace = false)
    {
        $resource = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($scan_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $files = [];
        foreach ($resource as $file) {
            $files[] = $replace
                ? str_replace([$scan_dir, ".php"], "", $file->getRealPath())
                : $file->getRealPath();
        }
        return $files;
    }

    /**
     * Get the List of directories for a location
     *
     * @param $scan_dir
     * @param bool $replace
     * @return array
     */
    public static function directories($scan_dir, $replace = false)
    {
        $resource = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($scan_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $dir = [];
        foreach ($resource as $file) {
            if ($file->isDir()) {
                $dir[] = $replace
                    ? str_replace($scan_dir, "", $file->getRealPath())
                    : $file->getRealPath();
            }
        }
        return $dir;
    }

    /**
     * Checks if the given filePath is valid.
     *
     * @param $filePath
     * @return bool
     */
    public static function isReadable($filePath)
    {
        if (!is_readable($filePath) || !is_file($filePath)) {
            return false;
        }
        return true;
    }

    /**
     * Checks if Path exists (file/directory)
     * Warning: The result of this function is cached
     *
     * @param $filePath
     * @return bool
     */
    public static function exists($filePath)
    {
        return file_exists($filePath);
    }

    /**
     * Read lines from the file, auto detecting line endings.
     *
     * @param string $filePath
     * @return array
     */
    public static function readLines(string $filePath)
    {
        // Read file into an array of lines with auto-detected line endings
        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        ini_set('auto_detect_line_endings', $autodetect);
        return $lines;
    }
}