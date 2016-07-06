<?php

namespace geoMarketing\Modele;

use Curl\Curl;
use geoMarketing\Exception\APIWeatherException;

/**
 * Class to get informations about weather
 */
class APIWeather 
{
    private $apiWeatherLink = "http://api.openweathermap.org/data/2.5/weather?";
    
    private $apiWeatherKey;
    
    private $latitude;
    
    private $longitude;
    
    private $searchCity;
    
    
    public function getApiWeatherLink() {
        return $this->apiWeatherLink;
    }

    public function getApiWeatherKey() {
        return $this->apiWeatherKey;
    }

    public function setApiWeatherLink($apiWeatherLink) {
        $this->apiWeatherLink = $apiWeatherLink;
    }

    public function setApiWeatherKey($apiWeatherKey) {
        $this->apiWeatherKey = $apiWeatherKey;
    }
    
    function getLatitude() {
        return $this->latitude;
    }

    function getLongitude() {
        return $this->longitude;
    }

    function getSearchCity() {
        return $this->searchCity;
    }

    /**
     * Method to set the latitude
     * 
     * @param type $latitude
     * 
     * @throws InvalidArgumentException
     */
    function setLatitude($latitude) {
        
        if(empty($latitude)) {
            throw \InvalidArgumentException("You need to set a correct latitude");
        }
        $this->latitude = $latitude;
    }

    /**
     * Method to set the longitude
     * 
     * @param type $longitude
     * 
     * @throws \InvalidArgumentException
     */
    function setLongitude($longitude) {
        
        if(empty($longitude)) {
            throw new \InvalidArgumentException("You need to set a correct longitude");
        }
        $this->longitude = $longitude;
    }

    /**
     * Method to set the search city
     * 
     * @param type $searchCity
     * 
     * @throws \InvalidArgumentException
     */
    function setSearchCity($searchCity) {
        
        if(empty($searchCity)) {
            throw new \InvalidArgumentException("You need to set a valid city for search");
        }
        $this->searchCity = $searchCity;
    }

    /**
     * Method to get information about weather from a latitude & longitude

     * @return Array informations weather
     * humidity     => %
     * pressure     => hpa
     * windSpeed    => m/s 
     * temp         => °C
     *
     * @throws APIWeatherException
     */
    public function getWeatherInfo()
    {
        if(!empty($this->latitude) && !empty($this->longitude)) {
            $url = $this->apiWeatherLink . "&lat=" . $this->latitude . "&lon=" . $this->longitude . "&appid=" . $this->apiWeatherKey;
        }
        
        if(!empty($this->searchCity)) {
            $url = $this->apiWeatherLink . "&q=" . $this->searchCity . "&appid=" . $this->apiWeatherKey;
        }
        
        $curl = new Curl();
        $curlResult = $curl->get($url);
        
        if($curl->httpStatusCode != 200) {
            if($curl->httpStatusCode == 401) {
                throw new APIWeatherException(!empty($curlResult->message) ? $curlResult->message : "API Key isn't valid");
            }
            throw new APIWeatherException("An error occured when attempted to request the weather webservice");
        }

        return array(
            'humidity'      => $curlResult->main->humidity,
            'pressure'      => $curlResult->main->pressure,
            'weather'       => $curlResult->weather[0]->main,
            'description'   => $curlResult->weather[0]->description,
            'windSpeed'     => $curlResult->wind->speed,
            'tempActual'    => ($curlResult->main->temp - 273.15),
            'temp_max'      => ($curlResult->main->temp_max - 273.15),
            'temp_min'      => ($curlResult->main->temp_min - 273.15)
        );
    }
}
