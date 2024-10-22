<?php

namespace App\NCUO\EifBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="FileStatusRepository")
 * @ORM\Table(name="eif.file_statuses") 
 */

class FileStatus {
    
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
     * @ORM\Column(type="integer", nullable=false)
     */     
    
    protected $sort_order;    
         
    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="file_status", fetch="EXTRA_LAZY")
     */
    
    protected $files;
    
            
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return FileStatus
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
     * @return FileStatus
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
     * Set sort_order
     *
     * @param integer $sortOrder
     * @return FileStatus
     */
    public function setSortOrder($sortOrder)
    {
        $this->sort_order = $sortOrder;

        return $this;
    }

    /**
     * Get sort_order
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * Add files
     *
     * @param \App\NCUO\EifBundle\Entity\File $files
     * @return FileStatus
     */
    public function addFile(\App\NCUO\EifBundle\Entity\File $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param \App\NCUO\EifBundle\Entity\File $files
     */
    public function removeFile(\App\NCUO\EifBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }
}
