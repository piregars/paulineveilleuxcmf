<?php

namespace Msi\Bundle\CmfBundle\Grid;

class Grid
{
    private $rows;

    private $columns;

    private $sortable = false;

    public function getSortable()
    {
        return $this->sortable;
    }

    public function setSortable($sortable)
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }
}
