<?php

namespace AppBundle\Repository;

class TruckDayRepository extends \Doctrine\ORM\EntityRepository
{
    public function findWithRestCapacity(\DateTime $date, string $postalCode)
    {
        $res = $this->getEntityManager()
            ->createQuery(
                'SELECT td, (td.capacity - SUM(co.quantity)) AS restCapacity 
                    FROM AppBundle:TruckDay td
                    LEFT JOIN AppBundle:Commande co WITH td.id = co.truckDay
                    WHERE td.date = :date
                    AND td.postalCode = :postalCode
                    GROUP BY td.id
                    ORDER BY td.id DESC'
            )
            ->setParameter(':date', $date->format('Y-m-d'))
            ->setParameter(':postalCode', $postalCode)
            ->getResult();
        return $res[0] ?? null;
    }

    public function findByIdWithRestCapacity(int $id)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT td, (td.capacity - SUM(co.quantity)) AS restCapacity 
                    FROM AppBundle:TruckDay td
                    LEFT JOIN AppBundle:Commande co WITH td.id = co.truckDay
                    WHERE td.id = :id'
            )
            ->setParameter(':id', $id)
            ->getSingleResult();
    }
}
