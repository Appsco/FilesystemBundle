<?php

namespace Appsco\FilesystemBundle\Adapter\Local;

use Appsco\FilesystemBundle\Model\File;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class Filesystem extends SymfonyFilesystem
{
    /**
     * Reads a file
     *
     * @param string $key
     * @param bool $includeContent
     *
     * @return File|boolean
     */
    public function read($key, $includeContent = true)
    {
        if (!$this->exists($key) || !is_readable($key)) {
            return false;
        }

        $info = new \SplFileInfo($key);

        $file = new File();
        $file->size = $info->getSize();
        $file->name = $info->getFilename();

        if ($includeContent) {
            $file->content = file_get_contents($info->getRealPath());
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $file->mime = finfo_file($fileInfo, $info->getRealPath());
        finfo_close($fileInfo);

        return $file;
    }

    /**
     * Atomically dumps content into a file.
     *
     * @param  string       $filename The file to be written to.
     * @param  string       $content  The data to write into the file.
     * @param  null|int     $mode     The file mode (octal). If null, file permissions are not modified
     *                                Deprecated since version 2.3.12, to be removed in 3.0.
     * @throws IOException            If the file cannot be written to.
     */
    public function dumpFile($filename, $content, $mode = 0666)
    {
        $dir = dirname($filename);

        if (!is_dir($dir)) {
            $this->mkdir($dir);
        } elseif (!is_writable($dir)) {
            throw new IOException(sprintf('Unable to write to the "%s" directory.', $dir), 0, null, $dir);
        }

        $tmpFile = tempnam($dir, basename($filename));

        if (false === @file_put_contents($tmpFile, $content)) {
            throw new IOException(sprintf('Failed to write file "%s".', $filename), 0, null, $filename);
        }

        $this->rename($tmpFile, $filename, true);
        if (null !== $mode) {
            $this->chmod($filename, $mode);
        }
    }
} 