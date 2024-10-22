<?php

namespace App\NCUO\EifBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="FileRepository")
 * @ORM\Table(name="eif.files") 
 */

class File {
    
    /**
     * @ORM\Column(type="guid", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */ 
        
    protected $id_file;
    
    /**
     * @ORM\ManyToOne(targetEntity="Protocol", inversedBy="files")
     * @ORM\JoinColumn(name="id_protocol", referencedColumnName="id_protocol")
     */     
    
    protected $protocol;
            
    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */         
    
    protected $file_name;
    
    /**
     * @ORM\Column(type="bigint", nullable=false)
     */      
    
    protected $file_data_sizeb;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */       
    
    protected $file_upload_date;
    
    /**
     * @ORM\ManyToOne(targetEntity="FileStatus", inversedBy="files")
     * @ORM\JoinColumn(name="id_status", referencedColumnName="id_status")
     */     
    
    protected $file_status;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */         
    
    protected $status_msg;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */     
    
    protected $migration_flag;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */       
    
    protected $migration_date;
	

    /**
     * Get id_file
     *
     * @return guid 
     */
    public function getIdFile()
    {
        return $this->id_file;
    }

    /**
     * Set id_file
     *
     * @return File 
     */
    public function setIdFile($id_file)
    {
        $this->id_file = $id_file;
		
		return $this;
    }		
	
    /**
     * Set file_name
     *
     * @param string $fileName
     * @return File
     */
    public function setFileName($fileName)
    {
        $this->file_name = $fileName;

        return $this;
    }

    /**
     * Get file_name
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * Set file_data_sizeb
     *
     * @param integer $fileDataSizeb
     * @return File
     */
    public function setFileDataSizeb($fileDataSizeb)
    {
        $this->file_data_sizeb = $fileDataSizeb;

        return $this;
    }

    /**
     * Get file_data_sizeb
     *
     * @return integer 
     */
    public function getFileDataSizeb()
    {
        return $this->file_data_sizeb;
    }

    /**
     * Set file_upload_date
     *
     * @param \DateTime $fileUploadDate
     * @return File
     */
    public function setFileUploadDate($fileUploadDate)
    {
        $this->file_upload_date = $fileUploadDate;

        return $this;
    }

    /**
     * Get file_upload_date
     *
     * @return \DateTime 
     */
    public function getFileUploadDate()
    {
        return $this->file_upload_date;
    }

    /**
     * Set status_msg
     *
     * @param string $statusMsg
     * @return File
     */
    public function setStatusMsg($statusMsg)
    {
        $this->status_msg = $statusMsg;

        return $this;
    }

    /**
     * Get status_msg
     *
     * @return string 
     */
    public function getStatusMsg()
    {
        return $this->status_msg;
    }

    /**
     * Set migration_flag
     *
     * @param integer $migrationFlag
     * @return File
     */
    public function setMigrationFlag($migrationFlag)
    {
        $this->migration_flag = $migrationFlag;

        return $this;
    }

    /**
     * Get migration_flag
     *
     * @return integer 
     */
    public function getMigrationFlag()
    {
        return $this->migration_flag;
    }

    /**
     * Set protocol
     *
     * @param \App\NCUO\EifBundle\Entity\Protocol $protocol
     * @return File
     */
    public function setProtocol(\App\NCUO\EifBundle\Entity\Protocol $protocol = null)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Get protocol
     *
     * @return \App\NCUO\EifBundle\Entity\Protocol 
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Set file_status
     *
     * @param \App\NCUO\EifBundle\Entity\FileStatus $fileStatus
     * @return File
     */
    public function setFileStatus(\App\NCUO\EifBundle\Entity\FileStatus $fileStatus = null)
    {
        $this->file_status = $fileStatus;

        return $this;
    }

    /**
     * Get file_status
     *
     * @return \App\NCUO\EifBundle\Entity\FileStatus 
     */
    public function getFileStatus()
    {
        return $this->file_status;
    }
	
    /**
     * Set migration_date
     *
     * @param \DateTime $migrationDate
     * @return File
     */
    public function setMigrationDate($migrationDate)
    {
        $this->migration_date = $migrationDate;

        return $this;
    }

    /**
     * Get migration_date
     *
     * @return \DateTime 
     */
    public function getMigrationDate()
    {
        return $this->migration_date;
    }	
}
