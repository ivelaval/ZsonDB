<?php

class Core{
    
    public function makeFile($arg=array()){
        $name = (isset($arg["name"]) && !empty($arg["name"]))?$arg["name"]:"";
        $path = (isset($arg["path"]) && !empty($arg["path"]))?$arg["path"]:$this->_pathStorage;
        $content = (isset($arg["content"]) && !empty($arg["content"]))?$arg["content"]:""; 
        
        $fp = fopen($path.$name.'.zson','w+');
        fwrite($fp,  $this->encryptIt(json_encode($content)));
        fclose($fp);
        
        $this->_zdatabses = $this->readFile(array(
            "db"=>$name
        ));
        
        return $this->_zdatabses;

    }
    
    public function readFile($arg=array()){
        
        $content = "";
        $db = (isset($arg["db"]) && !empty($arg["db"]))?$arg["db"]:"";
        
        echo $db;
        
        $file = fopen($this->_pathStorage.$db.".zson", "r") or exit("Unable to open file!");
        while(!feof($file)){
            $content .= fgets($file);
        }
        fclose($file);
        $content = $this->decryptIt($content);
        return json_decode($content);
    }
    
    public function compress($file, $filec){
        $fp = fopen($file, "r");
        $data = fread ($fp, filesize($file));
        fclose($fp); $zp = gzopen($filec, "w9");
        gzwrite($zp, $data);
        gzclose($zp);
    }
    
    public function decompress($file, $filec){
        $string = implode("", gzfile($file));
        $fp = fopen($filec, "w");
        fwrite($fp, $string, strlen($string));
        fclose($fp);
    }
    
    public function encryptIt($q) {
        $cryptKey  = $this->getMatchKeys();
        $qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
        return($qEncoded);
    }

    public function decryptIt($q) {
        $cryptKey  = $this->getMatchKeys();
        $qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
        return ($qDecoded);
    }
    
    public function verifyTableExist($data,$match){
        $res = false;
        if(isset($data->{$match}) && $res == false){
                $res = true;
        }
        return $res;
    }
    
    public function verifyFieldExist($data,$match){
        $res = false;
        foreach ($data as $value) {
            if($value == $match && $res == false){
                $res = true;
            }
        }
        return $res;
    }
    
    public function verifyPrimaryIndex($fieldsa, $arga, $dataa){
        $res = false;

        
        foreach ($fieldsa as $key => $value) {
                if(isset($value->{"index"}) && ($value->{"index"} || $value->{"unique"})){
                    
                    $res = $this->verifyValueMatch($dataa, $arga, $key);
                   
                }
        }
        
        return $res;
    }
    
    public function verifyValueMatch($data, $arg, $index){
        $res = false;
        foreach ($data as $keye => $value) {

            if((isset($value->{$index}) && isset($arg[$index])) && $value->{$index} == $arg[$index]){
                $res = true;
            }
        }
        return $res;
    }
    
    public function getIndexSystem($data){
        $index = 0;
        
        if(!empty($data)){
            foreach ($data as $key => $value) {
                $index = $key;
                $index++;
            }
        }
        return $index;
    }
    
    public function getNextIndexTable($fields, $data){
        $index = 0;
        $keySearch = "";
        foreach ($fields as $key => $value) {            
            if(isset($value->{"autoincrement"}) && $value->{"autoincrement"}==1){
                $keySearch = $key;
            }
        }
        
        foreach ($data as $key => $value) {
            if(isset($value->{$keySearch}) && !empty($value->{$keySearch})){
                $index = (intval($value->{$keySearch}) > $index)?intval($value->{$keySearch}):$index;
            }
        }
        
        $index++;
        
        return array("key"=>$keySearch,"index"=>$index);
    }
    
    public function saveLog($status,$msg="",$context){
        array_push($this->_debug, array(
                    "status"    => $status,
                    "msg"       => $msg,
                    "context"   => $context
                    ));
    }
    
    private function generateKey(){
        
        $cadenaServer = $_SERVER["SERVER_ADDR"]."::".$_SERVER["HTTP_HOST"]."::".$_SERVER["SERVER_ADMIN"];
        $escapedPW = md5("PuPz:=mbcwH6N@qB|P*4");
        $salt = crypt('ceratosystems', '$2y$10$'.$escapedPW.'$');
        $saltedPW =  $salt."::".$cadenaServer;

        $hashedPW = hash('sha256', $saltedPW);
        $key = $hashedPW;
        
        return $key;
    }
    
    private function generateKeyEnterprise(){
        
        $escapedPW = md5("PuPz:=mbcwH6N@qB|P*4");
        $salt = crypt('ceratosystems', '$2y$10$'.$escapedPW.'$');

        $saltedPW =  $escapedPW."::".$salt;
        $hashedPW = hash('sha256', $saltedPW);
        $key = $hashedPW;
        
        return $key;
    }
    
    private function getKeyFile(){
        
        $key = "";
        $keyEnterprise = "";
        
        $file1 = fopen("security/initialhash.key", "r") or exit("Unable to open file!");
        while(!feof($file1)){
            $key .= fgets($file1);
        }
        fclose($file1);
        
        $file2 = fopen("security/finalhash.key", "r") or exit("Unable to open file!");
        while(!feof($file2)){
            $keyEnterprise .= fgets($file2);
        }
        fclose($file2);
        
        return $key."::".$keyEnterprise;
    }
    
    private function generaPass(){
	$cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
	$longitudCadena=strlen($cadena);
	$pass = "";
	$longitudPass=10;
	for($i=1 ; $i<=$longitudPass ; $i++){
		$pos=rand(0,$longitudCadena-1);

		$pass .= substr($cadena,$pos,1);
	}
	return $pass;
    }


    private function getMatchKeys(){
        $response = "";
        
        
        
        $key = $this->generateKey()."::".$this->generateKeyEnterprise();
        if($this->getKeyFile() == $key){
            $response = $key;
        }else{
            $response = $this->generaPass();
        }
        
        echo "<pre>";
        print_r($key);
        echo "</pre>";
        
         echo "<pre>";
        print_r($this->getKeyFile());
        echo "</pre>";
        
        return $response;
    }
    
}