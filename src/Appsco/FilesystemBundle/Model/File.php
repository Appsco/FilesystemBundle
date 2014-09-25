<?php

namespace Appsco\FilesystemBundle\Model;

class File
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $mime;

    /**
     * @var int
     */
    public $size;

    /**
     * @return bool
     */
    public function isDirectory()
    {
        return (bool) preg_match('/directory$/', $this->mime);
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return (bool) preg_match('/^image\//', $this->mime);
    }
} 