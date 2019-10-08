<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 13:05
 */

namespace Kookaburra\Library\Manager;


use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryItem;

class LibraryManager
{
    /**
     * @var bool
     */
    private $generateIdentifier = true;

    /**
     * @var int
     */
    private $maximumCopies = 20;

    /**
     * @return bool
     */
    public function isGenerateIdentifier(): bool
    {
        return $this->generateIdentifier;
    }

    /**
     * GenerateIdentifier.
     *
     * @param bool $generateIdentifier
     * @return LibraryManager
     */
    public function setGenerateIdentifier(bool $generateIdentifier): LibraryManager
    {
        $this->generateIdentifier = $generateIdentifier;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaximumCopies(): int
    {
        return $this->maximumCopies;
    }

    /**
     * MaximumCopies.
     *
     * @param int $maximumCopies
     * @return LibraryManager
     */
    public function setMaximumCopies(int $maximumCopies): LibraryManager
    {
        $this->maximumCopies = $maximumCopies;
        return $this;
    }

    /**
     * newIdentifier
     * @param LibraryItem $item
     * @return LibraryItem
     */
    public function newIdentifier(LibraryItem $item): LibraryItem
    {
        $key = uniqid($item->getLibrary()->getAbbr().'-');
        $ok = false;
        do {
            $ok = null === ProviderFactory::getRepository(LibraryItem::class)->findOneBy(['identifier' => $key]);
            if (!$ok)
                $key = uniqid($item->getLibrary()->getAbbr().'-');
        } while (!$ok);

        return $item->setIdentifier($key);
    }
}