<?php
namespace App\Model\Form\Entities;

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
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $sendButtonValue;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $successMessage;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="text",nullable=false)
     */
    private $latteTemplate;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    private $locale;

    /**
     * @var ArrayCollection|ItemGroup[]
     * @ORM\OneToMany(targetEntity="ItemGroup", mappedBy="form",cascade={"persist"})
     */
    private $itemGroups;

    /**
     * Form constructor.
     * @param string $name
     * @param string $email
     * @param string $sendButtonValue
     * @param string $successMessage
     * @param string $latteTemplate
     * @param bool $isActive
     */
    public function __construct($name, $email, $sendButtonValue, $successMessage, $latteTemplate = null, $isActive = true)
    {
        $this->name = $name;
        $this->email = $email;
        $this->sendButtonValue = $sendButtonValue;
        $this->successMessage = $successMessage;
        $this->latteTemplate = $latteTemplate;
        $this->isActive = $isActive;

        $this->itemGroups = new ArrayCollection();
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
     * @param string $sendButtonValue
     */
    public function setSendButtonValue($sendButtonValue)
    {
        $this->sendButtonValue = $sendButtonValue;
    }

    /**
     * @param string $successMessage
     */
    public function setSuccessMessage($successMessage)
    {
        $this->successMessage = $successMessage;
    }

    /**
     * @param string $latteTemplate
     */
    public function setLatteTemplate($latteTemplate)
    {
        $this->latteTemplate = $latteTemplate;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
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
     * @return string
     */
    public function getSendButtonValue()
    {
        return $this->sendButtonValue;
    }

    /**
     * @return string
     */
    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    /**
     * @return string
     */
    public function getLatteTemplate()
    {
        return $this->latteTemplate;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return ItemGroup[]|ArrayCollection
     */
    public function getItemGroups()
    {
        return $this->itemGroups;
    }

}

