<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FoivSitcenter
 *
 * @ORM\Table(name="eif_data2.dict_foiv_sitcenter", indexes={@ORM\Index(name="IDX_4B633F321E90D3F0", columns={"director"}), @ORM\Index(name="IDX_4B633F32CFDEED70", columns={"foiv_id"})})
 * @ORM\Entity
 */
class FoivSitcenter
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.seq_sitcenter", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="functions", type="text", nullable=true)
     */
    private $functions;

    /**
     * @var string
     *
     * @ORM\Column(name="web_site", type="string", length=255, nullable=true)
     */
    private $webSite;

    /**
     * @var App\NCUO\FoivBundle\Entity\SitcenterPerson
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\SitcenterPerson")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="director", referencedColumnName="id")
     * })
     */
    private $director;

    /**
     * @var App\NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Foiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foiv_id", referencedColumnName="id")
     * })
     */
    private $foiv;

    /**
     * @var DateTime 
     *
     * @ORM\Column(name="created", type="string", nullable=false)
     */       
    
    protected $create_date;
    /**
     * @var DateTime 
     *
     * @ORM\Column(name="modified", type="string", nullable=false)
     */       
    protected $modified_date;
    


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
     * @return FoivSitcenter
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
     * Set address
     *
     * @param string $address
     * @return FoivSitcenter
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return FoivSitcenter
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FoivSitcenter
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set functions
     *
     * @param string $functions
     * @return FoivSitcenter
     */
    public function setFunctions($functions)
    {
        $this->functions = $functions;

        return $this;
    }

    /**
     * Get functions
     *
     * @return string 
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * Set webSite
     *
     * @param string $webSite
     * @return FoivSitcenter
     */
    public function setWebSite($webSite)
    {
        $this->webSite = $webSite;

        return $this;
    }

    /**
     * Get webSite
     *
     * @return string 
     */
    public function getWebSite()
    {
        return $this->webSite;
    }

    /**
     * Set director
     *
     * @param \NCUO\FoivBundle\Entity\SitcenterPerson $director
     * @return FoivSitcenter
     */
    public function setDirector(App\NCUO\FoivBundle\Entity\SitcenterPerson $director = null)
    {
        $this->director = $director;

        return $this;
    }

    /**
     * Get director
     *
     * @return \NCUO\FoivBundle\Entity\SitcenterPerson 
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * Set foiv
     *
     * @param \NCUO\FoivBundle\Entity\Foiv $foiv
     * @return FoivSitcenter
     */
    public function setFoiv(App\NCUO\FoivBundle\Entity\Foiv $foiv = null)
    {
        $this->foiv = $foiv;

        return $this;
    }

    /**
     * Get foiv
     *
     * @return \NCUO\FoivBundle\Entity\Foiv 
     */
    public function getFoiv()
    {
        return $this->foiv;
    }
    
    /**
     * Get create date
     *
     * @return String 
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }
    
    /**
     * Get modified date
     *
     * @return String 
     */
    public function getModifiedDate()
    {
        return $this->modified_date;
    }
    
}
