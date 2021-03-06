<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Customer;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * Class WindfarmRepository
 *
 * @category Repository
 * @package  AppBundle\Repository
 * @author   Anton Serra <aserratorta@gmail.com>
 */
class WindfarmRepository extends EntityRepository
{
    /**
     * @param integer|null $limit
     * @param string       $order
     *
     * @return QueryBuilder
     */
    public function findAllSortedByNameQB($limit = null, $order = 'ASC')
    {
        $query = $this
            ->createQueryBuilder('w')
            ->orderBy('w.name', $order);

        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }

        return $query;
    }

    /**
     * @param integer|null $limit
     * @param string       $order
     *
     * @return Query
     */
    public function findAllSortedByNameQ($limit = null, $order = 'ASC')
    {
        return $this->findAllSortedByNameQB($limit, $order)->getQuery();
    }

    /**
     * @param integer|null $limit
     * @param string       $order
     *
     * @return array
     */
    public function findAllSortedByName($limit = null, $order = 'ASC')
    {
        return $this->findAllSortedByNameQ($limit, $order)->getResult();
    }

    /**
     * @param integer|null $limit
     * @param string       $order
     *
     * @return QueryBuilder
     */
    public function findEnabledSortedByNameQB($limit = null, $order = 'ASC')
    {
        $query = $this
            ->findAllSortedByNameQB($limit, $order)
            ->where('w.enabled = true');

        return $query;
    }

    /**
     * @param integer|null $limit
     * @param string       $order
     *
     * @return Query
     */
    public function findEnabledSortedByNameQ($limit = null, $order = 'ASC')
    {
        return $this->findEnabledSortedByNameQB($limit, $order)->getQuery();
    }

    /**
     * @param integer|null $limit
     * @param string       $order
     *
     * @return array
     */
    public function findEnabledSortedByName($limit = null, $order = 'ASC')
    {
        return $this->findEnabledSortedByNameQ($limit, $order)->getResult();
    }

    /**
     * @param Customer     $customer
     * @param integer|null $limit
     * @param string       $order
     *
     * @return QueryBuilder
     */
    public function findOnlyAvailableSortedByNameQB(Customer $customer, $limit = null, $order = 'ASC')
    {
        $query = $this->findAllSortedByNameQB($limit, $order);
        $query
            ->where('w.customer IS NULL')
            ->orWhere('w.customer = :customer')
            ->setParameter('customer', $customer);

        return $query;
    }

    /**
     * @param Customer     $customer
     * @param integer|null $limit
     * @param string       $order
     *
     * @return Query
     */
    public function findOnlyAvailableSortedByNameQ(Customer $customer, $limit = null, $order = 'ASC')
    {
        return $this->findOnlyAvailableSortedByNameQB($customer, $limit, $order)->getQuery();
    }

    /**
     * @param Customer     $customer
     * @param integer|null $limit
     * @param string       $order
     *
     * @return array
     */
    public function findOnlyAvailableSortedByName(Customer $customer, $limit = null, $order = 'ASC')
    {
        return $this->findOnlyAvailableSortedByNameQ($customer, $limit, $order)->getResult();
    }
}
