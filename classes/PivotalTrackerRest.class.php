<?php
/**
 * Pivotal Tracker Rest (PHP)
 * 
 * This fork:
 * @author David Christian Liedle <david.liedle@gmail.com>
 * 
 * Based on Original:
 * @author Jeremy Blanchard <auzigog@gmail.com>
 * 
 * @license http://opensource.org/licenses/MIT MIT License
 * 
 * xml2array function in this file by Binny V A <binnyva@gmail.com>
 * @link http://www.bin-co.com/php/scripts/xml2array/
 * 
 */

class PivotalTrackerREST implements iPivotalTrackerRest {
    
    // Public properties
    public $base_url  = '';
    public $force_ssl = false;
    public $token = null;
    
        // Protected properties
        protected $username = '';
        protected $password = '';
        
    /**
     * Constructor:
     * 
     * @param type $token
     */
    public function __construct( $token = null ){
        
        $this->base_url = 'https://www.pivotaltracker.com/services/v3/';
        
        if( !empty($token) ){
            
            $this->token = $token;
            
        } // End of if( !empty($token) )

    } // End of public function __construct( $token = null )
    
    /**
     * Check if authenticated
     * 
     * @return type
     */
    public function is_authenticated(){
        
        return !empty($this->token);
        
    } // End of public function is_authenticated()

    /**
     * Helper function to quickly authenticate
     * 
     * @return type
     */
    public function authenticate(){
        
        if( !$this->is_authenticated() ){
            
            $token_arr = $this->tokens_active($this->username, $this->password);
            
            $this->_store_authentication($token_arr);
            
        } // End of if( !$this->is_authenticated() )

        return $this->token;
        
    } // End of public function authenticate()

    /**
     * Tokens Active
     * 
     * @todo Should really be in the other class, but it's needed for the authentication method
     * 
     * @param type $username
     * @param type $password
     * 
     * @return type
     */
    public function tokens_active( $username, $password ){
        
        $auth = ['username' => $username
                ,'password' => $password];
        
        $function = 'tokens/active';
        $token_arr = $this->_execute($function, null, 'GET', $auth);
        
        return $token_arr;
        
    } // End of public function tokens_active( $username, $password )
    
    /*
     * @todo MARK - not sure if the methods below were intended to be protected.
     * It appears that they were and should be, and therefore have been set to
     * protected. After I come back to test, I'll confirm and remove this comment.
     */
    
        /**
         * 
         * @param type $token_arr
         */
        protected function _store_authentication( $token_arr ){
            
            $this->token = $token_arr['token']['guid'];
            $this->user_id = $token_arr['token']['id'];
                
        } // End of protected function _store_authentication( $token_arr )
        
        /**
         * Execute
         * 
         * @param type $function
         * @param type $vars
         * @param type $method
         * @param type $auth
         * 
         * @return type
         */
        public function _execute( $function, $vars=null, $method='GET', $auth=null ){
            
            $xml = $this->_curl($function, $vars, $method, $auth);
            
            $arr = $this->_process_xml($xml);
            
            return $arr;
            
        } // End of protected function _execute( $function, $vars=null, $method='GET', $auth=null )
        
        /**
         * Process XML
         * 
         * @param type $xml
         * @param type $flattenValues
         * @param type $flattenAttributes
         * @param type $flattenChildren
         * 
         * @return type
         */
        protected function _process_xml($xml
                                       ,$flattenValues     = true
                                       ,$flattenAttributes = true
                                       ,$flattenChildren   = false
                                       ){
            
            $result_arr = $this->xml2array($xml, 0);
            //print_rr(htmlentities($xml));
            //print_rr($result_arr);
            
            return $result_arr;

        } // End of protected function _process_xml( $xml, $flattenValues = true, $flattenAttributes = true, $flattenChildren = false )
        
        /**
         * Get
         * 
         * @param type $url
         * @param type $vars
         */
        protected function _get( $url, $vars = null ){
            
            $this->_curl($url, $vars, 'GET');
            
        } // End of protected function _get( $url, $vars = null )
        
        /**
         * POST
         * 
         * @param type $url
         * @param type $vars
         */
        protected function _post( $url, $vars ){
            
            $this->_curl($url, $vars, 'POST');
            
        } // End of protected function _post( $url, $vars )
        
        /**
         * PUT
         * 
         * @param type $url
         * @param type $vars
         */
        protected function _put( $url, $vars ){
            
            $this->_curl($url, $vars, 'PUT');
            
        } // End of protected function _put( $url, $vars )
        
        /**
         * DELETE
         * 
         * @param type $url
         * @param type $vars
         */
        protected function _delete( $url, $vars ){
            
            $this->_curl($url, $vars, 'DELETE');
            
        } // End of protected function _delete( $url, $vars )
        
        
        /*
        $function String the end of the URL for the function call. Example: 'tokens/active'
        $vars Array associate array for post data
        $method String HTTP method
        $auth Array associative array for username and password
         */
        
