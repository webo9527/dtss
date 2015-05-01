<?php
   
    namespace dao;   
    class DaoBody {
        private static $_bds = null;
        private $db;

        public function __construct() {
            $this->db = \db\PdoDB::getInstance();
        }
        public function getBDS(){
            if(null==self::$_bds){
                
                $eds=array();
                $rs=$this->db->execute("select id, name from entity_definition",null);
                while($r=$rs->fetchObject()){
                    $eds[$r->id]=$r->name;
                }

                $pds=array();
                $rs=$this->db->execute("select id, name, value_type from property_definition",null);
                while($r=$rs->fetchObject()){
                    $pds[$r->id]["n"]=$r->name;
                    $pds[$r->id]["v"]=$r->value_type;
                }
                
                $bdys=array();
                $rs=$this->db->execute("select bid, edid from body",null);
                while($r=$rs->fetchObject()){
                    $bdys[$r->bid]=$r->edid;
                }
                
                $ptys=array();
                $rs=$this->db->execute("select bpid,epid,edid,bid,vs,vn,vt from body_pty",null);
                while($r=$rs->fetchObject()){
                    $pty=array();
                    $pty["b"]=$r->bpid;
                    $pty["e"]=$r->edid;
                    if($r->vt==0){
                        $pty["v"]=$r->vn;
                    }
                    else {
                        $pty["v"]=$r->vs;
                    }
                    $ptys[$r->bid][$r->epid]=$pty; 
                } 
                     
                self::$_bds=array("ed"=>$eds,"pd"=>$pds,"b"=>$bdys,"p"=>$ptys);    
            }
            return self::$_bds;
        }
        
        public function updateProperty($bpid,$vtype,$value){
            $vs=null;
            $vn=null;
            if(($vtype=="NUMBER")||($vtype==0)){
                $vn=$value;       
            }
            else {
                $vs=$value;
            }
            $this->db->execute("update body_pty set vs=?,vn=? where bpid=?",array($vs,$vn,$bpid)); 
        }
        public function getBodyByID($id,$name,$edid){
           
            $body=new \model\Body();
            $body->bid=$id;
			$body->edname=$name;
            $body->edid=$edid;
            $body->ptys=$this->getBodyPropertyByID($id);
            return $body;
            
            $this->getBodyPropertyByID($id);
        }
        public function getBodyPropertyByID($id) {
            $rs=$this->db->execute("select body_pty.bpid,body_pty.epid,body_pty.edid,body_pty.vs,body_pty.vn,body_pty.vt,property_definition.name from property_definition inner join body_pty on body_pty.epid=property_definition.id where body_pty.bid=?",array($id));
            
            $ret=array();
            while($row=$rs->fetchObject()){
                //echo "p".$row->bpid;
                $pty=new \model\BodyProperty();
                $pty->bid=$id;
                $pty->bpid=$row->bpid;
                $pty->edid=$row->edid;
                $pty->epid=$row->epid;
                $pty->vtype=$row->vt;
				$pty->name=$row->name;
                if($row->vt==0){
                    $pty->value=$row->vn;
                }
                else {
                    $pty->value=$row->vs;
                } 
                array_push($ret,$pty);
    		}
            return $ret;
            
        }
        public function getBdys(){
            $rs=$this->db->execute("select bid, edid from body",null);
            $bdys=array();
            $ptys=array();
            while($row=$rs->fetchObject()){
                $bdys[$row->bid]=$row->edid;
            }
            
            $rs=$this->db->execute("select bpid,epid,edid,bid,vs,vn,vt from body_pty",null);
            while($rw=$rs->fetchObject()){
                $pty=array();
                $pty["b"]=$rw->bpid;
                $pty["p"]=$rw->epid;
                $pty["e"]=$rw->edid;
                if($rw->vt==0){
                    $pty["v"]=$rw->vn;
                }
                else {
                    $pty["v"]=$rw->vs;
                }
                $ptys[$rw->bid]=$pty; 
            }          
            //print_r(bdys);  
            //return $ret;            
        }
        public function getBodys(){
            $sql="select body.bid, body.edid, entity_definition.name from entity_definition inner join body on entity_definition.id=body.edid ";
            $rs=$this->db->execute($sql,null);
            $ret=array();
            while($row=$rs->fetchObject()){
                //array_push($ret,$this->getBodyByID($row->bid,$row->name,$row->edid));
                $this->getBodyByID($row->bid,$row->name,$row->edid);
    		}
            return $ret;  
        }
        //获得树节点过滤规则
        public function treeNodeFilter($id){
            $rs=$this->db->execute("select filter from body_tree where id in ($id)",null);
            $ret=array();
            while($row=$rs->fetchObject()){
                array_push($ret,explode("$",$row->filter));
            }
            return $ret;
        }
        //获得树节点显示的数据列
        public function treeNodeColumns($id){
            $a=explode(",",$id);
            $b=end($a);
            return $this->db->get_var("select cols from body_tree where id=$b");
        }
        /*未来完善（获得某些用于筛选属性的唯一值列表）
        public function getOpts(){
            $sql="select tnid, filter from tree_node where filter<>null";
            $rs=$this->db->execute($sql,null);
            $ret=array();
            while($row=$rs->fetchObject()){
                $opts=array();
                $a=$this->db->get_var("select vt from body_pty where epid=$row->filter top 1");
                $v="vn";
                if($a==1){
                    $v="vs";
                }
                $ro=$this->db->execute("select distinct $v as v from body_pty where epid=?",array($row->filter));
                while($rw=$ro->fetchObject()){
                    array_push($opts,$rw->v);
                }
                array_push($ret,array($row->filter,$opts));
            }
            return $ret;
        }
        */
        public function getBodysByFilter($filter){
            $sql="select distinct body_pty.bid, entity_definition.name from entity_definition inner join body_pty on entity_definition.id=body_pty.bid ";
            $p=array();
            $mk=true;
            if(is_array($filter)){
                $a="";
                foreach($filter as $f) {
                    $fa=explode("->",$f);//筛选条件字符串结构:A|O|E->epid->vs=|vn(=,>,<...)->value->(...)
                    if(count($fa)>3){
                        if($fa[0]=="E"){
                            if(count($fa)==2){
                                $a="(edid=?)";
                                array_push($p,$fa[1]);
                            }
                            else {
                                $a="(edid in (";
                                for($i=1;$i<count($fa);$i++){
                                    if($i==1) {
                                        $a=$a."?";
                                    }
                                    else {
                                        $a=$a.",?";
                                    }
                                    array_push($p,$fa[$i]);
                                }
                                $a=$a."))";
                            }
                        }
                        else if($fa[0]=="A"){//and 条件
                            $a="(epid=".$fa[1].") and (".$fa[2]."?)";
                            array_push($p,$fa[3]);
                        }
                        else if($fa[0]=="O"){ //or 条件
                            $a="(epid=".$fa[1].") and ((".$fa[2]."?)";
                            array_push($p,$fa[3]);
                            for($i=4;$i<count($fa);$i+=2){
                                $a=$a." or (".$fa[$i]."?)";
                                array_push($p,$fa[$i+1]);
                            }
                            $a=$a.")";
                        }
                        if(mk){
                            $sql=$sql." where (".$a.")";
                        }
                        else{
                            $sql=$sql." and (".$a.")";                        
                        }
                    }
                }
            }
            $rs=$this->db->execute($sql,$p);
            $ret=array();
            while($row=$rs->fetchObject()){
                array_push($ret,$this->getBodyByID($row->bid,$row->name,$row->edid));
    		}
            return $ret;      
        }
        
		public function get_property_def($eid) {
			$sql="select property_definition.id, property_definition.title, property_definition.value_type from entity_property inner join property_definition on entity_property.property_id=property_definition.id where entity_property.entity_id=%s";
			$sql=sprintf($sql,$eid);
			return $this->db->execute($sql);
		}
        public function  add_new_body($body) {
            $sql = "INSERT INTO body(bid,edid) VALUES(%s,%s);";
			$id=\model\TicketService::GetId($this->db);
            $sql = sprintf($sql,$id,$body->edid);

			$this->db->execute($sql);
            foreach($body->ptys as $pty){
                $pty->bid=$id;
                $this->add_new_bodyproperty($pty);
            }
			return $id;
        }
		
		public function  del_body($body) {
            $sql = "delete from body where bid=%s;";
            $sql = sprintf($sql,$body->bid);

			$this->db->execute($sql);
			$sql = "delete from body_pty where bid=%s;";
            $sql = sprintf($sql,$body->bid);
			$this->db->execute($sql);
        }
		public function update_body($body){
            foreach($body->ptys as $pty){
                if($pty->flag!=0){
                    $pty->flag=0;
                    $vs=null;
                    $vn=null;
                    if(($property->vtype=="NUMBER")||($property->vtype==0)){
                        $vn=$property->value;       
                    }
                    else {
                        $vs=$property->value;
                    }
                    $this->db->execute("update body_pty set vs=?,vn=? where bpid=?",array($vs,$vn,$pty->bpid));                    
                }
            }
		}
		public function  add_new_bodyproperty($property) {
            $vs=null;
            $vn=null;
            $vt=0;
            if($property->vtype=="NUMBER"){
                $vn=$property->value;
            }
            else {
                $vs=$property->value;
                $vt=1;
            }
            $this->db->execute("INSERT INTO body_pty(bpid,bid,epid,edid,vn,vs,vt) VALUES(?,?,?,?,?,?,?);",
                                    array(\model\TicketService::GetId($this->db),
                                      $property->bid,
                                      $property->epid,
                                      $property->edid,
                                      $vn,$vs,$vt));

        }
        
        
        //递归获得tree结构
        //$data:bodytree数组
        //$pid:父节点id
        //$maxdepth:最大树深度(避免出现死循环)
        public function getTree($data, $pid=0, $maxDepth=0){  
            static $depth = 0;  
            $depth++;  
            if (intval($maxDepth) <= 0) {  
                $maxDepth = count($data) * count($data);  
            }  
            if ($depth > $maxDepth) {  
                exit("error tree:max depth {$maxDepth}");  
            }  
            $tree = array();  
            foreach ($data as $rv) {  
                if ($rv->parentid == $pid) {  
                    $rv->children = $this->getTree($data, $rv->id, $maxDepth);  
                    $tree[] = $rv;  
                }  
            }  
            return $tree;  
        }
        //从db中得到tree结构 
        public function bodyTrees(){
            $rs=$this->db->execute("select id, nodename, filter, parentid, cols from body_tree",null);
            
            $ret=array();
            while($row=$rs->fetchObject()){
                $bt=new \model\BodyTree();
                $bt->id=$row->id;
                $bt->text=$row->nodename;
                $bt->filter=$row->filter;
                $bt->parentid=$row->parentid;
                $bt->cols=$row->cols;
                array_push($ret,$bt);
    		}
            
            return $this->getTree($ret,0);
        }
        
        
        public function bodyTrees_b1($tid){
            $rs=$this->db->execute("select id, tid, tnid, parentid, toid from tree_data",null);
            $ret=array();
            while($row=$rs->fetchObject()){
                $bt=new \model\BodyTree();
                $bt->id=$row->id;
                //未来完善
                array_push($ret,$bt);
            }
        }
        public function getbl($tid){
            $title=array('ID','类型');
            $rs=$this->db->get_var("select filter, cols from body_tree where id=$tid");
            if($row=$rs->fetchObject()){
                $cols=$row->cols;
                $filter=$row->filter;
                
            }
        } 
        

    }

?>