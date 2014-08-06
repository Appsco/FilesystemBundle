<?php
namespace Appsco\FilesystemBundle\Adapter;

abstract class Adapter implements FilesystemInterface
{
    abstract public function getVolume($volume);

} 