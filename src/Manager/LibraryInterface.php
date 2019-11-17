<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 28/10/2019
 * Time: 11:31
 */

namespace Kookaburra\Library\Manager;
use Kookaburra\Library\Entity\LibraryItem;

/**
 * Interface LibraryInterface
 * @package Kookaburra\Library\Manager
 */
interface LibraryInterface
{
    /**
     * getLibraryManager
     * @return LibraryManager
     */
    public function getLibraryManager(): LibraryManager;

    /**
     * invoke
     * @param LibraryItem $item
     */
    public function invoke(LibraryItem $item): void;
}