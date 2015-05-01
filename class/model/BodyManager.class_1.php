<?php
    namespace model;
    class BodyManager
    {
        public  $dao;
        
        public  function __construct()
        {
            $this->dao = new \dao\DaoBody(); 
        }
        public function updateProperty($bpid,$vtype,$value){
            $this->dao->updateProperty($bpid,$vtype,$value);
        }
        public function getBodyDef($catalog,$entity){
            $catalog_man = new \model\EntityCatalogManager();
            $catalog_def = $catalog_man->loadCatalogByName($catalog,$entity);
            return $this->loadEntityDefinition($catalog_def,$entity);
        }
		public function add_new_body($body){
            $this->dao->add_new_body($body);
		}
        public function update_body($body){
            $this->dao->update_body($body);
        }
		/*
        public function add_new_body($entity,$property)
        {
            $body=new \model\Body();
			$body->edid=$entity;
			$body->bid=$this->dao->add_new_body($body);
			$pty_def=$this->dao->get_property_def($body->edid);//获得实体类型的属性定义列表
			if(!is_array($property)){
				return;
			}
			while($rw=$pty_def->fetchObject()){
				$pty=new \model\BodyProperty();
				$pty->bid=$body->bid;
				$pty->epid=$rw->id;
				$pty->edid=$body->edid;
				if(array_key_exists($rw->title,$property)){
					$pty->value=$property[$rw->title];
				}
				else {
					$pty->value="**********";//原始数据中没有找到该属性
				}
				$this->dao->add_new_bodyproperty($pty);
			}
        }
        */

        
        //由已有的数据衍生新数据
        public function deriveData() {
            
        }
        
        
        //数据校验
        public function checkData() {
            
        }
        
       
        public function getBodysByFilter($filter){            
            return $this->dao->getBodysByFilter($filter);
        }
        public function convertFilter($filter){
            return $this->dao->convertFilter($filter);
        }
        public function getBodys($bodys,$filter){
            $ret=array();
            foreach($bodys as $body){
                $mk=true;
                foreach($filter as $f){
                    $fa=explode("->",$f);
                    if($fa[0]=="E"){
                        
                    }
                    else if($fa[0]=="A") {
                        
                    }
                    else if($fa[0]=="O") {
                        
                    }
                }
                if($mk){
                    array_push($ret,$body);
                }
            }
            return $ret;
        }
    }
?>