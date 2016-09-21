<?php

namespace Salamek\Gitlab\Grido\DataSources;

use Grido\DataSources\IDataSource;
use Nette\Object;
use Tracy\Debugger;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class Gitlab extends Object implements IDataSource
{
    /** @var int */
    private $page = 1;

    /** @var int */
    private $perPage = 20;

    /** @var array */
    private $filters = [];

    /** @var null */
    private $orderBy = null;

    /** @var null */
    private $orderByDirection = null;

    /** @var callable */
    public $dataSource = [];

    /**
     * Gitlab constructor.
     * @param callable $dataSource
     */
    public function __construct(callable $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @param array $condition
     * @throws \Exception
     */
    public function filter(array $condition)
    {
        foreach($condition AS $c)
        {
            switch(current($c->condition))
            {
                case 'LIKE ?':
                    $value = trim(current($c->value), '%');
                    break;

                case 'IS NOT NULL':
                    $value = 1;
                    break;
                case '= ?':
                    $value = current($c->value);
                    break;
                default:
                    throw new \Exception(current($c->condition));
                    break;
            }

            $this->filters[current($c->column)] = $value;
        }
    }

    /**
     * @param int $offset
     * @param int $limit
     */
    public function limit($offset, $limit)
    {
        $this->perPage = $limit;
        $this->page = ($offset ? ($offset/$limit) + 1 : 1);
    }

    /**
     * @param array $sorting
     */
    public function sort(array $sorting)
    {
        $this->orderBy = key($sorting);
        $this->orderByDirection = strtolower(current($sorting));
    }

    /**
     * @param mixed $column
     * @param array $conditions
     * @param int $limit
     * @return mixed
     * @throws \Exception
     */
    public function suggest($column, array $conditions, $limit)
    {
        $this->limit(0, $limit);
        $parameters = [];

        foreach($conditions AS $c)
        {
            switch(current($c->condition))
            {
                case 'LIKE ?':
                    $value = trim(current($c->value), '%');
                    break;

                default:
                    $value = $c->value;
                    break;
            }

            $parameters[current($c->column)] = $value;
        }

        $data = $this->dataSource($this->page, $this->perPage, $parameters, $this->orderBy, $this->orderByDirection);

        $return = [];
        foreach($data AS $row)
        {
            if(!isset($row->{$column}))
            {
                throw new \Exception(sprintf('Column %s not found in datasource', $column));
            }

            $return[$row->{$column}] = $row->{$column};
        }

        return array_values($return);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->dataSource($this->page, $this->perPage, $this->filters, $this->orderBy, $this->orderByDirection);
    }

    /**
     * @return integer
     */
    public function getCount()
    {
        return count($this->dataSource(1, 100000000000, $this->filters, $this->orderBy, $this->orderByDirection));
    }
}