<?php
namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\EntityRepository;


class FoivFdoPersonsRepository extends EntityRepository
{
	public function getDataFromOldFields()
	{
		$SQLText = "select fp.id as \"ID\", fp.fio as \"FIO\", fp.position as \"POS\", fp.photo as \"PHOTO\", fp.photo_url as \"PURL\", ";
		$SQLText .="fp.phone as \"PHONE\", fp.address as \"ADDR\", fp.email as \"EMAIL\", fp.website_url as \"WURL\", fp.person_id as \"PID\" ";
		$SQLText .="from eif_data2.dict_foiv_fdo_persons fp order by fp.id" ;
		
		$em = $this->getEntityManager();
		$connection = $em->getConnection();
		$statement = $connection->prepare($SQLText);
		$statement->execute();
		
		$results = $statement->fetchAll();
		
		return $results;
	}
}
?>