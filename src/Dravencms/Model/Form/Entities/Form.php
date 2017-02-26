<?php
namespace Dravencms\Model\Form\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class Form
 * @package App\Model\Form\Entities
 * @ORM\Entity
 * @ORM\Table(name="formForm")
 */
class Form extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false,unique=true)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $email;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var ArrayCollection|ItemGroup[]
     * @ORM\OneToMany(targetEntity="ItemGroup", mappedBy="form",cascade={"persist"})
     */
    private $itemGroups;

    /**
     * @var ArrayCollection|FormTranslation[]
     * @ORM\OneToMany(targetEntity="FormTranslation", mappedBy="form",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * Form constructor.
     * @param string $name
     * @param string $email
     * @param bool $isActive
     */
    public function __construct($name, $email, $isActive = true)
    {
        $this->name = $name;
        $this->email = $email;
        $this->isActive = $isActive;

        $this->itemGroups = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }


    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return ItemGroup[]|ArrayCollection
     */
    public function getItemGroups()
    {
        return $this->itemGroups;
    }

    /**
     * @return ArrayCollection|FormTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}

