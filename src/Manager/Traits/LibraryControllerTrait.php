<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 6/11/2019
 * Time: 08:28
 */

namespace Kookaburra\Library\Manager\Traits;

use Kookaburra\Library\Manager\LibraryHelper;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Trait LibraryControllerTrait
 * @package Kookaburra\Library\Manager\Traits
 */
trait LibraryControllerTrait
{
    /**
     * LibraryControllerTrait constructor.
     */
    public function __construct(LibraryHelper $helper, SessionInterface $session)
    {
        $library = LibraryHelper::getCurrentLibrary();
        if ($library)
            $session->getBag('flashes')->add('info', ["The current library is the '{name}'", ['{name}' => $library->getName()], 'Library']);
    }
}