<?php

namespace Msi\Bundle\CmfBundle\Grid;

class GridBuilder
{
    protected $fields = array();

    public function add($name, $type = 'text', array $options = array())
    {
        $this->fields[] = array('name' => $name, 'type' => $type, 'options' => $options);

        return $this;
    }

    public function buildColumns()
    {
        $columns = array();

        foreach ($this->fields as $field) {
            $class = 'Msi\Bundle\CmfBundle\Grid\Column\\'.ucfirst($field['type']).'Column';
            $columns[] = new $class($field);
        }

        return $columns;
    }

    public function getGrid()
    {
        $grid = new Grid();

        $grid->setColumns($this->buildColumns());

        return $grid;
    }
}
