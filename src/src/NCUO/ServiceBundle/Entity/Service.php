<?php

namespace App\NCUO\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ServiceRepository")
 * @ORM\Table(name="service.services") 
 */

class Service {
    
    /**
     * @ORM\Column(type="guid", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */ 
        
    protected $id_service;
               
    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */         
    
    protected $service_name;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */        
    
    protected $service_descr;    
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */       
    
    protected $service_create_date;
    
    /**
     * @ORM\Column(type="text", nullable=false)
     */        
    
    protected $shell_cmd;       
    
    /**
     * @ORM\ManyToOne(targetEntity="ServiceStatus", inversedBy="services")
     * @ORM\JoinColumn(name="id_status", referencedColumnName="id_status")
     */     
    
    protected $service_status;
           
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */       
    
    protected $last_start_date;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */       
    
    protected $last_finish_date;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */       
    
    protected $last_output;        
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */     
    
    protected $sched_interval_min;
	
    /**
     * @ORM\Column(type="integer", nullable=true)
     */     
    
    protected $autocontrol_interval_min;	
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */       
    
    protected $sched_next_date;     
       
    /**
     * @ORM\Column(type="text", nullable=false)
     */       
    
    protected $pkill_pattern;         
       

    /**
     * Get id_service
     *
     * @return guid 
     */
    public function getIdService()
    {
        return $this->id_service;
    }

    /**
     * Set service_name
     *
     * @param string $serviceName
     * @return Service
     */
    public function setServiceName($serviceName)
    {
        $this->service_name = $serviceName;

        return $this;
    }

    /**
     * Get service_name
     *
     * @return string 
     */
    public function getServiceName()
    {
        return $this->service_name;
    }

    /**
     * Set service_descr
     *
     * @param string $serviceDescr
     * @return Service
     */
    public function setServiceDescr($serviceDescr)
    {
        $this->service_descr = $serviceDescr;

        return $this;
    }

    /**
     * Get service_descr
     *
     * @return string 
     */
    public function getServiceDescr()
    {
        return $this->service_descr;
    }

    /**
     * Set service_create_date
     *
     * @param \DateTime $serviceCreateDate
     * @return Service
     */
    public function setServiceCreateDate($serviceCreateDate)
    {
        $this->service_create_date = $serviceCreateDate;

        return $this;
    }

    /**
     * Get service_create_date
     *
     * @return \DateTime 
     */
    public function getServiceCreateDate()
    {
        return $this->service_create_date;
    }

    /**
     * Set shell_cmd
     *
     * @param string $shellCmd
     * @return Service
     */
    public function setShellCmd($shellCmd)
    {
        $this->shell_cmd = $shellCmd;

        return $this;
    }

    /**
     * Get shell_cmd
     *
     * @return string 
     */
    public function getShellCmd()
    {
        return $this->shell_cmd;
    }

    /**
     * Set last_start_date
     *
     * @param \DateTime $lastStartDate
     * @return Service
     */
    public function setLastStartDate($lastStartDate)
    {
        $this->last_start_date = $lastStartDate;

        return $this;
    }

    /**
     * Get last_start_date
     *
     * @return \DateTime 
     */
    public function getLastStartDate()
    {
        return $this->last_start_date;
    }

    /**
     * Set last_finish_date
     *
     * @param \DateTime $lastFinishDate
     * @return Service
     */
    public function setLastFinishDate($lastFinishDate)
    {
        $this->last_finish_date = $lastFinishDate;

        return $this;
    }

    /**
     * Get last_finish_date
     *
     * @return \DateTime 
     */
    public function getLastFinishDate()
    {
        return $this->last_finish_date;
    }

    /**
     * Set last_output
     *
     * @param string $lastOutput
     * @return Service
     */
    public function setLastOutput($lastOutput)
    {
        $this->last_output = $lastOutput;

        return $this;
    }

    /**
     * Get last_output
     *
     * @return string 
     */
    public function getLastOutput()
    {
        return $this->last_output;
    }

    /**
     * Set sched_interval_min
     *
     * @param integer $schedIntervalMin
     * @return Service
     */
    public function setSchedIntervalMin($schedIntervalMin)
    {
        $this->sched_interval_min = $schedIntervalMin;

        return $this;
    }

    /**
     * Get sched_interval_min
     *
     * @return integer 
     */
    public function getSchedIntervalMin()
    {
        return $this->sched_interval_min;
    }
	
    /**
     * Set autocontrol_interval_min
     *
     * @param integer $autocontrolIntervalMin
     * @return Service
     */
    public function setAutocontrolIntervalMin($autocontrolIntervalMin)
    {
        $this->autocontrol_interval_min = $autocontrolIntervalMin;

        return $this;
    }

    /**
     * Get autocontrol_interval_min
     *
     * @return integer 
     */
    public function getAutocontrolIntervalMin()
    {
        return $this->autocontrol_interval_min;
    }	

    /**
     * Set sched_next_date
     *
     * @param \DateTime $schedNextDate
     * @return Service
     */
    public function setSchedNextDate($schedNextDate)
    {
        $this->sched_next_date = $schedNextDate;

        return $this;
    }

    /**
     * Get sched_next_date
     *
     * @return \DateTime 
     */
    public function getSchedNextDate()
    {
        return $this->sched_next_date;
    }

    /**
     * Set pkill_pattern
     *
     * @param string $pkillPattern
     * @return Service
     */
    public function setPkillPattern($pkillPattern)
    {
        $this->pkill_pattern = $pkillPattern;

        return $this;
    }

    /**
     * Get pkill_pattern
     *
     * @return string 
     */
    public function getPkillPattern()
    {
        return $this->pkill_pattern;
    }    

    /**
     * Set service_status
     *
     * @param \NCUO\ServiceBundle\Entity\ServiceStatus $serviceStatus
     * @return Service
     */
    public function setServiceStatus( $serviceStatus = null)
    {
        $this->service_status = $serviceStatus;

        return $this;
    }

    /**
     * Get service_status
     *
     * @return \NCUO\ServiceBundle\Entity\ServiceStatus 
     */
    public function getServiceStatus()
    {
        return $this->service_status;
    }
}
