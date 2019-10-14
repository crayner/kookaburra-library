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
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryType;

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

    /**
     * setTranslations
     */
    public function setTranslations()
    {
        TranslationsHelper::addTranslation('Please enter an ISBN13 or ISBN10 value before trying to get data from Google Books.');
        TranslationsHelper::addTranslation('The specified record cannot be found.');
    }

    /**
     * handleItem
     * @param LibraryItem $item
     * @param array $content
     * @return LibraryItem
     */
    public function handleItem(LibraryItem $item, array $content): LibraryItem
    {
        $fields = [];
        $library = ProviderFactory::getRepository(Library::class)->find($content['library']);
        $libraryType = ProviderFactory::getRepository(LibraryType::class)->find($content['libraryType']);
        $item->setLibrary($library)->setLibraryType($libraryType);

        foreach($libraryType->getFields() as $q=>$field)
            $fields[$field['name']] = isset($content['field'.$q]) ? $content['field'.$q] : '';

        $item->setFields($fields);

        $em = ProviderFactory::getEntityManager();
        $em->refresh($library);
        $em->refresh($libraryType);
        $em->persist($item);
        $em->flush();
        return $item;
    }
}