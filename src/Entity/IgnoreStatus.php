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
 * Date: 13/11/2019
 * Time: 08:30
 */

namespace Kookaburra\Library\Entity;

use App\Manager\Traits\BooleanList;

/**
 * Class IgnoreStatus
 * @package Kookaburra\Library\Entity
 */
class IgnoreStatus
{
    use BooleanList;

    /**
     * @var string
     */
    private $status = 'N';

    /**
     * isStatus
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->getStatus() === 'Y';
    }

    /**
     * getStatus
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status = self::checkBoolean($this->status, 'N');
    }

    /**
     * Status.
     *
     * @param string $status
     * @return IgnoreStatus
     */
    public function setStatus(string $status): IgnoreStatus
    {
        $this->status = self::checkBoolean($status, 'N');
        return $this;
    }
}