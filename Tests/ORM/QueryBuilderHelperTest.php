<?php

namespace Hautelook\DoctrineExtraBundle\Tests\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Hautelook\DoctrineExtraBundle\ORM\QueryBuilderHelper;
use Prophecy\PhpUnit\ProphecyTestCase;

class QueryBuilderHelperTest extends ProphecyTestCase
{
    public function testJoinPropertyTree()
    {
        $helper = new QueryBuilderHelper();

        $emProphecy = $this->prophesize(EntityManager::CLASS);
        $qb = new QueryBuilder($emProphecy->reveal());
        $qb
            ->from('Styles', 'style')
            ->select('style')
        ;

        $helper->joinPropertyTree(
            $qb,
            [
                'quad' => [
                    'businessQuadMaps' => [
                        'businessClassification' => [
                            'businessDivision',
                            'businessDepartment',
                            'businessClass',
                            'businessSubclass',
                        ]
                    ],
                    'category'
                ]
            ]
        );

        $this->assertSame(
            'SELECT '
                .'style, '
                .'quad, '
                .'business_quad_maps, '
                .'business_classification, '
                .'business_division, '
                .'business_department, '
                .'business_class, '
                .'business_subclass, '
                .'category '
            .'FROM Styles style '
            .'LEFT JOIN style.quad quad '
            .'LEFT JOIN quad.businessQuadMaps business_quad_maps '
            .'LEFT JOIN business_quad_maps.businessClassification business_classification '
            .'LEFT JOIN business_classification.businessDivision business_division '
            .'LEFT JOIN business_classification.businessDepartment business_department '
            .'LEFT JOIN business_classification.businessClass business_class '
            .'LEFT JOIN business_classification.businessSubclass business_subclass '
            .'LEFT JOIN quad.category category',
            $qb->getDQL()
        );
    }

    public function testJoinPropertyTreeInnerNoFetch()
    {
        $helper = new QueryBuilderHelper();

        $emProphecy = $this->prophesize(EntityManager::CLASS);
        $qb = new QueryBuilder($emProphecy->reveal());
        $qb
            ->from('Styles', 'style')
            ->select('style')
        ;

        $helper->joinPropertyTree(
            $qb,
            [
                'quad' => [
                    'businessQuadMaps' => [
                        'businessClassification' => [
                            'businessDivision',
                            'businessDepartment',
                            'businessClass',
                            'businessSubclass',
                        ]
                    ],
                    'category'
                ]
            ],
            false,
            false
        );

        $this->assertSame(
            'SELECT '
            .'style '
            .'FROM Styles style '
            .'INNER JOIN style.quad quad '
            .'INNER JOIN quad.businessQuadMaps business_quad_maps '
            .'INNER JOIN business_quad_maps.businessClassification business_classification '
            .'INNER JOIN business_classification.businessDivision business_division '
            .'INNER JOIN business_classification.businessDepartment business_department '
            .'INNER JOIN business_classification.businessClass business_class '
            .'INNER JOIN business_classification.businessSubclass business_subclass '
            .'INNER JOIN quad.category category',
            $qb->getDQL()
        );
    }

    public function testHasJoin()
    {
        $helper = new QueryBuilderHelper();

        $emProphecy = $this->prophesize(EntityManager::CLASS);
        $qb = new QueryBuilder($emProphecy->reveal());
        $qb
            ->from('Styles', 'style')
            ->select('style')
        ;
        $qb->join('style.quad', 'quad');
        $qb->leftJoin('quad.classification', 'foo_classification');

        $this->assertTrue($helper->hasJoin($qb, 'style', 'style.quad'));
        $this->assertTrue($helper->hasJoin($qb, 'style', 'quad.classification'));
        $this->assertFalse($helper->hasJoin($qb, 'style', 'style.hello'));
        $this->assertFalse($helper->hasJoin($qb, 'style', 'hello.helloAgain'));
        $this->assertFalse($helper->hasJoin($qb, 'hello', 'style.quad'));
        $this->assertFalse($helper->hasJoin($qb, 'hello', 'hello.quad'));
    }

    public function testHasSelect()
    {
        $helper = new QueryBuilderHelper();

        $emProphecy = $this->prophesize(EntityManager::CLASS);
        $qb = new QueryBuilder($emProphecy->reveal());
        $qb
            ->from('Styles', 'style')
            ->select('style')
            ->addSelect('style.quad', 'foo.bar')
        ;

        $this->assertTrue($helper->hasSelect($qb, 'style'));
        $this->assertTrue($helper->hasSelect($qb, 'style.quad'));
        $this->assertTrue($helper->hasSelect($qb, 'foo.bar'));
        $this->assertFalse($helper->hasSelect($qb, 'foo'));
    }
}
