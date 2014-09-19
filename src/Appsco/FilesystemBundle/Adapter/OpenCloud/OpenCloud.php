<?php
namespace Appsco\FilesystemBundle\Adapter\OpenCloud;

use Appsco\FilesystemBundle\Adapter\Adapter;
use Appsco\FilesystemBundle\Model\File;
use Guzzle\Http\Exception\ClientErrorResponseException;
use OpenCloud\Common\Constants\Header;
use OpenCloud\Common\Exceptions\CreateUpdateError;
use OpenCloud\Common\Exceptions\DeleteError;
use OpenCloud\ObjectStore\Exception\ObjectNotFoundException;
use OpenCloud\ObjectStore\Resource\Container;
use OpenCloud\ObjectStore\Resource\DataObject;
use OpenCloud\ObjectStore\Service;

/**
 * OpenCloud adapter
 *
 */
class OpenCloud extends Adapter
{
    const CONTENT_TYPE_HEADER_DIRECTORY = 'application/directory';

    /**
     * @var Service
     */
    protected $objectStore;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var bool
     */
    private $createIfNotExist;

    /**
     * Constructor
     *
     * @param Service $objectStore
     * @param string $containerName The name of the container
     * @param bool $createContainer Whether to create the container if it does not exist
     */
    public function __construct(Service $objectStore, $createIfNotExist = false)
    {
        $this->objectStore = $objectStore;
        $this->createIfNotExist = $createIfNotExist;
    }

    /**
     * Returns an initialized container
     *
     * @param $containerName
     * @param bool $createContainer
     * @return Container
     * @throws \RuntimeException
     */
    protected function getContainer($containerName, $createContainer = false)
    {
        if ($this->container) {
            return $this->container;
        }

        try {
            $container = $this->objectStore->getContainer($containerName);
        } catch (ClientErrorResponseException $e) {
            if (!$createContainer) {
                throw new \RuntimeException(sprintf('Container "%s" does not exist.', $containerName));
            } else {
                if (!$container = $this->objectStore->createContainer($containerName)) {
                    throw new \RuntimeException(sprintf('Container "%s" could not be created.', $containerName));
                }
            }
        }

        return $this->container = $container;
    }

    /**
     * @param $volume
     * @throws \RuntimeException
     * @return $this
     */
    public function getVolume($volume)
    {
        $this->getContainer($volume, $this->createIfNotExist);
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
        if ($object = $this->tryGetObject($key)) {
            return $this->generateFileFromDataObject($object);
        }

        return false;
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
        try {
            $object = $this->container->uploadObject($key, $content);
        } catch (CreateUpdateError $updateError) {
            return false;
        }

        return $object->getContentLength();
    }

    public function mkdir($name, $recursive = true)
    {
        $dirs = [];
        if ($recursive) {
            $dirs = explode('/', $name);
        }
        $name = null;
        foreach ($dirs as $dir) {
            $name = $name ? $name . '/' . $dir : $dir;
            if (!$this->tryGetObject($name)) {
                $this->container->uploadObject(
                    $name,
                    '',
                    [
                        Header::CONTENT_TYPE => self::CONTENT_TYPE_HEADER_DIRECTORY
                    ]
                );
            }
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
        return $this->tryGetObject($key) !== false;
    }

    /**
     * @param null|string $path
     * @param bool $recursive
     *
     * @return File[] Content fields will be null
     */
    public function keys($path = null, $recursive = false)
    {

        $options = [];
        if(!$recursive) {
            $options['path'] = $path;
        } else {
            $options['prefix'] = $path;
        }

        $objectList = $this->container->objectList($options);
        $keys = array();

        while ($object = $objectList->next()) {
            $keys[$object->getName()] = $this->generateFileFromDataObject($object, false);
        }

        //sort($keys);

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
        if ($object = $this->tryGetObject($key)) {
            return $object->getLastModified();
        }

        return false;
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
        if (!$object = $this->tryGetObject($key)) {
            return false;
        }
        try {
            if($this->isDirectory($key)){
                $objects = $this->keys($key, true);
                foreach($objects as $path){
                    $object = $this->tryGetObject($path);
                    $object->delete();
                }
            } else {
                $object->delete();
            }
        } catch (DeleteError $deleteError) {
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
        if (false !== $this->write($targetKey, $this->read($sourceKey))) {
            $this->delete($sourceKey);

            return true;
        }

        return false;
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
        return self::CONTENT_TYPE_HEADER_DIRECTORY == $this->container->getObject($key)->getContentType();
    }

    /**
     * Returns the checksum of the specified key
     *
     * @param string $key
     *
     * @return string
     */
    public function checksum($key)
    {
        if ($object = $this->tryGetObject($key)) {
            return $object->getETag();
        }

        return false;
    }

    /**
     * @param string $key
     *
     * @return \OpenCloud\ObjectStore\Resource\DataObject|false
     */
    protected function tryGetObject($key)
    {
        try {
            return $this->container->getObject($key);
        } catch (ObjectNotFoundException $objFetchError) {
            return false;
        }
    }

    /**
     * @param DataObject $object
     * @param bool $includeContent
     *
     * @return File
     */
    private function generateFileFromDataObject(DataObject $object, $includeContent = true)
    {
        $file = new File();
        if ($includeContent) {
            $file->content = $object->getContent();
        }
        $file->mime = $object->getContentType();
        $file->name = $object->getName();
        $file->size = $object->getContentLength();

        return $file;
    }
}
