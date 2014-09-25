<?php
namespace Appsco\FilesystemBundle\Service;

use Appsco\FilesystemBundle\Adapter\Adapter;

class FilesystemProvider
{

    /**
     * @var Adapter[]
     */
    private $drivers = array();

    /**
     * @param string $volume
     * @param string $driver
     * @return Adapter
     * @throws \RuntimeException
     */
    public function getVolume($volume, $driver)
    {
        $adapter = isset($this->drivers[$driver]) ? $this->drivers[$driver] : null;
        if ($adapter) {
            return $adapter->getVolume($volume);
        }
        throw new \RuntimeException("Specified adapter isn't registered");
    }

    /**
     * @param Adapter $adapter
     * @param string $name
     */
    public function registerDrive(Adapter $adapter, $name)
    {
        if (!isset($this->drivers[$name])) {
            $this->drivers[$name] = $adapter;
        }
    }
} 