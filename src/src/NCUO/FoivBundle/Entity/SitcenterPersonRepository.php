<?php
namespace App\NCUO\FoivBundle\Entity;
use Doctrine\ORM\EntityRepository;


class SitcenterPersonRepository extends EntityRepository
{
	public function getDataFromOldFields()
	{
		$SQLText = "select sp.id as \"ID\", sp.fio as \"FIO\", sp.position as \"POS\", sp.photo_url as \"PURL\", sp.phone as \"PHONE\", sp.email as \"EMAIL\", sp.person_id as \"PID\" ";
		$SQLText .="from eif_data2.dict_sitcenter_person sp order by sp.id";
		
		$em = $this->getEntityManager();
		$connection = $em->getConnection();
		$statement = $connection->prepare($SQLText);
		$statement->execute();
		
		$results = $statement->fetchAll();
		
		return $results;
	}
}
?>