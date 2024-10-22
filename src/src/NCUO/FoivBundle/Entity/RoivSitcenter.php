<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoivSitcenter
 *
 * @ORM\Table(name="eif_data2.dict_roiv_sitcenter", indexes={@ORM\Index(name="IDX_347D8C455C4F55AD", columns={"roiv_id"}), @ORM\Index(name="IDX_347D8C451E90D3F0", columns={"director"})})
 * @ORM\Entity
 */
class RoivSitcenter
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_roiv_sitcenter_id_seq", allocationSize=1, initialValue=1)
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
     * @var NCUO\FoivBundle\Entity\Roiv
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\Roiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="roiv_id", referencedColumnName="id")
     * })
     */
    private $roiv;

    /**
     * @var NCUO\FoivBundle\Entity\RoivPersons
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\RoivPersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="director", referencedColumnName="id")
     * })
     */
    private $director;



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
     * @return RoivSitcenter
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
     * @return RoivSitcenter
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
     * @return RoivSitcenter
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
     * @return RoivSitcenter
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
     * @return RoivSitcenter
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
     * @return RoivSitcenter
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
     * Set roiv
     *
     * @param \NCUO\FoivBundle\Entity\Roiv $roiv
     * @return RoivSitcenter
     */
    public function setRoiv(\NCUO\FoivBundle\Entity\Roiv $roiv = null)
    {
        $this->roiv = $roiv;

        return $this;
    }

    /**
     * Get roiv
     *
     * @return \NCUO\FoivBundle\Entity\Roiv 
     */
    public function getRoiv()
    {
        return $this->roiv;
    }

    /**
     * Set director
     *
     * @param \NCUO\FoivBundle\Entity\RoivPersons $director
     * @return RoivSitcenter
     */
    public function setDirector(\NCUO\FoivBundle\Entity\RoivPersons $director = null)
    {
        $this->director = $director;

        return $this;
    }

    /**
     * Get director
     *
     * @return \NCUO\FoivBundle\Entity\RoivPersons 
     */
    public function getDirector()
    {
        return $this->director;
    }
}
