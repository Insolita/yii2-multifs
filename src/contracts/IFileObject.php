<?php
/**
 * Created by solly [04.06.17 3:18]
 */

namespace insolita\multifs\contracts;

/**
 * Interface IFileObject
 */
interface IFileObject
{
    
    /**
     * @return string
     */
    public function getPath();
    
    /**
     * @return string
     */
    public function getSize();
    
    /**
     * @return string
     */
    public function getMimeType();
    
    /**
     * @param bool|string $part
     *
     * @return mixed
     */
    public function getPathInfo($part = false);
    
    /**
     * @return mixed
     */
    public function getExtension();
    
    /**
     * @return mixed
     */
    public function getExtensionByMimeType();
    
    /**
     * @return string|null
     */
    public function getTargetFileName();
    
    /**
     * @var string
     **/
    public function setTargetFileName($fileName);
}
