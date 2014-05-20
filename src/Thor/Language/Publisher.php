<?php

namespace Thor\Language;

use Illuminate\Filesystem\Filesystem;

class Publisher
{

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The destination of the lang files.
     *
     * @var string
     */
    protected $publishPath;

    /**
     * The path to the application's packages.
     *
     * @var string
     */
    protected $packagePath;

    /**
     * Create a new lang publisher instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $publishPath
     * @return void
     */
    public function __construct(Filesystem $files, $publishPath)
    {
        $this->files = $files;
        $this->publishPath = $publishPath;
    }

    /**
     * Publish lang files from a given path.
     *
     * @param  string  $package
     * @param  string  $source
     * @param  string  $namespace
     * @return void
     */
    public function publish($package, $source, $namespace = null)
    {
        if(empty($namespace)) {
            $namespace = last(explode('/', $package));
        }

        $destination = $this->publishPath . "/packages/{$namespace}";

        $this->makeDestination($destination);

        return $this->files->copyDirectory($source, $destination);
    }

    /**
     * Publish the lang files for a package.
     *
     * @param  string  $package
     * @param  string  $packagePath
     * @param  string  $namespace
     * @return void
     */
    public function publishPackage($package, $packagePath = null, $namespace = null)
    {
        list($vendor, $name) = explode('/', $package);

        // First we will figure out the source of the package's lang location
        // which we do by convention. Once we have that we will move the files over
        // to the "main" lang directory for this particular application.
        $path = $packagePath ? : $this->packagePath;

        $source = $this->getSource($package, $name, $path);

        return $this->publish($package, $source, $namespace);
    }

    /**
     * Get the source lang directory to publish.
     *
     * @param  string  $package
     * @param  string  $name
     * @param  string  $packagePath
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function getSource($package, $name, $packagePath)
    {
        $source = $packagePath . "/{$package}/src/lang";

        if(!$this->files->isDirectory($source)) {
            $source = base_path() . '/vendor/' . $source;
            if(!$this->files->isDirectory($source)) {
                throw new \InvalidArgumentException("There are no lang files for this package.");
            }
        }

        return $source;
    }

    /**
     * Create the destination directory if it doesn't exist.
     *
     * @param  string  $destination
     * @return void
     */
    protected function makeDestination($destination)
    {
        if(!$this->files->isDirectory($destination)) {
            $this->files->makeDirectory($destination, 0777, true);
        }
    }

    /**
     * Set the default package path.
     *
     * @param  string  $packagePath
     * @return void
     */
    public function setPackagePath($packagePath)
    {
        $this->packagePath = $packagePath;
    }

}
