<?php
namespace Appsco\FilesystemBundle\Adapter;

/**
 * Interface for the filesystem adapters
 *
 */
interface FilesystemInterface
{
    /**
     * Reads the content of the file
     *
     * @param string $key
     *
     * @return string|boolean if cannot read content
     */
    public function read($key);

    /**
     * Writes the given content into the file
     *
     * @param string $key
     * @param string $content
     *
     * @return integer|boolean The number of bytes that were written into the file
     */
    public function write($key, $content);

    /**
     * Creates directory, if name is provided with `/` and recursive is on it will try to crete directory recursively
     *
     * @param string $dir
     * @param bool $recursive
     * @return boolean
     */
    public function mkdir($dir, $recursive = true);

    /**
     * Indicates whether the file exists
     *
     * @param string $key
     *
     * @return boolean
     */
    public function exists($key);

    /**
     * Returns an array of all keys (files and directories)
     *
     * @param null $path If null will return list of volume root content
     * @param bool $recursive will go through full list
     *
     * @return array
     */
    public function keys($path = null, $recursive = false);

    /**
     * Returns the last modified time
     *
     * @param string $key
     *
     * @return integer|boolean An UNIX like timestamp or false
     */
    public function mtime($key);

    /**
     * Deletes the file
     *
     * @param string $key
     *
     * @return boolean
     */
    public function delete($key);

    /**
     * Renames a file
     *
     * @param string $sourceKey
     * @param string $targetKey
     *
     * @return boolean
     */
    public function rename($sourceKey, $targetKey);

    /**
     * Check if key is directory
     *
     * @param string $key
     *
     * @return boolean
     */
    public function isDirectory($key);
}
