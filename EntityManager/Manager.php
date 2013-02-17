<?php

namespace Msi\Bundle\CmfBundle\EntityManager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\QueryBuilder;

class Manager
{
    protected $em;
    protected $repository;
    protected $class;
    protected $appLocales;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function save($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function saveBatch($entity, $i)
    {
        $this->em->persist($entity);
        $batchSize = 20;
        if ($i % $batchSize === 0) {
            $this->em->flush();
            $this->em->clear();
        }
    }

    public function delete($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function change($entity, $request)
    {
        $field = $request->query->get('field');
        $locale = $request->query->get('locale');

        $getter = 'get'.ucfirst($field);
        $setter = 'set'.ucfirst($field);

        if ($locale) {
            $entity->getTranslation($locale)->$getter()
                ? $entity->getTranslation($locale)->$setter(false)
                : $entity->getTranslation($locale)->$setter(true);
        } else {
            $entity->$getter() ? $entity->$setter(false) : $entity->$setter(true);
        }

        $this->save($entity);
    }

    public function savePosition($objects, $disposition)
    {
        $i = 1;
        $l = 0;
        foreach ($objects as $object) {
            if (in_array($object->getId(), $disposition)) {
                $object->setPosition($i + array_search($object->getId(), $disposition) - $l);
                $l++;
            } else {
                $object->setPosition($i);
            }
            $i++;
            $this->save($object);
        }
    }

    public function moveUp($entity)
    {
        $this->repository->moveUp($entity, 1);
        $this->save($entity);
    }

    public function moveDown($entity)
    {
        $this->repository->moveDown($entity, 1);
        $this->save($entity);
    }

    public function create()
    {
        return new $this->class();
    }

    public function getClass()
    {
        return $this->class;
    }

    public function isTranslatable()
    {
        return is_subclass_of($this->class, 'Msi\Bundle\CmfBundle\Entity\Translatable');
    }

    public function getAppLocales()
    {
        return $this->appLocales;
    }

    public function setAppLocales($appLocales)
    {
        $this->appLocales = $appLocales;

        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($this->class);

        return $this;
    }

    public function findAll()
    {
        return $this->repository->findBy(array());
    }

    public function findOneOrCreate($id = null)
    {
        if ($id) {
            $object = $this->getFindByQueryBuilder(array('a.id' => $id))->getQuery()->getOneOrNullResult();
            if (!$object) {
                throw new NotFoundHttpException();
            }
        } else {
            $object = $this->create();
        }

        if ($this->isTranslatable()) {
            $object->createTranslations($this->class.'Translation', $this->appLocales);
        }

        return $object;
    }

    public function getFindByQueryBuilder(array $where = array(), array $join = array(), array $orderBy = array(), $limit = null, $offset = null)
    {
        $qb = $this->repository->createQueryBuilder('a');

        $qb = $this->buildFindBy($qb, $where, $join, $orderBy);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb;
    }

    public function getSearchQueryBuilder($q, array $searchFields, array $where = array(), array $join = array(), array $orderBy = array())
    {
        $qb = $this->repository->createQueryBuilder('a');

        if (count($searchFields)) {
            $q = trim($q);
            $strings = explode(' ', $q);

            $orX = $qb->expr()->orX();
            $i = 1;
            foreach ($searchFields as $field) {
                foreach ($strings as $string) {
                    $token = 'likeMatch'.$i;
                    $orX->add($qb->expr()->like($field, ':'.$token));
                    $qb->setParameter($token, '%'.$string.'%');

                    $orX->add($qb->expr()->like('a.id', ':eqMatchForId'.$i));
                    $qb->setParameter('eqMatchForId'.$i, $string);
                    $i++;
                }
            }

            $qb->andWhere($orX);
        }

        $qb = $this->buildFindBy($qb, $where, $join, $orderBy);

        return $qb;
    }

    protected function buildFindBy(QueryBuilder $qb, array $where, array $join, array $orderBy)
    {
        $i = 1;
        foreach ($where as $k => $v) {
            $token = 'eqMatch'.$i;
            $qb->andWhere($qb->expr()->eq($k, ':'.$token))->setParameter($token, $v);
            $i++;
        }

        foreach ($join as $k => $v) {
            $qb->leftJoin($k, $v);
            $qb->addSelect($v);
        }

        foreach ($orderBy as $k => $v) {
            $qb->addOrderBy($k, $v);
        }

        return $qb;
    }
}
