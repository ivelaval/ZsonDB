<?php 

require_once 'Core.class.php';

class Zson extends Core{
    
    public $zdatabase;
    public $zdbname = "";
    public $ztablename = array();
    public $zpathstorage = "storage/";
    public $zdebug = array();
    
    public function __construct($arg=array()) {
        $this->zdatabase = new stdClass();
    }
    
    public function __call($name, $arguments) {
        
        if (strstr($name, 'db')) {
            $name = substr($name, 2);
            if(!file_exists($this->zpathstorage.$name.".zson")){                
                $this->createDataBase(array(
                    "name"=>$name
                ));
            }
            $this->zdbname = $name;
        }else if(strstr($name, 'tb')){
            $name = substr($name, 2);
            $this->ztablename = $name;
        }
                
        return $this;
    }
    
    public function getDebug($type='array'){
        $response= array();
        $res = array();
       
        $res = $this->zdebug;
        $response = $res;
               
        return $response;
    }
    
    public function getDebugJson(){
        $response = "";
        $res = array();
     
        $res = $this->zdebug;
        $response = json_encode($res);

        return $response;
    }
    
    public function createDataBase($arg=array()){
        $name = (isset($arg["name"]) && !empty($arg["name"]))?$arg["name"]:$this->zdbname;
        
	if(!file_exists($this->zpathstorage.$name.".zson")){
		
            $this->zdatabase->name = $name;
            $this->makeFile(array(
                "name"=>$name,
                "content"=>$this->zdatabase,
            ));
            
            $this->saveLog(1, "Successful create database ".$name,"createDataBase:");
            $this->zdbname = $name;
        }else{
            $this->saveLog(0.8, "Database {$name} already exists","createDataBase:");
        }
        return $this;
    }
    
    public function createTable($arg=array()){
        
        $db = (isset($arg["db"]) && !empty($arg["db"]))?$arg["db"]:$this->zdbname;
        
        $name = (isset($arg["name"]) && !empty($arg["name"]))?$arg["name"]:""; 
        $fields = (isset($arg["fields"]) && !empty($arg["fields"]))?$arg["fields"]:array();
		
        if($name != "" && !$this->verifyTableExist($db,$name)){
            
                $fieldsObject = new stdClass();
                foreach ($fields as $key => $value) {
                    if(!isset($fieldsObject->{$key})){
                        $tmp = new stdClass(); 
                        $tmp->name = $key;

                        foreach ($value as $keyv => $valuev) {
                            $tmp->{$keyv} = $valuev;
                        }
                        $fieldsObject->{$key} = $tmp; 
                    }else{
                        $this->saveLog(1, "Field ".$key." ya existe","createTable:");
                    }
                }

                $table = new stdClass();
                $table->name = $name;
                $table->fields = $fields;
                $table->autoincrement = 0; 
                $data->{$name} = $table; 	
				
                if(!empty($data)){

                    $this->makeFile(array(
                            "name"=>"{$this->zdbname}.{$name}",
                            "content"=>$data,
                    ));
                    $this->saveLog(1, "Successful create table ".$name,"createTable:");
                }else{
                    $this->saveLog(0.7, "Error creste table ".$name,"createTable:");
                }
                
                

        }else{
            $this->saveLog(0.1, "Table ".$name." already exists","createTable:");
        }
        
        return $this;
    }
    
    public function getTable($arg){
        $table = new stdClass();
        
        $table = $this->readFile(array(
           "db"=>"{$this->zdbname}.{$arg}" 
        ));
           
        $data = $this->readFile(array(
           "db"=>"{$this->zdbname}.{$arg}.data" 
        ));
        
        $table->{$arg}->data = $data;
        
        return $table->{$arg};
    }
    
    public function dropTable($table=""){
        
        $db = $this->zdatabase;
        if($table != "" && unlink($this->zpathstorage."{$this->zdbname}.{$table}.zson")){
            @unlink($this->zpathstorage."{$this->zdbname}.{$table}.data.zson");
            $this->saveLog(0.7, "The table ".$table." was deleted","dropTable:");
        }else{
            $this->saveLog(0.7, "Table ".$table." not exist","dropTable:");
        }
        
        return $this;
    }

    public function insert($arg=array()){
        
        $table = $this->getTable($this->ztablename);
        $fields = $table->fields;
        
        $validate = $this->verifyPrimaryIndex($fields, $arg, $table->data);
        $nextIndex = $this->getNextIndexTable($fields, $table->data);
        
        if($validate == true){
             $this->saveLog(0.2, "Duplicate primary key or unique");
        }else{
            
            $auxData = new stdClass();
            if(isset($fields->{$nextIndex["key"]}->{"autoincrement"}) && $fields->{$nextIndex["key"]}->{"autoincrement"} == 1  && !isset($arg[$nextIndex["key"]])){
                $auxData->{$nextIndex["key"]} = $nextIndex["index"];
                $table->autoincrement = $nextIndex["index"];
            }
            
            foreach ($fields as $key => $value) {
                if(isset($arg[$key])){
                    $auxData->{$key} = $arg[$key];
                }
            }
            
           
            
        }
         
        if(isset($auxData)){
            
            $index = $this->getIndexSystem($table->data);
            $table->data->{$index} = $auxData;
            
            $this->makeFile(array(
                    "name"=>"{$this->zdbname}.{$table->name}.data",
                    "content"=>$table->data,
             ));
            
            $this->saveLog(1, "Successful insert","insert:");
            
        }else{
             $this->saveLog(0.3, "Could not insert any value","insert:");             
        }
        
        
        return $this;
    }
    
