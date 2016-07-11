<?php

namespace geoMarketing\Modele;

use Curl\Curl;
use geoMarketing\Exception\AutoCompleteCountryException;

/**
 * Autocomplete API for Country, Region.
 */
class APIAutocomplete
{
    private $apiCountrysURL = 'https://restcountries.eu/rest/v1/all';

    public function getApiCountrysURL()
    {
        return $this->apiCountrysURL;
    }

    public function setApiCountrysURL($apiCountrysURL)
    {
        $this->apiCountrysURL = $apiCountrysURL;
    }

    /**
     * Method to get all the countries for autocompletion.
     * 
     * @return array countries
     * 
     * @throws AutoCompleteCountryException
     */
    public function getAutocompleteCountrys()
    {
        $countrys = array();

        $curl = new Curl();
        $result = $curl->get($this->apiCountrysURL);

        if ($curl->httpStatusCode != 200) {
            throw new AutoCompleteCountryException("The API country aren't accessible for this moment");
        }

        foreach ($result as $country) {
            $countrys[] = trim($country->name);
        }

        return json_encode($countrys);
    }
}
