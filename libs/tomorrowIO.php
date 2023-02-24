<?php

   class TIO_ERROR_CODES
    {
        public static function ToString(int $errCode)
        {
            switch ($errCode) {  
                case 400: return 'Bad Request';
                case 400001: return 'Invalid Body Parameter';
                case 400002: return 'Invalid Query Parameters';
                case 400003: return 'Missing Required Body Parameters';
                case 400004: return 'Missing Required Query Parameters';
                case 400005: return 'Rule Violation';
                case 400006: return 'Missing Required Header Parameters';
                case 400007: return 'Invalid Path Parameters';
                case 401001: return 'Invalid API Key';
                case 403001: return 'Access Denied';
                case 403002: return 'Account Limit';
                case 403003: return 'Forbidden Action';
                case 404001: return 'Resource ID not found';
                case 500001: return 'Service downtime';
                case 503001: return 'Service not available';
                default: return $errCode . 'error';
            }
        }
    }



    class WeatherApi
    {
       

       
        const api_uri = "https://api.tomorrow.io/v4/timelines";


        public $params  = array(
            'apikey' => '',
            'location' => '631232f26730c50009585472',
            'fields' => '',
            'startTime' => 'now',
            'endTime' => 'nowPlus7d',
            'timesteps' => '1d',
            'units' => 'metric',
            'timezone' => 'Europe/Berlin',
        );

        public $fieldsNow = array(
            'temperature' => false,
            'temperatureApparent' => false,
            'dewPoint' => false,
            'humidity' => false,
            'windSpeed' => false,
            'windDirection' => false,
            'windGust' => false,
            'pressureSurfaceLevel' => false,
            'pressureSeaLevel' => false,
            'precipitationIntensity' => false,
            'rainIntensity' => false,
            'freezingRainIntensity' => false,
            'snowIntensity' => false,
            'sleetIntensity' => false,
            'precipitationProbability' => false,
            'precipitationType' => false,
            'rainAccumulation' => false,
            'snowAccumulation' => false,
            'snowAccumulationLwe' => false,
            'sleetAccumulation' => false,
            'sleetAccumulationLwe' => false,
            'iceAccumulation' => false,
            'iceAccumulationLwe' => false,
            'sunriseTime' => false,
            'sunsetTime' => false,
            'visibility' => false,
            'cloudCover' => false,
            'cloudBase' => false,
            'cloudCeiling' => false,
            'moonPhase' => false,
            'uvIndex' => false,
            'uvHealthConcern' => false,
            'weatherCodeFullDay' => false,
            'weatherCodeDay' => false,
            'weatherCodeNight' => false,
            'weatherCode' => false,
        );

        public $fieldsWeek = array(
            'temperature' => false,
            'temperatureApparent' => false,
            'dewPoint' => false,
            'humidity' => false,
            'windSpeed' => false,
            'windDirection' => false,
            'windGust' => false,
            'pressureSurfaceLevel' => true,
            'pressureSeaLevel' => false,
            'precipitationIntensity' => false,
            'rainIntensity' => false,
            'freezingRainIntensity' => false,
            'snowIntensity' => false,
            'sleetIntensity' => false,
            'precipitationProbability' => false,
            'precipitationType' => false,
            'rainAccumulation' => false,
            'snowAccumulation' => false,
            'snowAccumulationLwe' => false,
            'sleetAccumulation' => false,
            'sleetAccumulationLwe' => false,
            'iceAccumulation' => false,
            'iceAccumulationLwe' => false,
            'sunriseTime' => false,
            'sunsetTime' => false,
            'visibility' => false,
            'cloudCover' => false,
            'cloudBase' => false,
            'cloudCeiling' => false,
            'moonPhase' => false,
            'uvIndex' => false,
            'uvHealthConcern' => false,
            'weatherCodeFullDay' => false,
            'weatherCodeDay' => false,
            'weatherCodeNight' => false,
            'weatherCode' => false,
        );

        public $units = array(
            'temperature_unit' => '°C',
            'dewPoint_unit' => '°C',
            'humidity_unit' => '%',
            'windSpeed_unit' => 'm/s',
            'windDirection_unit' => 'Grd',
            'windGust_unit' => 'm/s',
            'pressureSurfaceLevel_unit' => 'hPa',
            'pressureSeaLevel_unit' => 'hPa',
            'precipitationIntensity_unit' => 'mm/hr',
            'rainIntensity_unit' => 'mm/hr',
            'freezingRainIntensity_unit' => 'mm/hr',
            'snowIntensity_unit' => 'mm/hr',
            'sleetIntensity_unit' => 'mm/hr',
            'precipitationProbability_unit' => '%',
            'precipitationType_unit' => '',
            'rainAccumulation_unit' => 'mm',
            'snowAccumulation_unit' => 'mm',
            'snowAccumulationLwe_unit' => 'mm of LWE',
            'sleetAccumulation_unit' => 'mm',
            'sleetAccumulationLwe_unit' => 'mm of LWE',
            'iceAccumulation_unit' => 'mm',
            'iceAccumulationLwe_unit' => 'mm of LWE',
            'sunriseTime_unit' => '',
            'sunsetTime_unit' => '',
            'visibility_unit' => 'km',
            'cloudCover_unit' => '%',
            'cloudBase_unit' => 'km',
            'cloudCeiling_unit' => 'km',
            'moonPhase_unit' => '',
            'uvIndex_unit' => '',
            'uvHealthConcern_unit' => '',
            'weatherCodeFullDay_unit' => '',
            'weatherCodeDay_unit' => '',
            'weatherCodeNight_unit' => '',
            'weatherCode_unit' => ''  
        );

/*
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
*/



        function setParam($key,$value){
            $this->param[$key] = $value;
        }

        function weatherCode(int $code){
            switch ($code) {
                case 1000:
                    $weather = "klar";
                    break;
                case 1001:
                    $weather = "wolkig";
                    break;
                case 1100:
                    $weather = "meist klar";
                    break;
                case 1101:
                    $weather = "teilweise wolkig";
                    break;
                case 1101:
                    $weather = "meistens wolkig";
                    break;
                case 2000:
                    $weather = "Nebel";
                    break;   
                case 2001:
                    $weather = "leichter Nebel";
                    break; 
                case 3000:
                    $weather = "leichter Wind";
                    break; 
                case 3001:
                    $weather = "Wind";
                    break; 
                case 3002:
                    $weather = "starker Wind";
                    break; 
                case 4000:
                    $weather = "Nieselregen";
                    break; 
                case 4001:
                    $weather = "Regen";
                    break;   
                case 4200:
                    $weather = "leichter Regen";
                    break; 
                case 4201:
                    $weather = "starker Regen";
                    break;   
                case 5000:
                    $weather = "Schnee";
                    break;
                case 5001:
                    $weather = "Wirbelwind";
                    break;     
                case 5100:
                    $weather = "leichter Schneefall";
                    break;   
                case 5101:
                    $weather = "starker Schneefall";
                    break; 
                case 6000:
                    $weather = "eisiger Nieselregen";
                    break; 
                case 6001:
                    $weather = "eisiger Regen";
                    break; 
                case 6200:
                    $weather = "leichter eisiger Regen";
                    break; 
                case 6201:
                    $weather = "starker eisiger Regen";
                    break; 
                case 7000:
                    $weather = "Hagel";
                    break; 
                case 7101:
                    $weather = "starker Hagel";
                    break; 
                case 7102:
                    $weather = "leichter Hagel";
                    break; 
                case 8000:
                    $weather = "Gewitter";
                    break; 
                default:
                    $weather = "unbekannt";
                    break;
            }
            return $weather;
        }

 

        # Alle Felder mit Eintrag TRUE ausfiltern
        public function getFields($range){
            $felder = array();
            if($range == "now"){
                $felder = array_keys($this->fieldsNow, true);
            }
            if($range =="week"){
                $felder = array_keys($this->fieldsWeek, true);
            }
            if($range =="dayH"){
                $fields = "temperature";
            }
            else{
                $fields = $felder[0];
                for($i = 1; $i < count($felder); $i++) {
                    $fields = $fields.",".$felder[$i];
                } 
            }
            return $fields;
        }

        # Parameter-Liste Werte mit neuen Werten schreiben
        public function update_params($range) {
            $this->params['fields'] = $this->getFields($range);
            return $this->params;
        }

        public function getData(){
            $response = array();
            $curl = curl_init();    
            $web_url =  self::api_uri;
            $info = http_build_query($this->params);
            $retriveWebUrl = $web_url."?".$info;

            curl_setopt_array($curl, [
            CURLOPT_URL => $retriveWebUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER, array("accept: application/json")
            ]);

            $json_string = curl_exec($curl);
            $err = curl_error($curl);
            
            curl_close($curl);

            $array_json = json_decode($json_string, true); 
            
            if ($err or array_key_exists('code',$array_json)) {
                $errMessage = TIO_ERROR_CODES::ToString($array_json['code']);
                return array(false, "cURL Error #:" . $errMessage);
            } else {
                return array(true, $array_json);
            }
 


        }

    }
