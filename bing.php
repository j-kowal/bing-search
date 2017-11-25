<?php
class BingSearch
{
    //private properties
    private 
    $key = '#', //value of Custom Search API key
    $custom_conf_id = '#', //value of Custom Configuartion ID
    $limit, //limit of search results max.50 from outside
    $results_per_page, //number of results per page - this value is passed in one of parameter from outside
    $curl; // curl client

    //function counting how many search pages will be displayed.
    private function pages($size)
    {
        if ($size % $this->results_per_page == 0) // checking if modulo is equal 0, that means it's gonna be divisible number
            return (int) $size / $this->results_per_page;
        else
            return ((int) ($size / $this->results_per_page) + 1); // ex 5 results per page --- 11 results -- integer 11/5 = 2 but we need one more page to display 11th result. so +1
    }
    //query function
    public function query($query, $results_per_page, $limit)
    {
        //curl to BING search engine
        $this->limit = $limit;
        $this->results_per_page = $results_per_page;
        $this->curl = curl_init();
        curl_setopt_array($this->curl, array(
            CURLOPT_HTTPHEADER => array(
                "Ocp-Apim-Subscription-Key: $this->key",
                "Accept: application/json"
            ),
            CURLOPT_URL => "https://api.cognitive.microsoft.com/bingcustomsearch/v7.0/search?q=$query&customconfig=$this->custom_conf_id&responseFilter=Webpages&mkt=en-US&safesearch=Moderate&count=$this->limit",
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 1
        ));
        //checking if curl contains no errors
        if(!$response = curl_exec($this->curl)){
            exit('Error: "' . curl_error($this->curl) . '" - Code: ' . curl_errno($this->curl));
        }
        //closing curl
        curl_close($this->curl);
        
        //decoding response to array
        $json = json_decode($response, true);
     
        //checking if there is some results and rearranging array to be returned just with fewer necesssary values
        if(isset($json['webPages']['value']))
        {
            $temp = array(); // array with results to be stored in returning array
            $results = $json['webPages']['value'];
            $size = sizeof($results); //number or results
            $pages = $this->pages($size);
            //populating array with each result values
            foreach ($results as $x)
                array_push($temp, array('name' => $x['name'], 'url' => $x['url'], 'snippet' => $x['snippet'],
                'img' => isset($x['openGraphImage'])/*check if image is there*/ ? $x['openGraphImage']['contentUrl'] : null
            ));
            //packing everything to final array
            $array = array('search' => array('size' => $size, 'pages' => $pages, 'results' => $temp));
            //and return
            return $array;
        }
        return false; //if there is no results return false.
    }
}
?>
