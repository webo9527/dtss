<?php
    namespace model; 
    set_include_path(get_include_path() . PATH_SEPARATOR .APP_ROOT_DIR.'/utils/phplinq/');
    
    require_once 'PHPLinq/LinqToObjects.php';
    
    class BodyManager
    {
        private static $_bodys = null;
        //private static $_treeops = null;  
        public  $dao;
        
        public  function __construct() {
            $this->dao = new \dao\DaoBody();
            $this->dao->getBdys();
            if (null == self::$_bodys){       
                //self::$_bodys=array();
                //self::$_bodys = $this->dao->getBodys();
                //$this->dao->getBdys();       
            } 
            /*未来完善（获得某些用于筛选属性的唯一值列表）
            if (null == self::$_treeops){
                self::$_treeops=array();
                self::$_treeops=$this->dao->getOpts();
            }
            */
        }
                
        public function updateProperty($bpid,$vtype,$value){
            $this->dao->updateProperty($bpid,$vtype,$value);
        }
        public function getBodyDef($catalog,$entity){
            $catalog_man = new EntityCatalogManager();
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
        public function getBodysByFilter($filter){            
            return $this->dao->getBodysByFilter($filter);
        }
        */
        public function convertFilter($filter){
            return $this->dao->convertFilter($filter);
        }
        
        public function getTree() {
            return $this->dao->bodyTrees();
        }
        public function getb($bid){
            $ret=array();
            foreach(self::$_bodys as $body){
                if($body->bid==$bid){
                    foreach($body->ptys as $pty){
                        array_push($ret,array("name"=>$pty->name,"value"=>$pty->value));
                    }
                    return $ret;    
                }
            }
            return null;
        }
        //未来完善
        public function getTree_b1($tid) {
            return $this->dao->bodyTrees_b1($tid);
        }
        public function getBodyList($tid){
            
        }
        
        public function getbl($tid){
            //return $this->dao->treeNodeFilter($tid);
            $bl=$this->getBodysByFilter($this->dao->treeNodeFilter($tid));
            //return $bl;
            $cls=$this->dao->treeNodeColumns($tid);
            $cols=array();
            $a=explode("{field:'",$cls);
            foreach($a as $a1){
                $n=strpos($a1,"'");
                if($n) {
                    $s1=substr($a1,0,$n);
                    array_push($cols,$s1);//获得显示数据列   
                }
            }
            $blret=array();
            foreach($bl as $b){
                $b1=array();
                foreach($cols as $c){
                    $b1[$c]=$b->{$c};
                }
                array_push($blret,$b1);
            }
            return array("b"=>$blret,"cl"=>$cls);
        }
        /*
        $filter=array(array("e","=",1287),array(1051,"=","重庆")......)
        */
        public function getBodysByFilter($filter){
            $ret=array();
            foreach(self::$_bodys as $body){
                $mk=true;
                foreach($body->ptys as $pty){
                    foreach($filter as $f1){
                        if(is_array($f1) && (count($f1)>2)){
                            if($f1[0]=="e"){
                                if($f1[1]=="i"){
                                    $el=explode(",",$f2[2]);
                                    $mk=false;
                                    foreach($el as $ei){
                                        if($ei==$pty->edid){
                                            $mk=true;
                                            break;
                                        }
                                    }
                                    if(!$mk){
                                        break;
                                    }
                                }
                                else if($f1[1]=="="){
                                    if($f1[2]!=$pty->edid){
                                        $mk=false;
                                        break;
                                    }                
                                }
                                else {                                    
                                    if($f1[2]==$pty->edid){
                                        $mk=false;
                                        break;
                                    }                
                                }
                            }
                            else {
                                if($f1[0]==$pty->epid){
                                    if($f1[1]=="="){
                                        if($f1[2]!=$pty->value){
                                            $mk=false;
                                            break;
                                        }
                                    }
                                    else if($f1[1]==">"){
                                        if($f1[2]<=$pty->value){
                                            $mk=false;
                                            break;
                                        }                                        
                                    }
                                    else if($f1[1]=="<"){
                                        if($f1[2]>=$pty->value){
                                            $mk=false;
                                            break;
                                        }                                        
                                    }
                                    else if($f1[1]=="<>"){
                                        if($f1[2]==$pty->value){
                                            $mk=false;
                                            break;
                                        }                                        
                                    }
                                    else if($f1[1]=="i"){
                                        if(strpos($pty->value,$f1[2])===false){
                                            $mk=false;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if($mk){
                    array_push($ret,$body);
                }                
            }
            return $ret;
        }
        
        public function testpl1() { //test php linq
            $result=from('$body')->in(self::$_bodys)->where('$body=>($body->edname=="城域网CR")')->select('$body');
            print_r($result);
            /*
            foreach(self::$_bodys as $body){
                echo $body->p1049;
                //print_r($body->ptys);
            } */   
        }        
    }
?>