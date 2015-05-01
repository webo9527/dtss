<?php
    namespace model;
    //实体
	class Body
	{
		public $bid; //实体id
		public $edid; //实体类型id 
        public $ptys; //属性 array
        public function __construct()
        {
            $this->ptys=array();
        }
		public function addpty($pty){
		  array_push($this->ptys,$pty);
		}
        
        public function __get($name) {
            foreach($this->ptys as $pty){
                if($pty->name==$name){
                    return $pty->value;
                }
            }
            return null;
        }
        
        public function __set($name,$value) {
            foreach($this->ptys as $pty){
                if($pty->name==$name){
                    $pty->value=$value;
                    (new BodyManager())->updateProperty($pty->bpid,$pty->vtype,$value);
                }
            }
            return null; 
        }
	}
?>