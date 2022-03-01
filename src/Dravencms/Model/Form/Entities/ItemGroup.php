<?php declare(strict_types = 1);
namespace Dravencms\Model\Form\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\ILocale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class ItemGroup
 * @package App\Model\Form\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="formItemGroup")
 */
class ItemGroup
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false,unique=true)
     */
    private $identifier;

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
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $items;

    /**
     * @var ArrayCollection|ItemGroupTranslation[]
     * @ORM\OneToMany(targetEntity="ItemGroupTranslation", mappedBy="itemGroup",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * ItemGroup constructor.
     * @param Form $form
     * @param string $identifier
     * @param bool $isShowName
     */
    public function __construct(Form $form, string $identifier, bool $isShowName = false)
    {
        $this->identifier = $identifier;
        $this->isShowName = $isShowName;
        $this->form = $form;

        $this->translations = new ArrayCollection();
    }

    /**
     * @param boolean $isShowName
     */
    public function setIsShowName(bool $isShowName): void
    {
        $this->isShowName = $isShowName;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }


    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @param Form $form
     */
    public function setForm(Form $form): void
    {
        $this->form = $form;
    }

    /**
     * @return boolean
     */
    public function isShowName(): bool
    {
        return $this->isShowName;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
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

    /**
     * @param ILocale $locale
     * @return GalleryTranslation
     */
    public function translate(ILocale $locale)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("locale", $locale));
        return $this->getTranslations()->matching($criteria)->first();
    }
}

