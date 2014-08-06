FilesystemBundle
================

It is used to browse, create, read and delete files and folders on different filesystems.
Rackspace OpenCloud and Local file systems are supported.

Prerequisites
-------------

This version of bundle requires Symfony ~2.2 and Rackspace OpenCloud v1.10.0

Installation
------------


Step 1: Download appsco/filesystembundle with composer
--------------------------------------------------

Add appsco/filesystembundle to your ```composer.json``` requirements:

``` json
{
    "require": {
        "appsco/filesystembundle": "dev-master"
    }
}
```

Check bundle [releases](https://github.com/Appsco/FilesystemBundle/releases) for the latest stable release.


Step 2: Load the bundle to kernel
---------------------------------

Add Add AppscoFilesystemBundle to the kernel of your project:

``` php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Appsco\FilesystemBundle\AppscoFilesystemBundle(),
    );
}
```

Now you are ready to go. With this setup you can use Local filesystem.

Further Setup - Rackspace OpenCloud
-----------------------------------

Firs you need to acquire username and apiKey to use Cloud Files.
When you acquire data you can continue on further setup.
By default configuration is set to LON region. If you are using the same then bare minimum for configuration is
``` yml
appsco_filesystem:
    rackspace:
        client:
            username: %username%
            apikey: %apikey%

```

Here is the full configuration (config.json) for rackspace open cloud.

``` yml
appsco_filesystem:
    rackspace:
        client:
            username: %username%
            apikey: %apikey%
            url: %url%
        objectstore:
            type: ~
            name: ~
            region: LON
            urlType: ~

```

Using The bundle
----------------

To use the bundle call the service `appsco_ket_filesystem.filesystem` then retrieve the Volume you would like to use
along with the adapter that is used to manipulate the filesystem.

``` php
  // tmp folder must be create so that file system can mount it for further use
  $localFs = $this->get('appsco_filesystem.filesystem')->getVolume('/tmp', 'local');

  // Fere volume represents the container that you would like to use
  $rackspaceFs = $this->get('appsco_filesystem.filesystem')->getVolume('container', 'rackspace');

```
