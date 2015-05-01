<?php

    namespace dao;
    
    
    class DaoBody {
        private $db;
        
        public function __construct() {
            $this->db = \db\PdoDB::getInstance();
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
        public function getBodyByID($id){
            $body=new \model\Body();
            $body->bid=$id;
            $body->ptys=$this->getBodyPropertyByID($id);
            return $body;
        }
        public function getBodyPropertyByID($id) {
            $rs=$this->db->execute("select bpid,epid,edid,vs,vn,vt from body_pty where bid=?",array($id));
            $ret=array();
            while($row=$rs->fetchObject()){
                $pty=new \model\BodyProperty();
                $pty->bid=$id;
                $pty->bpid=$row->bpid;
                $pty->edid=$row->edid;
                $pty->epid=$row->epid;
                $pty->vtype=$row->vt;
                if($row-vt==0){
                    $pty->value=$row->vn;
                }
                else {
                    $pty->value=$row->vs;
                } 
                array_push($ret,$pty);
    		}
            return $ret;
            
        }
        public function getedid($cat,$entity){
            
        }
        public function getepid($edid,$property){
            
        }
        public function convertFilter($filter){
            $ret=array();

            return $ret;
        }
        public function getBodysByFilter($filter){
            $sql="select distinct bid from body_pty where ";
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
                            $sql=$sql." (".$a.")";
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
                array_push($ret,$this->getBodyByID($row->bid));
    		}
            return $ret;      
        }
        
        /*
        public function getBodysByFilter($edid,$filter){
            $sql="select bid from body_pty where (edid=?)";
            $p=array();
            array_push($p,$edid);
            if(is_array($filter)){
                foreach($filter as $f) {
                    $fa=explode("->",$f);//筛选条件字符串结构:A|O->epid->vs=|vn(=,>,<...)->value->(...)
                    if(count($fa)>3){ 
                        if($fa[0]=="A"){//and 条件
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
                        $sql=$sql." and (".$a.")";
                    }
                }
            }
            $rs=$this->db->execute($sql,$p);
            $ret=array();
            while($row=$rs->fetchObject()){
                array_push($ret,$this->getBodyByID($row->bid));
    		}
            return $ret;      
        }*/

		public function get_property_def($eid) {
			$sql="select property_defination.id, property_defination.title, property_defination.value_type from entity_property inner join property_defination on entity_property.property_id=property_defination.id where entity_property.entity_id=%s";
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
			$this->db->execute("INSERT INTO body_pty(bpid,bid,epid,edid,vs,vn,vt) VALUES(?,?,?,?,?,?,?);",
                                array(\model\TicketService::GetId($this->db),
                                      $property->bid,
                                      $property->epid,
                                      $property->edid,
                                      $vs,$vn,$vt));
        }
    }

?>