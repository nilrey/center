<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoivPvo
 *
 * @ORM\Table(name="eif_data2.dict_roiv_pvo", indexes={@ORM\Index(name="IDX_69147F3B1E90D3F0", columns={"director"}), @ORM\Index(name="IDX_69147F3B229EE1CF", columns={"fk_roiv"})})
 * @ORM\Entity
 */
class RoivPvo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_roiv_pvo_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="short_name", type="string", length=255, nullable=true)
     */
    private $shortName;

    /**
     * @var string
     *
     * @ORM\Column(name="website_url", type="text", nullable=true)
     */
    private $websiteUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="functions", type="text", nullable=true)
     */
    private $functions;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="text", nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var NCUO\FoivBundle\Entity\RoivPvoPersons
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\RoivPvoPersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="director", referencedColumnName="id")
     * })
     */
    private $director;

    /**
     * @var NCUO\FoivBundle\Entity\Roiv
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\Roiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_roiv", referencedColumnName="id")
     * })
     */
    private $fkRoiv;



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
     * @return RoivPvo
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
     * Set shortName
     *
     * @param string $shortName
     * @return RoivPvo
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Get shortName
     *
     * @return string 
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Set websiteUrl
     *
     * @param string $websiteUrl
     * @return RoivPvo
     */
    public function setWebsiteUrl($websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    /**
     * Get websiteUrl
     *
     * @return string 
     */
    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return RoivPvo
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
     * Set functions
     *
     * @param string $functions
     * @return RoivPvo
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
     * Set phone
     *
     * @param string $phone
     * @return RoivPvo
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
     * @return RoivPvo
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
     * Set director
     *
     * @param \NCUO\FoivBundle\Entity\RoivPvoPersons $director
     * @return RoivPvo
     */
    public function setDirector(\NCUO\FoivBundle\Entity\RoivPvoPersons $director = null)
    {
        $this->director = $director;

        return $this;
    }

    /**
     * Get director
     *
     * @return \NCUO\FoivBundle\Entity\RoivPvoPersons 
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * Set fkRoiv
     *
     * @param \NCUO\FoivBundle\Entity\Roiv $fkRoiv
     * @return RoivPvo
     */
    public function setFkRoiv(\NCUO\FoivBundle\Entity\Roiv $fkRoiv = null)
    {
        $this->fkRoiv = $fkRoiv;

        return $this;
    }

    /**
     * Get fkRoiv
     *
     * @return \NCUO\FoivBundle\Entity\Roiv 
     */
    public function getFkRoiv()
    {
        return $this->fkRoiv;
    }
}
