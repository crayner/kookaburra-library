<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/11/2019
 * Time: 05:40
 */

namespace Kookaburra\Library\Manager;

use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\Library;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class LibraryHelper
 * @package Kookaburra\Library\Manager
 */
class LibraryHelper
{
    /**
     * @var SessionInterface
     */
    private static $session;

    /**
     * LibraryHelper constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        self::$session = $session;
    }

    /**
     * @return SessionInterface
     */
    public static function getSession(): SessionInterface
    {
        return self::$session;
    }

    /**
     * getCurrentLibrary
     * @return Library|null
     */
    public static function getCurrentLibrary(): ?Library
    {
        if (self::getSession()->has('current_library'))
            return self::getSession()->get('current_library');
        $result = ProviderFactory::getRepository(Library::class)->findBy([], ['id' => 'ASC']);
        if (count($result) > 0)
            self::setCurrentLibrary($result[0]);
        return $result[0];
    }

    /**
     * getCurrentLibrary
     * @return void
     */
    public static function setCurrentLibrary(Library $library)
    {
        self::getSession()->set('current_library', $library);
    }
}