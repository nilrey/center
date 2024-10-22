<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * docsMetadata
 *
 * @ORM\Table(name="eif_data2.docs_metadata", indexes={@ORM\Index(name="fki_category", columns={"category"}), @ORM\Index(name="fki_content_fk", columns={"content_id"})})
 * @ORM\Entity
 */
class docsMetadata
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.docs_metadata_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="fk_foiv", type="integer", nullable=false)
     */
    private $fkFoiv;

    /**
     * @var integer
     *
     * @ORM\Column(name="fk_user", type="integer", nullable=false)
     */
    private $fkUser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetimetz", nullable=true)
     */
    private $createdAt = 'now()';

    /**
     * @var integer
     *
     * @ORM\Column(name="content_id", type="integer", nullable=true)
     */
    private $contentId;

    /**
     * @var integer
     *
     * @ORM\Column(name="file_size", type="bigint", nullable=true)
     */
    private $fileSize;

    /**
     * @var string
     *
     * @ORM\Column(name="mime_type", type="string", nullable=true)
     */
    private $mimeType;

    /**
     * @var integer
     *
     * @ORM\Column(name="category", type="integer", nullable=true)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=true)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="fk_system", type="integer", nullable=true)
     */
    private $fkSystem;



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
     * @return docsMetadata
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
     * Set fkFoiv
     *
     * @param integer $fkFoiv
     * @return docsMetadata
     */
    public function setFkFoiv($fkFoiv)
    {
        $this->fkFoiv = $fkFoiv;

        return $this;
    }

    /**
     * Get fkFoiv
     *
     * @return integer 
     */
    public function getFkFoiv()
    {
        return $this->fkFoiv;
    }

    /**
     * Set fkUser
     *
     * @param integer $fkUser
     * @return docsMetadata
     */
    public function setFkUser($fkUser)
    {
        $this->fkUser = $fkUser;

        return $this;
    }

    /**
     * Get fkUser
     *
     * @return integer 
     */
    public function getFkUser()
    {
        return $this->fkUser;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return docsMetadata
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set contentId
     *
     * @param integer $contentId
     * @return docsMetadata
     */
    public function setContentId($contentId)
    {
        $this->contentId = $contentId;

        return $this;
    }

    /**
     * Get contentId
     *
     * @return integer 
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * Set fileSize
     *
     * @param integer $fileSize
     * @return docsMetadata
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get fileSize
     *
     * @return integer 
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return docsMetadata
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string 
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set category
     *
     * @param integer $category
     * @return docsMetadata
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return integer 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return docsMetadata
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set fkSystem
     *
     * @param integer $fkSystem
     * @return docsMetadata
     */
    public function setFkSystem($fkSystem)
    {
        $this->fkSystem = $fkSystem;

        return $this;
    }

    /**
     * Get fkSystem
     *
     * @return integer 
     */
    public function getFkSystem()
    {
        return $this->fkSystem;
    }
}
