<?php

namespace App\NCUO\MapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="MetaUnloadRepository")
 * @ORM\Table(name="gis_unload.meta_unload") 
 */

class MetaFunction {
    
    /**
     * @ORM\Column(type="guid", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */ 
        
    protected $id;
               
    /**
     * @ORM\Column(type="string", length=254, nullable=false)
     */         
    
    protected $name;
    
    /**
     * @ORM\Column(type="text", nullable=false)
     */        
    
    protected $command;    
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */       
    
    protected $created;
    
    
    /**
     * Get id
     *
     * @return guid 
     */
    public function getId()
    {
        return $this->id;
    }
    
     /**
     * Set $id
     *
     * @param string $id
     * @return MetaFunction
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set name
     *
     * @param string $fnName
     * @return MetaFunction
     */
    public function setName($fnName)
    {
        $this->name = $fnName;
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
     * Get $command
     *
     * @return string 
     */
    public function getCommand()
    {
        return $this->command;
    }
    
    /**
     * Set $command
     *
     * @param string $cmd
     * @return MetaFunction
     */
    public function setCommand($cmd)
    {
        $this->command = $cmd;
        return $this;
    }

    /**
     * Get $created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }
    
    /**
     * Set $created
     *
     * @param \DateTime $createDate
     * @return MetaFunction
     */
    public function setCreateDate($createDate)
    {
        $this->created = $createDate;
        return $this;
    }
    
    // конструктор. выставить текущее время
    public function __construct()
    {
        $this->created = new \DateTime(); 
    }
}
