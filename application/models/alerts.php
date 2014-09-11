<?php 
class Alerts extends CI_Model{
	public function __construct(){
        $this->load->database();
    }

    public function Load_List($type, $country_id){
    	$this->db->select('id, email');
    	$this->db->from('alerts_list');
    	$this->db->where('type', $type);
        $this->db->where('countries_id', $country_id);
    	$query = $this->db->get();
    	if ($query->num_rows()>0){
    		return $query;
    	}else{
    		return false;
    	}
    }

    public function Save_Contact($type, $email, $country_id){
    	$exist = $this->Exist_Contact($type, $email, $country_id);

    	if (!$exist){
    		$res = array(
    			'email' => $email,
    			'type' => $type,
                'countries_id' => $country_id
    		);
    		$result = $this->db->insert('alerts_list', $res);
    		return $result;
    	}else{
    		return false;
    	}
    }

    public function Get_Token($post){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
        curl_setopt($curl, CURLOPT_POST, 5);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $info = curl_getinfo($curl);

        $result = curl_exec($curl);
        echo curl_error($curl);
        curl_close($curl);

        $response = json_decode($result);

        $accesstoken = $response->access_token;

        return $accesstoken;
    }

    public function Curl_Google($url){
        $curl = curl_init();
        $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
         
        curl_setopt($curl,CURLOPT_URL,$url);  
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE); 
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5); 
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); 
        curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE); 
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
         
        $contents = curl_exec($curl);
        curl_close($curl);
        return $contents;
    }

    public function Get_Google_Contacts($max_results, $accesstoken){
        $contacts = array();
        $url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results='.$max_results.'&oauth_token='.$accesstoken;
        $xmlresponse =  $this->Curl_Google($url);
        if((strlen(stristr($xmlresponse,'Authorization required'))>0) && (strlen(stristr($xmlresponse,'Error '))>0)){
            return false;
        }else{
            $xml =  new SimpleXMLElement($xmlresponse);
            $xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');
            $result = $xml->xpath('//gd:email');

            foreach ($result as $title) {
                $contacts[] = $title->attributes()->address;
            }

            return $contacts;
        }
    }

    public function Exist_Contact($type, $email, $country_id){
    	$this->db->select('email');
    	$this->db->from('alerts_list');
    	$this->db->where('type', $type);
    	$this->db->where('email', $email);
        $this->db->where('countries_id', $country_id);

    	$query = $this->db->get();
    	if ($query->num_rows()>0){
    		return true;
    	}else{
    		return false;
    	}
    }

    public function Delete_Contact($type, $email, $country_id){
    	$res = array(
    		'email' => $email,
    		'type' => $type,
            'countries_id' => $country_id
    	);

    	$result = $this->db->delete('alerts_list', $res);
    	return $result;
    }
}