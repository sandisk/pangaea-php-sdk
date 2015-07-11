<?php
namespace Pangaea;

use \JsonSerializable;

class Category implements JsonSerializable
{
    private $values;

    /**
     * Create a new category id, and specify whether online
     *
     * @param $id
     * @param $online
     */
    public function __construct($id, $online)
    {
        $this->values['c_category_id'] = $id;
        $this->values['online_flag']   = $online;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->values['c_category_name'] = $name;
    }

    /**
     * @param $id
     * @param $name
     */
    public function setParent($id, $name)
    {
        $this->values['p_category_id']   = $id;
        $this->values['p_category_name'] = $name;
    }

    /**
     * Specify whether the category is a shelf, i.e. has no sub-categories so holds products
     *
     * @param $shelf
     */
    public function setShelf($shelf)
    {
        $this->values['is_shelf'] = $shelf;
    }

    /**
     * @todo: should SPEC_DEFAULT_END_DATE be used if $end empty?
     *
     * @param $start
     * @param $end
     */
    public function setDates($start, $end)
    {
        $this->values['online_from'] = Date::format($start);
        $this->values['online_to']   = Date::format($end);
    }

    public function jsonSerialize()
    {
        return $this->values;
    }
}
