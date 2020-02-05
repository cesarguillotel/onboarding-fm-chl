<?php

namespace AppBundle\Repository;

class TruckDayRepository extends \Doctrine\ORM\EntityRepository
{
    public function findWithRestCapacity(\DateTime $date, string $postalCode, int $minRestCapacity = 0)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT td, (td.capacity - SUM(co.quantite)) AS restCapacity 
                    FROM AppBundle:TruckDay td
                    LEFT JOIN AppBundle:Commande co WITH td.id = co.truckDay
                    where td.date = :date
                    and td.postalCode = :postalCode'
            )
            ->setParameter(':date', $date->format('Y-m-d'))
            ->setParameter(':postalCode', $postalCode)
            ->getSingleResult();
    }

    public function findByIdWithRestCapacity(int $id, int $minRestCapacity = 0)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT td, (td.capacity - SUM(co.quantite)) AS restCapacity 
                    FROM AppBundle:TruckDay td
                    LEFT JOIN AppBundle:Commande co WITH td.id = co.truckDay
                    where td.id = :id'
            )
            ->setParameter(':id', $id)
            ->getSingleResult();
    }
}
