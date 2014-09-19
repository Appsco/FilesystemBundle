<?php
namespace Appsco\FilesystemBundle\Adapter\Local;

use Appsco\FilesystemBundle\Model\File;
use Appsco\FilesystemBundle\Adapter\Adapter;
use Symfony\Component\Filesystem\Exception\IOException;

class Local extends Adapter
{

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var bool
     */
    private $createIfNotExists;

    /**
     * @var string
     */
    private $container;

    public function __construct(Filesystem $fs, $createIfNotExists = false)
    {
        $this->fs = $fs;
        $this->createIfNotExists = $createIfNotExists;
    }

    /**
     * @param string $volume
     * @return $this
     * @throws \RuntimeException
     */
    public function getVolume($volume)
    {
        if (!$this->fs->exists($volume)) {
            if ($this->createIfNotExists) {
                $this->fs->mkdir($volume);
            } else {
                throw new \RuntimeException("Specified driver isn't registered");
            }

        }
        $this->container = $volume;

        return $this;
    }

    /**
     * Reads the content of the file
     *
     * @param string $key
     *
     * @return File|boolean if cannot read content
     */
    public function read($key)
    {
        if (true === $this->isDirectory($key)) {
            return false;
        }

        return $this->fs->read($this->getRealPath($key));
    }

    /**
     * Writes the given content into the file
     *
     * @param string $key
     * @param string $content
     *
     * @return integer|boolean The number of bytes that were written into the file
     */
    public function write($key, $content)
    {
        $path = $this->getRealPath($key);

        try {
            if ($this->exists($path)) {
                $this->fs->remove($path);
            }
            $this->fs->dumpFile($path, $content);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Creates directory, if name is provided with `/` and recursive is on it will try to crete directory recursively
     *
     * @param string $dir
     * @param bool $recursive
     * @return boolean
     */
    public function mkdir($dir, $recursive = true)
    {
        try {
            $this->fs->mkdir($this->getRealPath($dir));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Indicates whether the file exists
     *
     * @param string $key
     *
     * @return boolean
     */
    public function exists($key)
    {
        return $this->fs->exists($key);
    }

    /**
     * Returns an array of all keys (files and directories)
     *
     * @return array
     */
    public function keys($path = null, $recursive = false)
    {
        $keys = array();

        $path = str_replace(
            [
                DIRECTORY_SEPARATOR . '..',
                '..' . DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR . '.',
                '.' . DIRECTORY_SEPARATOR,
            ],
            '',
            $path
        );

        if (!$list = @scandir($this->getRealPath($path))) {
            return [];
        }

        foreach ($list as $name) {
            if (in_array($name, ['.', '..'])) {
                continue;
            }
            $keys[$path . DIRECTORY_SEPARATOR . $name] = $this->fs->read($this->getRealPath($path . DIRECTORY_SEPARATOR . $name), false);
        }

        return $keys;
    }

    /**
     * Returns the last modified time
     *
     * @param string $key
     *
     * @return integer|boolean An UNIX like timestamp or false
     */
    public function mtime($key)
    {
        // clearstatcache();
        return @filemtime($this->getRealPath($key));
    }

    /**
     * Deletes the file
     *
     * @param string $key
     *
     * @return boolean
     */
    public function delete($key)
    {
        try {
            $this->fs->remove($this->getRealPath($key));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Renames a file
     *
     * @param string $sourceKey
     * @param string $targetKey
     *
     * @return boolean
     */
    public function rename($sourceKey, $targetKey)
    {
        try {
            $this->fs->rename($this->getRealPath($sourceKey), $this->getRealPath($targetKey), true);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Check if key is directory
     *
     * @param string $key
     *
     * @return boolean
     */
    public function isDirectory($key)
    {
        return is_dir($this->getRealPath($key));
    }

    private function getRealPath($key)
    {
        return $this->container . DIRECTORY_SEPARATOR . $key;
    }
} 