<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FoivItsConvention
 *
 * @ORM\Table(name="eif_data2.dict_status_3_opt")
 * @ORM\Entity
 */
class Status3Opt
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="protocol_its", type="string", length=64)
     */
    private $protocolIts;

    /**
     * @var string
     *
     * @ORM\Column(name="integration_spo", type="string", length=64)
     */
    private $integrationSpo;
    
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
     * @return FoivItsConvention
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
     * Set ProtocolIts
     *
     * @param string $id
     * @return ProtocolIts
     */
    public function setProtocolIts($id)
    {
        $this->protocolIts = $protocolIts;

        return $this;
    }

    /**
     * Get ProtocolIts
     *
     * @return string 
     */
    public function getProtocolIts()
    {
        return $this->protocolIts;
    }
    
    /**
     * Set IntegrationSpo
     *
     * @param string $id
     * @return IntegrationSpo
     */
    public function setIntegrationSpo($id)
    {
        $this->integrationSpo = $integrationSpo;

        return $this;
    }

    /**
     * Get IntegrationSpo
     *
     * @return string 
     */
    public function getIntegrationSpo()
    {
        return $this->integrationSpo;
    }
}
