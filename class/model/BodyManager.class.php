<?php
    namespace model; 
    set_include_path(get_include_path() . PATH_SEPARATOR .APP_ROOT_DIR.'/utils/phplinq/');
    
    require_once 'PHPLinq/LinqToObjects.php';
    
    class BodyManager
    {
        public $dao;
        public $bds;
        
        public  function __construct() {
            $this->dao = new \dao\DaoBody();
            $this->bds=$this->dao->getBDS();
        }
                
        public function updateProperty($bpid,$vtype,$value){
            $this->dao->updateProperty($bpid,$vtype,$value);
        }
        public function getBodyDef($catalog,$entity){
            $catalog_man = new EntityCatalogManager();
            $catalog_def = $catalog_man->loadCatalogByName($catalog,$entity);
            return $this->loadEntityDefinition($catalog_def,$entity);
        }
        /*
		public function add_new_body($body){
            $this->dao->add_new_body($body);
		}
        public function update_body($body){
            $this->dao->update_body($body);
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
            $p=$this->bds["p"][$bid];
            if(($p!=null)&&(is_array($p))){
                foreach($p as $key=>$value){
                    array_push($ret,array("name"=>$this->bds["pd"][$key]["n"],"value"=>$value["v"]));
                }
                return $ret;    
            }
            return null;
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
            foreach($bl as $key=>$value){
                $b0=array();
                foreach($cols as $c){
                    if($c=="id"){$b0[$c]=$key;}
                    else if($c=="n"){$b0[$c]=$this->bds["ed"][$value];}
                    else {$b0[$c]=$this->bds["p"][$key][$c]["v"];}
                }
                array_push($blret,$b0);
            }
            return array("b"=>$blret,"cl"=>$cls);
        }
        /*
        $filter=array(array("e","=",1287),array(1051,"=","重庆")......)
        */
        public function getBodysByFilter($filter){
            $ret=array();
            foreach($this->bds["b"] as $key=>$body){
                $mk=true;
                foreach($filter as $f1){
                    if(is_array($f1) && (count($f1)>2)){
                        if($f1[0]=="e"){ //实体类型
                            if($f1[1]=="i"){
                                $el=explode(",",$f2[2]);
                                $mk=false;
                                foreach($el as $ei){if($ei==$body){$mk=true;break;}}
                            }
                            else if($f1[1]=="="){if($f1[2]!=$body){$mk=false;break;}}
                            else {if($f1[2]==$body){$mk=false;break;}}
                            if(!$mk){break;}
                        }
                        else {//属性过滤
                            if($this->bds["p"][$key]!=null){
                                $p=$this->bds["p"][$key][$f1[0]];
                                if($p!=null){
                                    if($f1[1]=="="){if($f1[2]!=$p["v"]){$mk=false;break;}}
                                    else if($f1[1]==">"){if($f1[2]<=$p["v"]){$mk=false;break;}}
                                    else if($f1[1]=="<"){if($f1[2]>=$p["v"]){$mk=false;break;}}
                                    else if($f1[1]=="<>"){if($f1[2]==$p["v"]){$mk=false;break;}}
                                    else if($f1[1]=="i"){if(strpos($p["v"],$f1[2])===false){$mk=false;break;}}
                                }
                            }
                            else {$mk=false;break;}
                        }
                    }
                }
                if($mk){$ret[$key]=$body;}
            }                
            return $ret;
        }

        /*
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
        */
        public function testpl1() { //test php linq
            $result=from('$body')->in(self::$_bodys)->where('$body=>($body->edname=="城域网CR")')->select('$body');
            print_r($result);
        }        
    }
?>