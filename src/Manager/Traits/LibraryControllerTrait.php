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
        $flashBag = $session->getBag('flashes');
        $notSet = true;
        if ($flashBag->has('info')) {
            foreach($flashBag->peek('info') as $flash)
            {
                if (strpos($flash[0], 'The current library is the') === 0) {
                    $notSet = false;
                    break;
                }
            }
        }
        if ($library && $notSet)
            $flashBag->add('info', ["The current library is the '{name}'", ['{name}' => $library->getName()], 'Library']);
    }
}