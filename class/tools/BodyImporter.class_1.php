<?php
namespace tools;

class BodyImporter
{
    
    public function ImportFromExcel($importData)
    {
        $catalog_man = new \model\EntityCatalogManager();
        $cat=explode("->",$importData["entityCatalog"]);
        if(count($cat)<2){
            return null;
        }
        $catalog = $catalog_man->loadCatalogByName($cat[0],$cat[1]);
        $edm=new \model\EntityDefinitionManager();
        $entity = $edm->loadEntityDefinition($catalog,$importData["entityDefName"]);

        $datafiledir = APP_ROOT_DIR . "\\..\\dtss\\datafile\\";
        $fileName = $datafiledir . iconv('utf-8', 'gb2312', $importData["file"]);
        $excel = new \file\ExcelParser($fileName);
        $excel->setCurSheetName($importData["sheet"]);
        $excel->load();
        $datas=$excel->getData();
        $titles=$datas[$importData["titleRow"]-1];
        $a=array();
        foreach($entity->property_defs as $p){
            array_push($a,array_search($p->name,$titles));//查找
        }
        $req=explode(",",$importData["requiredCol"]);
        $dao = new \dao\DaoBody();
        for($r=$importData["titleRow"];$r<count($datas);$r++){
            $mk=false;
            foreach($req as $rc){
                if(($datas[$r][$rc-1]==null)||($datas[$r][$rc-1]=="")){
                    $mk=true;
                    break;
                }
            }
            if($mk){
                continue;
            } 
            $body=new \model\Body();
            $body->edid=$entity->id;
            for($i=0;$i<count($entity->property_defs);$i++){
                $pty=new \model\BodyProperty();
                $pty->bid=$body->bid;
                $pty->epid=$entity->property_defs[$i]->id;
                $pty->vtype=$entity->property_defs[$i]->vtype;
                if($a[$i]!==false){
                    $pty->value=$datas[$r][$a[$i]];    
                }
                else {
                    $pty->value=null;
                }
                $body->addpty($pty);
            }            
            $body->bid=$dao->add_new_body($body);
        }
    }
    
    public function test() {
        $this->ImportFromExcel(array("file"=>"表2.2 网络设备现状表v3.9.xls",
                                     "sheet"=>"省网CR现状",  
                                     "titleRow"=>5, 
                                     "entityCatalog"=>"网络层逻辑设备->省网CR",
                                     "entityDefName"=>"省网CR",
                                     "requiredCol"=>"2,3"));
    }
}


?>