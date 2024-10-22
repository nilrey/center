<?php

namespace App\NCUO\FoivBundle\Entity;

use App\NCUO\FoivBundle\Entity\File;
use Doctrine\ORM\Mapping as ORM;

/**
 * Convention
 *
 * @ORM\Entity
 * @ORM\Table(name="eif_data2.conventions")
 * 
 */
class Convention
{
	/**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.seq_convention", allocationSize=1, initialValue=1)
     */
	private $id;
	
	/**
     * @var date
     *
     * @ORM\Column(name="date_signing", type="string", nullable=false)
     */
	private $date_signing;
	
	 /**
     * @var App\NCUO\FoivBundle\Entity\File
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\File")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id")
     */
	private $file;
	
	 /**
     * Get id
     *
     * @return integer 
     */
	public function getId()
	{
		return $this->id;
	}
	
	
	/*public function  setId($Id)
	{
		$this->Id = $Id;
		return $this;
	}*/
	
	
	/**
     * get date signing
     *
     * 
     * @return string
     */
	public function getDateSigning()
	{
		return $this->date_signing;
	}
	
	public function getDateSigningAsDateTime()
	{
		return new \DateTime($this->date_signing);
	}
	
	/**
     * Set date signing
     *
     * @param string $DateSigning
     * @return Convention
     */
	public function setDateSigning($DateSigning)
	{
		$this->date_signing = $DateSigning;
		return $this;
	}
	
	public function setTitle($Text)
	{
		if($this->file !==NULL)
		{
			return $this->file->setTitle($Text);
		}
		
		return $this;
	}
	
	public function getTitle()
	{
		if($this->file !==NULL)
		{
			return $this->file->getTitle();
		}
		
		return NULL;
	}
	
	/**
     * get date signing
     *
     * 
     * @return FoivBundle\Entity\File
     */
	public function getFile()
	{
		return $this->file;
	}
	
	/**
     * Set date signing
     *
     * @param string $DateSigning
     * @return Convention
     */
	public function setFile(File $File)
	{
		$this->file = $File;
		return $this;
	}
}
?>