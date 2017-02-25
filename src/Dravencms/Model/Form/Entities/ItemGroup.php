<?php
namespace Dravencms\Model\Form\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class ItemGroup
 * @package App\Model\Form\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="formItemGroup")
 */
class ItemGroup extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $isShowName;

    /**
     * @var integer
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var Form
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Form", inversedBy="itemGroups")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     */
    private $form;

    /**
     * @var ArrayCollection|Item[]
     * @ORM\OneToMany(targetEntity="Item", mappedBy="itemGroup",cascade={"persist"})
     */
    private $items;

    /**
     * @var ArrayCollection|ItemGroupTranslation[]
     * @ORM\OneToMany(targetEntity="ItemGroupTranslation", mappedBy="itemGroup",cascade={"persist"})
     */
    private $translations;

    /**
     * ItemGroup constructor.
     * @param Form $form
     * @param bool $isShowName
     */
    public function __construct(Form $form, $isShowName = false)
    {
        $this->isShowName = $isShowName;
        $this->form = $form;

        $this->translations = new ArrayCollection();
    }

    /**
     * @param boolean $isShowName
     */
    public function setIsShowName($isShowName)
    {
        $this->isShowName = $isShowName;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param Form $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * @return boolean
     */
    public function isShowName()
    {
        return $this->isShowName;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return Item[]|ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return ArrayCollection|ItemGroupTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}