    public function select($arg=array()){
        $db = $this->zdatabase;
		$data = array();
		
        //$fields = $db->tables->{$this->ztablename}->fields;
		if(isset($db->tables->{$this->ztablename}->data)){
			$data = $db->tables->{$this->ztablename}->data;
		}
		
        $response = array();
        $numResults = 0;
        $numArgms = count($arg);
        
        if($numArgms>0){
            foreach ($data as $key => $value) {
                $i=0;
                foreach ($arg as $keya => $valuea) {
                    
                    if($numArgms>1 && $value->{$keya} == $valuea){
                        $i++;
                        if($i == $numArgms){
                            array_push($response,$value);
                        }
                    }else{
                        if($numArgms==1 && $value->{$keya} == $valuea){
                            array_push($response,$value);
                        }
                    }
                }

            }
            
            $numResults = count($response);
            if($numResults<=0){
                $this->saveLog(0.4, "No results found","select:");      
            }else{
                 $this->saveLog(1, $numResults." results found","select:");
            }
        }else{
            $response = $data;
        }
        
        return $response;
    }
    
    public function update($arg=array()){
        
        $table = $this->getTable($this->ztablename);
        $fields = $table->fields;
        
        $data = $table->data;
        
        $response = array();
        $numResults = 0;
        $numArgms = count($arg["select"]);
        
      
        
        if($numArgms>0){
            foreach ($data as $key => $value) {
                $i=0;
                foreach ($arg["select"] as $keya => $valuea) {
                    
                    if($numArgms>1 && $value->{$keya} == $valuea){
                        $i++;
                        if($i == $numArgms){
                            array_push($response,$value);
                            
                            foreach ($arg["update"] as $keyb => $valueb) {
                                if((isset($fields->{$keyb}->index) && $fields->{$keyb}->index != "primary" || $fields->{$keyb}->index != "unique") || !isset($fields->{$keyb}->index)){
                                    $db->tables->{$this->ztablename}->data->{$key}->{$keyb} = $valueb;
                                }else{
                                    $this->saveLog(0.5, "You can not update a primary or unique key","update:");
                                }
                            }
                            
                        }
                    }else{
                        if($numArgms==1 && $value->{$keya} == $valuea){
                            array_push($response,$value);
                            
                            foreach ($arg["update"] as $keyb => $valueb) {
                                
                                if(isset($fields->{$keyb}->index)){
                                    
                                    if($fields->{$keyb}->index != "primary" && $fields->{$keyb}->index != "unique"){
                                        $db->tables->{$this->ztablename}->data->{$key}->{$keyb} = $valueb;
                                    }else{
                                        $this->saveLog(0.5, "You can not update a primary or unique key","update:");
                                    }
                                    
                                }else{
                                    $db->tables->{$this->ztablename}->data->{$key}->{$keyb} = $valueb;
                                }
                                
                            }
                            
                        }
                    }
                }
                
                

            }
            $numResults = count($response);
            if($numResults<=0){
                $this->saveLog(0.4, "No record(s) updated","update:");      
            }else{
                 $this->saveLog(1, $numResults." record(s) updated","update:");
            }
        }else{
            $response = $data;
        }
        
        $this->makeFile(array(
                "name"=>"{$this->zdbname}.{$this->ztablename}.data",
                "content"=>$table->data,
         ));
        
        return $response;
    }
    
    public function delete($arg=array()){
        
        $db = $this->zdatabase;
        $fields = $db->tables->{$this->ztablename}->fields;
        $data = $db->tables->{$this->ztablename}->data;
        $response = array();
        $numResults = 0;
        $numArgms = count($arg);
        
        if($numArgms>0){
            foreach ($data as $key => $value) {
                $i=0;
                foreach ($arg as $keya => $valuea) {
                    
                    if($value->{$keya} == $valuea){
                            foreach ($arg as $keyb => $valueb) {
                                array_push($response,$value);
                                unset($db->tables->{$this->ztablename}->data->{$key});
                            }
                            
                        }
                }

            }
            
            
            
            $numResults = count($response);
            if($numResults<=0){
                $this->saveLog(0.4, "No record(s) deleted","delete:");      
            }else{
                $this->saveLog(1, $numResults." Record(s) deleted","delete:");
            }
        }else{
            $response = $data;
        }
        
        $this->makeFile(array(
                "name"=>$this->zdatabase->name,
                "content"=>$db,
         ));
        
        return $response;
    }
    
}