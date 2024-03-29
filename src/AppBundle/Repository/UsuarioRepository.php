<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UsuarioRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class UsuarioRepository extends EntityRepository
{
    public function listadoUsuarios()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.dni', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function usuarioPassword($id)
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }



}
