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
 * Date: 19/11/2019
 * Time: 09:58
 */

namespace Kookaburra\Library\Entity;

use App\Entity\RollGroup;

/**
 * Class BorrowerIdentifierList
 * @package Kookaburra\Library\Entity
 */
class BorrowerIdentifierList
{
    /**
     * @var string|null
     */
    private $borrowerType;

    /**
     * @var RollGroup|null
     */
    private $rollGroup;

    /**
     * @var bool
     */
    private $withPhoto = true;

    /**
     * @return string|null
     */
    public function getBorrowerType(): ?string
    {
        return $this->borrowerType;
    }

    /**
     * BorrowerType.
     *
     * @param string|null $borrowerType
     * @return BorrowerIdentifierList
     */
    public function setBorrowerType(?string $borrowerType): BorrowerIdentifierList
    {
        $this->borrowerType = $borrowerType;
        return $this;
    }

    /**
     * @return RollGroup|null
     */
    public function getRollGroup(): ?RollGroup
    {
        return $this->rollGroup;
    }

    /**
     * RollGroup.
     *
     * @param RollGroup|null $rollGroup
     * @return BorrowerIdentifierList
     */
    public function setRollGroup(?RollGroup $rollGroup): BorrowerIdentifierList
    {
        $this->rollGroup = $rollGroup;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithPhoto(): bool
    {
        return $this->withPhoto;
    }

    /**
     * WithPhoto.
     *
     * @param bool $withPhoto
     * @return BorrowerIdentifierList
     */
    public function setWithPhoto($withPhoto): BorrowerIdentifierList
    {
        $this->withPhoto = ($withPhoto === 'Y');
        return $this;
    }
}