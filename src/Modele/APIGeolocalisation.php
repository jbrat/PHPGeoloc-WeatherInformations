<?php

namespace geoMarketing\Modele;

use Curl\Curl;
use geoMarketing\Exception\ArgumentLocationException;
use geoMarketing\Exception\APIErrorException;
use geoMarketing\Exception\APICountryISOException;
use geoMarketing\Exception\InvalidIPArgumentException;
use geoMarketing\Exception\APIReverseGeocodingException;

/**
 * Class to interact with differents API for geolocalisation
 */
class APIGeolocalisation 
{   
    private $clientIP;
    
    private $latitude;
    
    private $longitude;
    
    private $apiGeolocalisationIP = "http://ipinfo.io/";
   
    private $apiReverseGeocoding = "http://nominatim.openstreetmap.org/reverse?format=json&";
    
    private $countryAPI = "https://restcountries.eu/rest/v1/alpha?codes";

    
    public function getClientIP() 
    {
        return $this->clientIP;
    }

    public function getLatitude() 
    {
        return $this->latitude;
    }

    public function getLongitude() 
    {
        return $this->longitude;
    }
    
    public function getApiGeolocalisationIP() {
        return $this->apiGeolocalisationIP;
    }

    public function getApiReverseGeocoding() {
        return $this->apiReverseGeocoding;
    }

    public function getCountryAPI() {
        return $this->countryAPI;
    }

    public function setApiGeolocalisationIP($apiGeolocalisationIP) {
        $this->apiGeolocalisationIP = $apiGeolocalisationIP;
    }

    public function setApiReverseGeocoding($apiReverseGeocoding) {
        $this->apiReverseGeocoding = $apiReverseGeocoding;
    }

    public function setCountryAPI($countryAPI) {
        $this->countryAPI = $countryAPI;
    }

    /**
     * Method to set the client IP
     * 
     * @param string $clientIP
     * 
     * @throws InvalidIPArgumentException
     */
    public function setClientIP($clientIP) 
    {
        if (filter_var($clientIP, FILTER_VALIDATE_IP)) {
            $this->clientIP = $clientIP;
        } else {
            throw new InvalidIPArgumentException("You need to give a valid IP address");
        }
    }
    
    /**
     * Method to set the Latitude of the client
     * 
     * @param type $latitude
     * 
     * @throws \InvalidArgumentException
     */
    public function setLatitude($latitude) 
    {
        if(empty($latitude)) {
            throw new \InvalidArgumentException("You need to set a valid latitude");
        }
        $this->latitude = $latitude;
    }

    /**
     * Method to set the longitude of the client
     * 
     * @param type $longitude
     * 
     * @throws \InvalidArgumentException
     */
    public function setLongitude($longitude) 
    {
        if(empty($longitude)) {
            throw new \InvalidArgumentException("You need to set a valid longitude");
        }
        $this->longitude = $longitude;
    }
   
    /**
     * Method to get location informations with a Latitude & Longitude
     * 
     * @return Array location information
     * 
     * @throws APIReverseGeocodingException
     */
    private function getGeolocWithLatLng() 
    {
        $curl = new Curl();
        $url = $this->apiReverseGeocoding.'lat='.$this->latitude.'&lon='.$this->longitude;
        
        $curlResult = $curl->get($url);
        
        if($curl->httpStatusCode != 200) {
            throw new APIReverseGeocodingException("The API gmap can't be request for the moment, url :". $url);
        }
        
        if(!empty($curlResult->error_message)) {
            throw new APIReverseGeocodingException($curlResult->error_message);
        }
        
        return array(
            'country'   => $curlResult->address->country,
            'city'      => $curlResult->address->city,
            'postcode'  => explode(';', $curlResult->address->postcode)[0],
            'region'    => $curlResult->address->state,
            'lat'       => $this->latitude,
            'lng'       => $this->longitude
        );
    }
    
    /**
     * Method to get location informations with an IP
     * 
     * @return Array location informations
     * 
     * @throws APIErrorException
     */
    private function getGeolocWithIP()
    {
        $curl = new Curl;
        $curlResult = $curl->get($this->apiGeolocalisationIP . $this->clientIP);
        
        if($curl->httpStatusCode != 200) {
            throw new APIErrorException("The API is not accessible actually with the request : " . $this->apiGeolocalisation . $this->clientIP);
        }   
        
        $countryName = $this->getCountryNameWithISO($curlResult->country);
        
        $latitude = explode(",", $curlResult->loc)[0];
        $longitude = explode(",", $curlResult->loc)[1];
        
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
        $resultPostCode = $this->getGeolocWithLatLng()['postcode'];
        
        return array(
            "city"      => $curlResult->city,
            "country"   => $countryName,
            "region"    => $curlResult->region,
            "lat"       => $latitude,
            "lng"       => $longitude,
            "postcode"  => $resultPostCode
        );
    }
    
    /**
     * Method to get a countryName with an ISO code
     * 
     * @param type $isoCountry
     * 
     * @return string country Name
     * 
     * @throws APICountryISOException
     */
    public function getCountryNameWithISO($isoCountry)
    {

        $curl = new Curl;
        $curlResult = $curl->get($this->countryAPI . "=" . $isoCountry);
      
        if($curl->httpStatusCode != 200) {
            throw new APICountryISOException("The connection with the country API failed");
        }
        
        return $curlResult[0]->name;  
    }
    
    /**
     * Method to return location information with latitude/longitude or IP client
     * 
     * @return Array location informations
     * 
     * @throws ArgumentLocationException
     */
    public function getLocalisation() {  
        
        if(!empty($this->latitude) && !empty($this->longitude)) {
            $location = $this->getGeolocWithLatLng();
        } elseif(!empty($this->clientIP)) {
            $location = $this->getGeolocWithIP();
        } else {
            throw new ArgumentLocationException("You need to set the longitude and latitude or the clientIP");
        }
        
        return $location;
    }
    
   
}
