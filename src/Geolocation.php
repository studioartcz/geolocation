<?php

namespace StudioArtCz;

use Nette\Http\Response;
use Nette\Http\Request;

class GeoLocation
{
    private $response;
    private $request;
    private $cookies;
    private $lat;
    private $lng;
    private $time;

    public function __construct(Response $response, Request $request)
    {
        $this->response = $response;
        $this->request  = $request;
        $this->cookies  = ['geo_lat', 'geo_lng'];
    }

    /**
     * @param $array
     */
    public function setCookiesNames($array)
    {
        $this->cookies = $array;
    }

    /**
     * @param $value
     */
    public function setLat($value)
    {
        $this->lat = $value;
    }

    /**
     * @param $value
     */
    public function setLng($value)
    {
        $this->lng = $value;
    }

    /**
     * Get status
     * @return bool
     */
    public function isActive()
    {
        return  $this->request->getCookie($this->cookies[0]) &&
                $this->request->getCookie($this->cookies[1]);
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Save cords to cookies
     * @param null $lat
     * @param null $lng
     * @param string $time in DateTime format (for example: + 3 days)
     */
    public function setGeoLocation($lat = null, $lng = null, $time = null)
    {
        $this->lat = $lat;
        $this->lng = $lng;

        if($time)
        {
            $this->setTime($time);
        }
        if($this->lat)
        {
            $this->response->setCookie($this->cookies[0], $this->lat, new \DateTime($this->time));
        }
        if($this->lng)
        {
            $this->response->setCookie($this->cookies[1], $this->lng, new \DateTime($this->time));
        }
    }

    /**
     * Get array with cords
     * @return array|null
     */
    public function getLocation()
    {
        if($this->isActive())
        {
            return [
                "lat" => $this->request->getCookie($this->cookies[0]),
                "lng" => $this->request->getCookie($this->cookies[1])
            ];
        }

        return null;
    }

    /**
     * Calc distance between 2 points via gps cords
     * @param $lat1 string
     * @param $lng1 string
     * @param null $lat2
     * @param null $lng2
     * @return float
     */
    public function distanceBetweenTwoPoints($lat1, $lng1, $lat2 = null, $lng2 = null)
    {
        if(null == $lat2 && null == $lng2)
        {
            $cords = $this->getLocation();
            $lat2  = $cords["lat"];
            $lng2  = $cords["lng"];
        }

        $degrees    = rad2deg(acos((sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng1-$lng2)))));
        $distance   = $degrees * 111.13384;

        return $distance;
    }

    /**
     * @param $a object
     * @param $b object
     * @return bool
     */
    public function compareTwoPoints($a, $b)
    {
        $distanceA = $this->distanceBetweenTwoPoints($a->latitude, $a->longitude);
        $distanceB = $this->distanceBetweenTwoPoints($b->latitude, $b->longitude);

        return $distanceA >= $distanceB;
    }

    /**
     * @param $a object
     * @param $b object
     * @return bool
     */
    public function comparePointsRelativeToThePoint($a, $b)
    {
        $distanceA = $this->distanceBetweenTwoPoints($a->latitude, $a->longitude, $this->lat, $this->lng);
        $distanceB = $this->distanceBetweenTwoPoints($b->latitude, $b->longitude, $this->lat, $this->lng);
        return  $distanceA >= $distanceB;
    }

}
