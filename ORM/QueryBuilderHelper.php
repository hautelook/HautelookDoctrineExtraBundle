<?php

namespace Hautelook\DoctrineExtraBundle\ORM;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class QueryBuilderHelper
{
    /**
     * @see walkPropertyTree
     *
     * @param QueryBuilder $qb
     * @param array        $propertyTree
     * @param boolean      $leftJoin
     * @param boolean      $fetchJoin
     */
    public function joinPropertyTree(QueryBuilder $qb, array $propertyTree, $leftJoin = true, $fetchJoin = true)
    {
        $rootAlias = $qb->getRootAlias();

        $this->walkPropertyTree(
            $rootAlias,
            $propertyTree,
            function ($parentPropertyAlias, $property) use ($qb, $rootAlias, $leftJoin, $fetchJoin) {
                $join = sprintf('%s.%s', $parentPropertyAlias, $property);
                $joinExpr = $this->getJoin($qb, $rootAlias, $join);

                if (null === $joinExpr) {
                    $propertyAlias = Inflector::tableize($property);

                    if ($leftJoin) {
                        $qb->leftJoin($join, $propertyAlias);
                    } else {
                        $qb->innerJoin($join, $propertyAlias);
                    }
                } else {
                    $propertyAlias = $joinExpr->getAlias();
                }

                if ($fetchJoin && !$this->hasSelect($qb, $propertyAlias)) {
                    $qb->addSelect($propertyAlias);
                }

                return $propertyAlias;
            }
        );
    }

    public function getJoin(QueryBuilder $qb, $rootAlias, $joinPath)
    {
        $joins = $qb->getDQLPart('join');

        if (!isset($joins[$rootAlias])) {
            return null;
        }

        $joins = $joins[$rootAlias];
        /** @var $joins \Doctrine\ORM\Query\Expr\Join[] */

        foreach ($joins as $join) {
            if ($joinPath === $join->getJoin()) {
                return $join;
            }
        }

        return null;
    }

    public function hasJoin(QueryBuilder $qb, $rootAlias, $joinPath)
    {
        return null !== $this->getJoin($qb, $rootAlias, $joinPath);
    }

    public function hasSelect(QueryBuilder $qb, $select)
    {
        $selects = $qb->getDQLPart('select');
        /** @var $selects \Doctrine\ORM\Query\Expr\Select[] */

        foreach ($selects as $querySelect) {
            if (in_array($select, $querySelect->getParts())) {
                return true;
            }
        }

        return false;
    }

    /**
     * $propertyTree example:
     *
     * [
     *      'quad' => [
     *          'businessQuadMaps' => [
     *              'businessClassification'
     *          ],
     *          'category'
     *      ]
     * ]
     *
     * @param string   $rootAlias
     * @param array    $propertyTree
     * @param callable $callback
     */
    private function walkPropertyTree($rootAlias, array $propertyTree, $callback)
    {
        $traverse = function ($parentPropertyAlias, array $propertyTree) use (&$traverse, $callback) {
            array_walk(
                $propertyTree,
                function ($value, $key) use ($parentPropertyAlias, $traverse, $callback) {
                    $values = is_array($value) ? $value : array();
                    $property = is_integer($key) ? $value : $key;

                    $propertyAlias = call_user_func($callback, $parentPropertyAlias, $property);

                    $traverse($propertyAlias, $values);
                }
            );
        };
        $traverse($rootAlias, $propertyTree);
    }
}
