<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 25.2.17
 * Time: 23:00
 */

namespace Dravencms\Model\Form\Entities;

use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Nette;

/**
 * Class Form
 * @package App\Model\Form\Entities
 * @ORM\Entity
 * @ORM\Table(name="formFormTranslantion", uniqueConstraints={@UniqueConstraint(name="form_idlocale_id", columns={"form_id", "locale_id"})})
 */
class FormTranslation
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $sendButtonValue;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $successMessage;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=false)
     */
    private $latteTemplate;

    /**
     * @var Form
     * @ORM\ManyToOne(targetEntity="Form", inversedBy="translations")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     */
    private $form;

    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * FormTranslation constructor.
     * @param Form $form
     * @param Locale $locale
     * @param $sendButtonValue
     * @param $successMessage
     * @param null $latteTemplate
     */
    public function __construct(
            Form $form, 
            Locale $locale, 
            string $sendButtonValue, 
            string $successMessage,
            string $latteTemplate = null
            )
    {
        $this->form = $form;
        $this->locale = $locale;
        $this->sendButtonValue = $sendButtonValue;
        $this->successMessage = $successMessage;
        $this->latteTemplate = $latteTemplate;
    }

    /**
     * @param string $sendButtonValue
     */
    public function setSendButtonValue(string $sendButtonValue): void
    {
        $this->sendButtonValue = $sendButtonValue;
    }

    /**
     * @param string $successMessage
     */
    public function setSuccessMessage(string $successMessage): void
    {
        $this->successMessage = $successMessage;
    }

    /**
     * @param null|string $latteTemplate
     */
    public function setLatteTemplate(string $latteTemplate = null): void
    {
        $this->latteTemplate = $latteTemplate;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getSendButtonValue(): string
    {
        return $this->sendButtonValue;
    }

    /**
     * @return string
     */
    public function getSuccessMessage(): string
    {
        return $this->successMessage;
    }

    /**
     * @return null|string
     */
    public function getLatteTemplate(): ?string
    {
        return $this->latteTemplate;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }
}