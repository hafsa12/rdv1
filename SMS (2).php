<?php
/**
 * Created by PhpStorm.
 * User: oji
 * Date: 05/10/2016
 * Time: 10:26
 */


class Atexo_SMS {

    public $login;
    public $pass;
    public $destination;
    public $message;
    public $shortcode;
    public $url;
    public $codeClient;
    public $dcs="0";


    function curl_get_contents($url,$data) {

//if($this->login && $this->pass){
	$ch=curl_init();
 	curl_setopt($ch,CURLOPT_URL,$url);
 	curl_setopt($ch,CURLOPT_HEADER,0);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
 	curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
 	curl_setopt($ch,CURLOPT_POST,1);
 	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
 	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($ch,CURLOPT_VERBOSE, true);
	//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        // Execute the curl session
         $output=curl_exec($ch);
	
        // Close the curl session
        curl_close($ch);
        // Return the output as a variable
        return $output;
  //  }
//else return null;
} 

  public function envoyerSms($modeSoap=false){

        if($this->url){
            $data['GSM']=self::getDestination();
	    $data['Body']=self::getMessage();
	    $data['Title']=self::getShortcode();	
            $res= $this->curl_get_contents($this->url,$data);

		$logger=Atexo_LoggerManager::getLogger("rdvLogInfo");
		$logger->info("Send SMS to ".$data["GSM"]." : ".$res);

	    return $res;

        }else{
            return null;
        }
    } 


/*    public function prepareUrl(){
        return $this->url.='?ndest='.$this->getDestination().'&message='.$this->getMessage().'&shortcode='.$this->getShortcode();
    }*/


    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return mixed
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param mixed $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination($destination)
    {
       /* if (0 === strpos($destination, '06')) {
            $destination = "212".substr($destination, 1);
        }
        $this->destination = str_replace("+","",$destination);*/
	$this->destination=$destination;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getShortcode()
    {
        return $this->shortcode;
    }

    /**
     * @param mixed $shortcode
     */
    public function setShortcode($shortcode)
    {
        $this->shortcode = $shortcode;
    }

    public function getUrl(){return $this->url;}
    public function setUrl($v){  $this->url =$v;}

    /**
     * @return string
     */
    public function getDcs()
    {
        return $this->dcs;
    }

    /**
     * @param string $dcs
     */
    public function setDcs($dcs)
    {
        $this->dcs = $dcs;
    }

    private function toUnicode($message) {
        mb_http_output('UCS-2');
        $message = mb_convert_encoding($message, 'UCS-2', 'UTF-8');
        return bin2hex($message);
    }

   public function getCodeClient(){
       return $this->codeClient;
    }

   public function setCodeClient($codeClient){
	$this->codeClient=$codeClient;
    }
}
