<?php declare(strict_types = 1);
namespace Dravencms\Model\Form\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class SaveValue
 * @package App\Model\Form\Entities
 * @ORM\Entity
 * @ORM\Table(name="formSaveValue")
 */
class SaveValue
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private $value;

    /**
     * @var Item
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="saveValues")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;

    /**
     * @var Save
     * @ORM\ManyToOne(targetEntity="Save", inversedBy="saveValues")
     * @ORM\JoinColumn(name="save_id", referencedColumnName="id")
     */
    private $save;

    /**
     * SaveValue constructor.
     * @param string $value
     * @param Item $item
     * @param Save $save
     */
    public function __construct(string $value, Item $item, Save $save)
    {
        $this->value = $value;
        $this->item = $item;
        $this->save = $save;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}

