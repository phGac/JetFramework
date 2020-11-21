<?php


class CurrencyService implements \Jet\Service\Service
{
    private $baseUrl;
    private $actual;

    function __construct($url)
    {
        $this->baseUrl = $url;
        $this->actual = null;
    }

    /**
     * @inheritDoc
     */
    function onCreate()
    {
        $this->actual = $this->request($this->baseUrl);
    }

    /**
     * @inheritDoc
     */
    function onCall()
    {
        // TODO: Implement onCall() method.
    }

    /**
     * @param $url
     * @return bool|string
     */
    private function request($url)
    {
        $json = null;
        if ( ini_get('allow_url_fopen') ) {
            $json = file_get_contents($url);
        } else {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($curl);
            curl_close($curl);
        }

        return json_decode($json);
    }

    function getActual()
    {
        $availables = [];
        foreach ($this->actual as $name => $value) {
            switch($name) {
                case 'version':
                case 'autor':
                case 'fecha':
                    break;
                default:
                    $availables[$name] = $value;
            }
        }

        return $availables;
    }

    /**
     * @return string[]
     */
    function getAvailablesCurrencies()
    {
        $availables = [];
        foreach ($this->actual as $name => $value) {
            switch($name) {
                case 'version':
                case 'autor':
                case 'fecha':
                    break;
                default:
                    $availables[] = $name;
            }
        }

        return $availables;
    }

    /**
     * @param string $name
     * @return array|false
     */
    function getActualCurrency($name)
    {
        return (isset($this->actual[$name])) ? $this->actual[$name] : false;
    }

    /**
     * @param string $name
     * @return bool|string
     */
    function getLastMonthCurrency($name)
    {
        if(! isset($this->actual[$name])) return false;
        return $this->request($this->baseUrl . '/' . $name);
    }

    /**
     * @param string $name
     * @param int $year
     * @return bool|string
     */
    function getYearCurrency($name, $year)
    {
        if(! isset($this->actual[$name])) return false;
        return $this->request($this->baseUrl . '/' . $name . '/' . $year);
    }
}