<?php
namespace App\NCUO\FoivBundle\Entity;
use Doctrine\ORM\EntityRepository;


class FoivPvoPersonsRepository extends EntityRepository
{
	public function getDataFromOldFields()
	{
		$SQLText = "select pp.id as \"ID\", pp.fio as \"FIO\", pp.position as \"POS\", pp.address as \"ADDR\", pp.email as \"EMAIL\", pp.phone \"PHONE\", pp.website_url as \"WURL\", pp.photo as \"PHOTO\",  pp.photo_url as \"PURL\", pp.biography as \"BIO\", pp.person_id as \"PID\" ";
		$SQLText .=" from eif_data2.dict_foiv_pvo_persons pp order by pp.id ";
		
		$em = $this->getEntityManager();
		$connection = $em->getConnection();
		$statement = $connection->prepare($SQLText);
		$statement->execute();
		
		$results = $statement->fetchAll();
		
		return $results;
	}
}
?>
