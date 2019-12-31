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
 * Date: 18/11/2019
 * Time: 11:57
 */

namespace Kookaburra\Library\Provider;


use Kookaburra\UserAdmin\Entity\Person;
use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use Kookaburra\Library\Entity\Library;
use Symfony\Component\Form\FormInterface;

class LibraryProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = Library::class;

    /**
     * findPeopleFormIdentifierReport
     * @param FormInterface $form
     * @return array
     */
    public function findPeopleFormIdentifierReport(FormInterface $form): array
    {
        $borrowerType = $form->get("borrowerType")->getData();
        $rollGroup = $form->get("rollGroup")->getData();

        switch ($borrowerType) {
            case 'Student':
                if ($rollGroup === null)
                    return [];
                return $this->getRepository(Person::class)->findStudentsByRollGroup($rollGroup, 'surname');
            case 'Staff':
                return $this->getRepository(Person::class)->findCurrentStaff();
            case 'Parent':
                return $this->getRepository(Person::class)->findCurrentParents();
            case 'Other':
                return $this->getRepository(Person::class)->findOthers();
        }
        return [];
    }
}