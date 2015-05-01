<?php

/**
 * @author 
 * @copyright 2015
 */

    namespace model;
    class BodyTree	{
        public $id;
        public $text;
        public $filter;
        public $parentid;
        public $children;

        public function __construct() {
            $this->children=array();
        }
        
    }
?>