<?php
/**
 * Created by solly [04.06.17 6:28]
 */

namespace insolita\multifs\contracts;

use insolita\multifs\contracts\IFilesystemBuilder;
use League\Flysystem\FilesystemInterface;

interface IMultifsManager
{
    /**
     * @return array
     */
    public function listPrefixes();
    
    /**
     * @param $prefix
     *
     * @return bool
     */
    public function hasFilesystem($prefix);
    
    /**
     * Mount contracts.
     *
     * @param \League\Flysystem\FilesystemInterface[] $filesystems [:prefix => Filesystem,]
     * @return $this
     */
    public function mountFilesystems(array $filesystems);
    
    /**
     * Mount contracts.
     *
     * @param string              $prefix
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @return $this
     */
    public function mountFilesystem($prefix, FilesystemInterface $filesystem);
    
    /**
     * @param                                                  $prefix
     * @param \insolita\multifs\contracts\IFilesystemBuilder   $builder
     *
     * @return $this;
     */
    public function buildAndMountFilesystem($prefix, IFilesystemBuilder $builder);
    /**
     * Get the filesystem with the corresponding prefix.
     *
     * @param string $prefix
     *
     * @return FilesystemInterface
     */
    public function getFilesystem($prefix);
    
    /**
     * Retrieve the prefix from an arguments array.
     *
     * @param array $arguments
     *
     * @return array [:prefix, :arguments]
     */
    public function filterPrefix(array $arguments);
    
    /**
     * Invoke a plugin on a filesystem mounted on a given prefix.
     *
     * @param string $method
     * @param array  $arguments
     * @param string $prefix
     *
     * @return mixed
     */
    public function invokePluginOnFilesystem($method, $arguments, $prefix);
    
    /**
     * Move a file.
     *
     * @param string $from
     * @param string $to
     * @param array  $config
     *
     * @return bool
     */
    public function move($from, $to, array $config = []);
    
    /**
     * List with plugin adapter.
     *
     * @param array  $keys
     * @param string $directory
     * @param bool   $recursive
     *
     * @return array
     */
    public function listWith(array $keys = [], $directory = '', $recursive = false);
    
    /**
     * @param string $from
     * @param string $to
     * @param array  $config
     *
     *
     * @return bool
     */
    public function copy($from, $to, array $config = []);
    
    /**
     * Call forwarder.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments);
    
    /**
     * @param string $directory
     * @param bool   $recursive
     *
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false);
}
