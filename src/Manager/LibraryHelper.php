<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @param SessionInterface $session
     */
    public static function setSession(SessionInterface $session): void
    {
        self::$session = $session;
    }

    /**
     * @var null|Library
     */
    private static $currentLibrary;

    /**
     * getCurrentLibrary
     * @return Library|null
     */
    public static function getCurrentLibrary(): ?Library
    {
        if (null !== self::$currentLibrary)
            return self::$currentLibrary;
        if (self::getSession()->has('current_library')) {
            return self::$currentLibrary = ProviderFactory::getRepository(Library::class)->find(self::getSession()->get('current_library')->getId());
        }
        $result = ProviderFactory::getRepository(Library::class)->findBy([], ['id' => 'ASC']);
        if (count($result) > 0)
            self::setCurrentLibrary($result[0]);
        return self::$currentLibrary = $result ? $result[0] : null;
    }

    /**
     * setCurrentLibrary
     * @param null|Library $library
     */
    public static function setCurrentLibrary(?Library $library)
    {
        self::$currentLibrary = $library;
        self::getSession()->set('current_library', $library);
    }
}