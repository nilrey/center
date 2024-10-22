<?php

namespace App\NCUO\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="service.service_statuses") 
 */

class ServiceStatus {
    
    /**
     * @ORM\Column(type="guid", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */ 
        
    protected $id_status;
                
    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */         
    
    protected $status_name;
    
    /**
     * @ORM\Column(type="text", nullable=true)
    */         
    
    protected $status_descr;    
             
    /**
     * @ORM\OneToMany(targetEntity="Service", mappedBy="service_status", fetch="EXTRA_LAZY")
     */
    
    protected $services;
    
   
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->services = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id_status
     *
     * @return guid 
     */
    public function getIdStatus()
    {
        return $this->id_status;
    }

    /**
     * Set status_name
     *
     * @param string $statusName
     * @return ServiceStatus
     */
    public function setStatusName($statusName)
    {
        $this->status_name = $statusName;

        return $this;
    }

    /**
     * Get status_name
     *
     * @return string 
     */
    public function getStatusName()
    {
        return $this->status_name;
    }

    /**
     * Set status_descr
     *
     * @param string $statusDescr
     * @return ServiceStatus
     */
    public function setStatusDescr($statusDescr)
    {
        $this->status_descr = $statusDescr;

        return $this;
    }

    /**
     * Get status_descr
     *
     * @return string 
     */
    public function getStatusDescr()
    {
        return $this->status_descr;
    }

    /**
     * Add services
     *
     * @param \NCUO\ServiceBundle\Entity\Service $services
     * @return ServiceStatus
     */
    public function addService( $services)
    {
        $this->services[] = $services;

        return $this;
    }

    /**
     * Remove services
     *
     * @param \NCUO\ServiceBundle\Entity\Service $services
     */
    public function removeService($services)
    {
        $this->services->removeElement($services);
    }

    /**
     * Get services
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getServices()
    {
        return $this->services;
    }
}
