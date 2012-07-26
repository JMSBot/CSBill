<?php

namespace CS\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CS\ClientBundle\Entity\ContactType
 *
 * @ORM\Table(name="contact_types")
 * @ORM\Entity()
 */
class ContactType
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=45, unique=true, nullable=false)
     * @Assert\NotBlank()
     * @Assert\MaxLength(45)
     */
    private $name;
    
    /**
     * @var ArrayCollection $details
     * 
     * @ORM\OneToMany(targetEntity="ContactDetail", mappedBy="type", cascade="ALL")
     */
    private $details;
    
    /**
     * Constructer
     */
    public function __construct()
    {
		$this->detail = new ArrayCollection;
	}

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ContactType
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Add detail
     * 
     * @param ContactDetail $detail
     * @return ContactType
     */
    public function addDetail(ContactDetail $detail)
    {
		$this->details[] = $detail;
		$detail->setType($this);
		
		return $this;
	}
	
	/**
	 * Get details
	 * 
	 * @return ArrayCollection
	 */
	public function getDetails()
	{
		return $this->detail;
	}
}
