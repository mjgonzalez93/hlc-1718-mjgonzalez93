<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FacturaReparacionRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class FacturaReparacionRepository extends EntityRepository
{
    public function listadoReparaciones()
    {
        return $this->createQueryBuilder('f')
            ->addSelect('a')
            ->addSelect('m')
            ->addSelect('u')
            ->leftJoin('f.alquiler', 'a')
            ->leftJoin('f.mecanico', 'm')
            ->leftJoin('a.usuario', 'u')
            ->orderBy('f.id')
            ->getQuery()
            ->getResult();
    }
}
