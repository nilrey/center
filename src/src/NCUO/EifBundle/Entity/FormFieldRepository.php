<?php

namespace App\NCUO\EifBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * FormFieldRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FormFieldRepository extends EntityRepository
{
    
    /**
     * Функция вывода списка полей формы по блокам
     */
    
    public function getDataByBlocks($form, $start, $length, $search_str) {
        $em = $this->getEntityManager();
        
        // Получаем общее кол-во строк
        $total_cnt = $em->createQuery(
            '
            SELECT COUNT(f)
            FROM NCUOEifBundle:FormField f
            WHERE f.form = :form
                AND LOWER(f.field_name) LIKE LOWER(:search_str)
            '
        )
        ->setParameter('form', $form)
        ->setParameter('search_str', '%' . $search_str . '%')
        ->getSingleScalarResult();
        
        // Получаем выборку строк в заданном диапазоне
        $data = $em->createQuery(
            '
            SELECT f
            FROM NCUOEifBundle:FormField f
            WHERE f.form = :form
                AND LOWER(f.field_name) LIKE LOWER(:search_str)
            ORDER BY f.field_pos ASC
            '
        )
        ->setParameter('form', $form)
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
     * Функция получения кол-ва полей для формы
     */
    
    public function getFieldsCnt($form) {
        return $this->getEntityManager()->createQuery(
            'SELECT COUNT(f) FROM NCUOEifBundle:FormField f WHERE f.form = :form'
        )
        ->setParameter('form', $form)
        ->getSingleScalarResult();        
    }
    
    /*
     * Функция перерасчета порядковых номеров полей для формы
     */
    
    public function updateFieldPos($form, $deleted_pos) {
        return $this->getEntityManager()->createQuery('UPDATE NCUOEifBundle:FormField f SET f.field_pos = f.field_pos - 1 WHERE f.form = :form AND f.field_pos > :del_pos')
        ->setParameter('form', $form)
        ->setParameter('del_pos', $deleted_pos)
        ->execute();
    }
    
    /**
     * Функция получения всех полей формы
     */
    
    public function getAllFields($form) {
        return $this->getEntityManager()->createQuery('SELECT f FROM NCUOEifBundle:FormField f WHERE f.form = :form ORDER BY f.field_pos ASC')
            ->setParameter('form', $form)
            ->getResult();
    }
    
    /**
     * Функция получения ключевых полей для формы
     */
    
    public function getKeyFields($form) {
        return $this->getEntityManager()->createQuery('SELECT f FROM NCUOEifBundle:FormField f WHERE f.form = :form AND f.key_flag = 1 ORDER BY f.field_pos ASC')
            ->setParameter('form', $form)
            ->getResult();        
    }
}
