<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 16:11
 */

namespace Kookaburra\Library\Validator;

use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UniqueIdentifierValidator
 * @package Kookaburra\Library\Validator
 */
class UniqueIdentifierValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        dump($value, ProviderFactory::getRepository(LibraryItem::class)->findOneBy(['identifier' => $value]));
        if (null !== ProviderFactory::getRepository(LibraryItem::class)->findOneBy(['identifier' => $value]))
            $this->context->buildViolation('The value is not unique')
                ->setTranslationDomain('messages')
                ->addViolation();
    }

}