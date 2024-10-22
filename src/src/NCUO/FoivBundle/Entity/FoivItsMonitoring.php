<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FoivItsMonitoring
 *
 * @ORM\Table(name="eif_data2.dict_foiv_its_monitoring")
 * @ORM\Entity(repositoryClass="NCUO\FoivBundle\Entity\FoivItsMonitoringRepository")
 */
class FoivItsMonitoring
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
     * @var NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\Foiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foiv_id", referencedColumnName="id")
     * })
     */
    private $foivId;

    /**
     * @var NCUO\FoivBundle\Entity\FoivItsConvention
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\FoivItsConvention")
     * @ORM\JoinColumn(name="convention", referencedColumnName="id")
     */
    private $convention;

    /**
     * @var integer
     *
     * @ORM\Column(name="protocol_its", type="integer")
     */
    private $protocolIts;

    /**
     * @var integer
     *
     * @ORM\Column(name="cabinet", type="integer")
     */
    private $cabinet;

    /**
     * @var integer
     *
     * @ORM\Column(name="email", type="integer")
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="data_service", type="integer")
     */
    private $dataService;

    /**
     * @var integer
     *
     * @ORM\Column(name="vipnet", type="integer")
     */
    private $vipnet;

    /**
     * @var integer
     *
     * @ORM\Column(name="gis", type="integer")
     */
    private $gis;

    /**
     * @var integer
     *
     * @ORM\Column(name="resources_engaged", type="integer")
     */
    private $resourcesEngaged;

    /**
     * @var integer
     *
     * @ORM\Column(name="video", type="integer")
     */
    private $video;

    /**
     * @var integer
     *
     * @ORM\Column(name="arm", type="integer")
     */
    private $arm;

    /**
     * @var integer
     *
     * @ORM\Column(name="its_protocol", type="integer")
     */
    private $itsProtocol;

    /**
     * @var integer
     *
     * @ORM\Column(name="its_approved", type="smallint")
     */
    private $itsApproved;

    /**
     * @var integer
     *
     * @ORM\Column(name="its_on_approval", type="smallint")
     */
    private $itsOnApproval;

    /**
     * @var integer
     *
     * @ORM\Column(name="its_not_approved", type="smallint")
     */
    private $itsNotApproved;
    
    /**
     * @var integer
     *
     */
    private $cabinetSum;
    
    /**
     * @var NCUO\FoivPortal\Entity\User
     *
     */
    private $foivCabinets;
    
    /**
     * @var integer
     *
     */
    private $emailSum;
    
    /**
     * @var Array
     *
     */
    private $itsProtocolsByStatus;

    /**
     * @var integer
     *
     */
    private $systemSum;

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
     * Set foivId
     *
     * @param \NCUO\FoivBundle\Entity\Foiv $foivId
     * @return FoivItsMonitoring
     */
    public function setFoivId(\NCUO\FoivBundle\Entity\Foiv $foivFk = null)
    {
        $this->foivId = $foivId;

        return $this;
    }

    /**
     * Get foivId
     *
     * @return \NCUO\FoivBundle\Entity\Foiv 
     */
    public function getFoivId()
    {
        return $this->foivId;
    }

    /**
     * Set convention
     *
     * @param integer $convention
     * @return FoivItsMonitoring
     */
    public function setConvention($convention)
    {
        $this->convention = $convention;

        return $this;
    }

    /**
     * Get convention
     *
     * @return integer 
     */
    public function getConvention()
    {
        return $this->convention;
    }

    /**
     * Set protocolIts
     *
     * @param integer $protocolIts
     * @return FoivItsMonitoring
     */
    public function setProtocolIts($protocolIts)
    {
        $this->protocolIts = $protocolIts;

        return $this;
    }

    /**
     * Get protocolIts
     *
     * @return integer 
     */
    public function getProtocolIts()
    {
        return $this->protocolIts;
    }

    /**
     * Set cabinet
     *
     * @param integer $cabinet
     * @return FoivItsMonitoring
     */
    public function setCabinet($cabinet)
    {
        $this->cabinet = $cabinet;

        return $this;
    }

    /**
     * Get cabinet
     *
     * @return integer 
     */
    public function getCabinet()
    {
        return $this->cabinet;
    }

    /**
     * Set email
     *
     * @param integer $email
     * @return FoivItsMonitoring
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return integer 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set dataService
     *
     * @param integer $dataService
     * @return FoivItsMonitoring
     */
    public function setDataService($dataService)
    {
        $this->dataService = $dataService;

        return $this;
    }

    /**
     * Get dataService
     *
     * @return integer 
     */
    public function getDataService()
    {
        return $this->dataService;
    }

    /**
     * Set vipnet
     *
     * @param integer $vipnet
     * @return FoivItsMonitoring
     */
    public function setVipnet($vipnet)
    {
        $this->vipnet = $vipnet;

        return $this;
    }

    /**
     * Get vipnet
     *
     * @return integer 
     */
    public function getVipnet()
    {
        return $this->vipnet;
    }

    /**
     * Set gis
     *
     * @param integer $gis
     * @return FoivItsMonitoring
     */
    public function setGis($gis)
    {
        $this->gis = $gis;

        return $this;
    }

    /**
     * Get gis
     *
     * @return integer 
     */
    public function getGis()
    {
        return $this->gis;
    }

    /**
     * Set resourcesEngaged
     *
     * @param integer $resourcesEngaged
     * @return FoivItsMonitoring
     */
    public function setResourcesEngaged($resourcesEngaged)
    {
        $this->resourcesEngaged = $resourcesEngaged;

        return $this;
    }

    /**
     * Get resourcesEngaged
     *
     * @return integer 
     */
    public function getResourcesEngaged()
    {
        return $this->resourcesEngaged;
    }

    /**
     * Set video
     *
     * @param integer $video
     * @return FoivItsMonitoring
     */
    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return integer 
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set arm
     *
     * @param integer $arm
     * @return FoivItsMonitoring
     */
    public function setArm($arm)
    {
        $this->arm = $arm;

        return $this;
    }

    /**
     * Get arm
     *
     * @return integer 
     */
    public function getArm()
    {
        return $this->arm;
    }

    /**
     * Set itsProtocol
     *
     * @param integer $itsProtocol
     * @return FoivItsMonitoring
     */
    public function setItsProtocol($itsProtocol)
    {
        $this->itsProtocol = $itsProtocol;

        return $this;
    }

    /**
     * Get itsProtocol
     *
     * @return integer 
     */
    public function getItsProtocol()
    {
        return $this->itsProtocol;
    }

    /**
     * Set itsApproved
     *
     * @param integer $itsApproved
     * @return FoivItsMonitoring
     */
    public function setItsApproved($itsApproved)
    {
        $this->itsApproved = $itsApproved;

        return $this;
    }

    /**
     * Get itsApproved
     *
     * @return integer 
     */
    public function getItsApproved()
    {
        return $this->itsApproved;
    }

    /**
     * Set itsOnApproval
     *
     * @param integer $itsOnApproval
     * @return FoivItsMonitoring
     */
    public function setItsOnApproval($itsOnApproval)
    {
        $this->itsOnApproval = $itsOnApproval;

        return $this;
    }

    /**
     * Get itsOnApproval
     *
     * @return integer 
     */
    public function getItsOnApproval()
    {
        return $this->itsOnApproval;
    }

    /**
     * Set itsNotApproved
     *
     * @param integer $itsNotApproved
     * @return FoivItsMonitoring
     */
    public function setItsNotApproved($itsNotApproved)
    {
        $this->itsNotApproved = $itsNotApproved;

        return $this;
    }

    /**
     * Get itsNotApproved
     *
     * @return integer 
     */
    public function getItsNotApproved()
    {
        return $this->itsNotApproved;
    }
    
    /**
     * Get cabinetSum
     *
     * @return integer 
     */
    public function getCabinetSum()
    {
        return $this->cabinetSum; 
    }
    
    /**
     * Set cabinetSum
     *
     * @return integer 
     */
    public function setCabinetSum($data)
    {
        $this->cabinetSum = $data;
        
        return $this;
    }
    
    /**
     * Get FoivCabinets
     *
     * @return NCUO\FoivPortal\Entity\User 
     */
    public function getFoivCabinets()
    {
        return $this->foivCabinets ; 
    }
    
    /**
     * Set FoivCabinets
     *
     * @return NCUO\FoivPortal\Entity\User 
     */
    public function setFoivCabinets($data)
    {
        $this->foivCabinets = $data;
        
        return $this;
    }
    
    /**
     * Get emailSum
     *
     * @return integer 
     */
    public function getEmailSum()
    {
        return $this->emailSum; 
    }
    
    /**
     * Set emailSum
     *
     * @return integer 
     */
    public function setEmailSum($data)
    {
        $this->emailSum = $data;
        
        return $this;
    }
    /**
     * Get itsProtocolsByStatus
     *
     * @return Array 
     */
    public function getItsProtocolsByStatus()
    {
        return $this->itsProtocolsByStatus; 
    }
    
    /**
     * Set itsProtocolsByStatus
     *
     * @return Array 
     */
    public function setItsProtocolsByStatus($data)
    {
        $this->itsProtocolsByStatus = $data;
        
        return $this;
    }
    
    /**
     * Get systemSum
     *
     * @return integer 
     */
    public function getSystemSum()
    {
        return $this->systemSum; 
    }
    
    /**
     * Set systemSum
     *
     * @return integer 
     */
    public function setSystemSum($data)
    {
        $this->systemSum = $data;
        
        return $this;
    }
}
