<?php
namespace App\NCUO\FoivBundle\Entity;
use Doctrine\ORM\EntityRepository;


class FoivDepartmentsRepository extends EntityRepository
{
	/*
	public function getDataFromOldFields()
	{
		$SQLText = "select dp.id as \"ID\", dp.name as \"NAME\", dp.position as \"POS\", dp.address as \"ADDR\", ";
		$SQLText .="dp.email as \"EMAIL\", dp.phone \"PHONE\", dp.website_url as \"WURL\", dp.photo as \"PHOTO\",  ";
		$SQLText .="dp.photo_id as \"FID\", dp.photo_url as \"PURL\", dp.person_id as \"PID\" from eif_data2.dict_foiv_dpt_persons dp";
		
		$em = $this->getEntityManager();
		$connection = $em->getConnection();
		$statement = $connection->prepare($SQLText);
		$statement->execute();
		
		$results = $statement->fetchAll();
		
		return $results;
	}
	*/
}
?>