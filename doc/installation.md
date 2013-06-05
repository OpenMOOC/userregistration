Userregistration module
=======================

Requirements
------------

* PHP 5.2.0 or newer
* simpleSAMLphp 1.9.0 or newer
* One of the following:
    * redis and php-redis extension
    * MongoDB and php MongoDB PECL extension

Downloading userregistration
----------------------------

You can download the [latest tar.gz/zip from GitHub](https://github.com/OpenMOOC/userregistration/archive/master.zip).

You can also clone the git repository using the following command:

    git clone https://github.com/OpenMOOC/userregistration.git
    
Once you have downloaded a copy of the module, place the `userregistration/` directory in the simpleSAMLphp `modules/` subdirectory. If you downloaded the tar.gz/zip file, rename the resulting uncompressed directory to `userregistration` instead the default `userregistration-master` name.

Enabling userregistration
-------------------------

Create an empty `enable` file inside the `userregistration/` directory. An easy way to do that follows:

    touch userregistration/enable

The module will be enabled. You can now proceed to [configuration.md](configure it).
