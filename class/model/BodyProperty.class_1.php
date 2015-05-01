<?php
    namespace model;
    //实体属性
	class BodyProperty
	{
		public $bpid; //属性id
		public $bid;  //实体id
		public $epid; //属性类型id 
		public $edid; //实体类型id
		public $value;//属性值
        public $vtype; //值类型 
        public $flag;//标志(是否修改)
        public $name;//属性名称
        public function __construct()
        {
        }
	}
?>