<?php declare(strict_types = 1);
namespace Dravencms\Model\Form\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class ItemOption
 * @package App\Model\Form\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="formItemOption")
 */
class ItemOption
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var integer
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false,unique=true)
     */
    private $identifier;

    /**
     * @var Item
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="itemOptions")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;

    /**
     * @var ArrayCollection|ItemOptionTranslation[]
     * @ORM\OneToMany(targetEntity="ItemOptionTranslation", mappedBy="itemOption",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * ItemOption constructor.
     * @param Item $item
     * @param $identifier
     */
    public function __construct(Item $item, string $identifier)
    {
        $this->item = $item;
        $this->identifier = $identifier;
        $this->translations = new ArrayCollection();
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
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }

    /**
     * @return ArrayCollection|ItemOptionTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}

