<?php

namespace App\NCUO\ServiceBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ServiceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ServiceRepository extends EntityRepository
{
    
   /**
     * Функция вывода списка сервисов по блокам
     */
    
    public function getDataByBlocks($start, $length, $search_str) {
        $em = $this->getEntityManager();
        
        // Получаем общее кол-во строк
        $total_cnt = $em->createQuery(
            '
            SELECT COUNT(s)
            FROM NCUOServiceBundle:Service s
            WHERE LOWER(s.service_name) LIKE LOWER(:search_str)
            '
        )
        ->setParameter('search_str', '%' . $search_str . '%')
        ->getSingleScalarResult();
        
        // Получаем выборку строк в заданном диапазоне
        $data = $em->createQuery(
            '
            SELECT s
            FROM NCUOServiceBundle:Service s
            WHERE LOWER(s.service_name) LIKE LOWER(:search_str)
            ORDER BY s.service_create_date ASC
            '
        )
        ->setParameter('search_str', '%' . $search_str . '%')                
        ->setFirstResult($start)
        ->setMaxResults($length)
        ->getResult();
        
        return array(
            'total_cnt' => $total_cnt,
            'data' => $data
        );
    }
    
    /**
     * Функция вывода списка сервисов, у которых включен регламент
     */
    
    public function getSchedServices() {
        return $this->getEntityManager()->createQuery(
            "SELECT s FROM NCUOServiceBundle:Service s WHERE s.sched_interval_min != 0"
        )
        ->getResult();        
    }
}
