<?php

namespace AppBundle\Repository;

class TruckDayRepository extends \Doctrine\ORM\EntityRepository
{
    public function findWithRestCapacity(\DateTime $date, string $postalCode)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT td, (td.capacity - SUM(co.quantity)) AS restCapacity 
                    FROM AppBundle:TruckDay td
                    LEFT JOIN AppBundle:Commande co WITH td.id = co.truckDay
                    where td.date = :date
                    and td.postalCode = :postalCode'
            )
            ->setParameter(':date', $date->format('Y-m-d'))
            ->setParameter(':postalCode', $postalCode)
            ->getSingleResult();
    }

    public function findByIdWithRestCapacity(int $id)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT td, (td.capacity - SUM(co.quantity)) AS restCapacity 
                    FROM AppBundle:TruckDay td
                    LEFT JOIN AppBundle:Commande co WITH td.id = co.truckDay
                    where td.id = :id'
            )
            ->setParameter(':id', $id)
            ->getSingleResult();
    }
}
