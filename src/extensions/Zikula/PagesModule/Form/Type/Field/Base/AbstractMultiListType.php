<?php

/**
 * Pages.
 *
 * @copyright Zikula Team (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula Team <info@ziku.la>.
 * @see https://ziku.la
 * @version Generated by ModuleStudio 1.4.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\PagesModule\Form\Type\Field\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\PagesModule\Form\DataTransformer\ListFieldTransformer;
use Zikula\PagesModule\Helper\ListEntriesHelper;

/**
 * Multi list field type base class.
 */
abstract class AbstractMultiListType extends AbstractType
{
    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;

    public function __construct(ListEntriesHelper $listHelper)
    {
        $this->listHelper = $listHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ListFieldTransformer($this->listHelper);
        $builder->addModelTransformer($transformer);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'zikulapagesmodule_field_multilist';
    }
}