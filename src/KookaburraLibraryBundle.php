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
 * Date: 5/10/2019
 * Time: 18:09
 */

namespace Kookaburra\Library;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class KookaburraLibraryBundle
 * @package Kookaburra\Library
 */
class KookaburraLibraryBundle extends Bundle
{
    /**
     * build
     * @param ContainerBuilder $container
     */
     public function build(ContainerBuilder $container)
     {
         parent::build($container);
     }
}