        /**
         * CURL
         * 
         * @param type   $function
         * @param type   $vars
         * @param string $method   Default is GET
         * @param type   $auth
         * 
         * @return type
         */
        protected function _curl( $function, $vars = null, $method = 'GET', $auth = null ){
            
            // @todo CLEANUP THIS METHOD
            // 
// Construct the full URL
$url = $this->base_url.$function;


$url = str_replace( "&amp;", "&", urldecode(trim($url)) );


$fields = (is_array($vars)) ? http_build_query($vars) : $vars; 

$ch = curl_init($url);

// Follow redirects
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 

//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Set the request type
switch ($method) {
case 'GET':
curl_setopt($ch, CURLOPT_HTTPGET, 1);
break;
case 'POST':
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields)));
break;
case 'PUT':	
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields))); 
break;
case 'DELETE':
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields)));
break;
default:
//TODO
break;
}


// Add the Pivotal Tracker token

if(!empty($this->token)) {
curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-TrackerToken: ' . $this->token));
}



// add user authentication if necessary
$do_auth = !empty($auth) && is_array($auth) && !empty($auth['username']) && !empty($auth['password']);
if($do_auth) {
curl_setopt($ch, CURLOPT_USERPWD, $auth['username'].':'.$auth['password']);
}

// force ssl if necessary
// TODO: Maybe it should alway suse SSL??
if($this->force_ssl || $do_auth) {
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANYSAFE);
} else {
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
}

// Warning: This will blindly accept any certificate (even self signed ones) and is essentially unsecure
// TODO: Do real authentication. http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


// Debug stuff
//$this->_rest_debug($ch);


// $output contains the output string
$output = curl_exec($ch);
//print_rr($output);

// $response contains the response HTTP headers
$response = curl_getinfo($ch);
//print_rr($response);

// close curl resource to free up system resources
if (curl_errno($ch))
return curl_error($ch);
else
curl_close($ch);

            return $output;

        } // End of protected function _curl( $function, $vars = null, $method = 'GET', $auth = null )
        
        /**
         * REST Debug
         * 
         * @param type $ch
         */
        protected function _rest_debug( $ch ){
            
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            
            $response_data = curl_exec($ch);
            
            header('Content-Type: text/html');
            print_rr('response_data:');
            print_rr($response_data); // Request header
            print_rr('all curl info:');
            print_rr(curl_getinfo($ch)); // Response header
            
            die();
            
        } // End of protected function _rest_debug( $ch )
        
    
    /**
     * XML to Array
     * 
     * @param type $contents
     * @param type $get_attributes
     * @param type $priority
     * 
     * @return type
     */
    public function xml2array( $contents, $get_attributes = 1, $priority = 'tag'){
        
        /*
         * @todo CLEANUP THIS METHOD (xml2array)
         */
        
        /**
         * xml2array() will convert the given XML text to an array in the XML structure.
         * Link: http://www.bin-co.com/php/scripts/xml2array/
         * Arguments : $contents - The XML text
         *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
         *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
         * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
         * Examples: $array =  xml2array(file_get_contents('feed.xml'));
         *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
         */
        
        if(!$contents) return array();

        if(!function_exists('xml_parser_create')) {
            //print "'xml_parser_create()' function not found!";
            return array();
        }

        //Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);

        if(!$xml_values) return;//Hmm...

        //Initializations
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();

        $current = &$xml_array; //Refference

        //Go through the tags.
        $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
        foreach($xml_values as $data) {
            unset($attributes,$value);//Remove existing values, or there will be trouble

            //This command will extract these variables into the foreach scope
            // tag(string), type(string), level(int), attributes(array).
            extract($data);//We could use the array by itself, but this cooler.

            $result = array();
            $attributes_data = array();

            if(isset($value)) {
                if($priority == 'tag') $result = $value;
                else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
            }

            //Set the attributes too.
            if(isset($attributes) and $get_attributes) {
                foreach($attributes as $attr => $val) {
                    if($priority == 'tag') $attributes_data[$attr] = $val;
                    else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }

            //See tag status and do the needed.
            if($type == "open") {//The starting of the tag '<tag>'
                $parent[$level-1] = &$current;
                if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                    $current[$tag] = $result;
                    if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag.'_'.$level] = 1;

                    $current = &$current[$tag];

                } else { //There was another element with the same tag name

                    if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                        $repeated_tag_index[$tag.'_'.$level]++;
                    } else {//This section will make the value an array if multiple tags with the same name appear together
                        $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                        $repeated_tag_index[$tag.'_'.$level] = 2;

                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }

                    }
                    $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                    $current = &$current[$tag][$last_item_index];
                }

            } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
                //See if the key is already taken.
                if(!isset($current[$tag])) { //New Key
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

                } else { //If taken, put all things inside a list(array)
                    if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                        // ...push the new element into that array.
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

                        if($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag.'_'.$level]++;

                    } else { //If it is not an array...
                        $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        if($priority == 'tag' and $get_attributes) {
                            if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well

                                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                unset($current[$tag.'_attr']);
                            }

                            if($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                    }
                }

            } elseif($type == 'close') { //End of tag '</tag>'
                $current = &$parent[$level-1];
            }
        }

        return($xml_array);
        
    } // End of public function xml2array( $contents, $get_attributes = 1, $priority = 'tag')

    
} // End of class PivotalTrackerREST implements iPivotalTrackerRest
