<?php
/**
 * Title: darksky.net Weather API
  *
 * author PiTo
 * 
 * GITHUB = <https://github.com/SymPiTo/MySymApps>
 * 
 * Version:1.0.2019.4.5
 */
//Class: MyWeather



require_once __DIR__.'/../libs/MyHelper.php';  // diverse Klassen
require_once __DIR__.'/../libs/tomorrowIO.php';  // diverse Klassen




class MyWeather extends IPSModule
{
       //Traits verbinden
       use DebugHelper, ModuleHelper, BuffHelper;

    
       
      
    
       
    
    # ___________________________________________________________________________ 
    #    Section: Internal Modul Functions
    #    Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung.
    # ___________________________________________________________________________ 

  
    #-----------------------------------------------------------# 
    #    Function: Create                                       #
    #    Create() Wird ausgeführt, beim Anlegen der Instanz.    #
    #-----------------------------------------------------------#    
    

    public function Create()
    {
	//Never delete this line!
        parent::Create();

        

        // Variable aus dem Instanz Formular registrieren (zugänglich zu machen)
        // Aufruf dieser Form Variable mit  $this->ReadPropertyFloat("IDENTNAME")
        //
        //$this->RegisterPropertyFloat("IDENTNAME", 0.5);
        $this->RegisterPropertyBoolean("ID_active", false);
        $this->RegisterPropertyString("APIkey", "111111111111111111");
        $this->RegisterPropertyString("Latitude", 49.3987524);  
        $this->RegisterPropertyString("longitude", 8.6724335);  
        $this->RegisterPropertyString("Town_ID", '631232f26730c50009585472');  
        $this->RegisterPropertyString("WetterDaten", "[]");
        
        
        //Integer Variable anlegen
        //integer RegisterVariableInteger ( string $Ident, string $Name, string $Profil, integer $Position )
        //Aufruf dieser Variable mit $this->GetIDForIdent("IDENTNAME")
        //$this->RegisterVariableInteger("FSSC_Position", "Position", "Rollo.Position");
      
        //Boolean Variable anlegen
        //integer RegisterVariableBoolean ( string $Ident, string $Name, string $Profil, integer $Position )
        // Aufruf dieser Variable mit $this->GetIDForIdent("IDENTNAME")
        //$this->RegisterVariableBoolean("FSSC_Mode", "Mode");
        
        //String Variable anlegen
        //RegisterVariableString ($Ident,  $Name, $Profil, $Position )
        //Aufruf dieser Variable mit $this->GetIDForIdent("IDENTNAME")
        //$this->RegisterVariableString("SZ_MoFr", "SchaltZeiten Mo-Fr");
        $variablenID = $this->RegisterVariableString ("ID_Week", "WeekFrame", "~HTMLBox", 0);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID = $this->RegisterVariableString ("ID_WeekData", "WeekData", "", 1);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID = $this->RegisterVariableString ("ID_Now", "NowFrame", "~HTMLBox", 2);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID = $this->RegisterVariableString ("ID_NowData", "NowData", "", 3);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID = $this->RegisterVariableString ("ID_dayhData", "DayHData", "", 3);
        IPS_SetInfo ($variablenID, "WSS"); 
        // Aktiviert die Standardaktion der Statusvariable zur Bedienbarkeit im Webfront
        //$this->EnableAction("IDENTNAME");
        
        //IPS_SetVariableCustomProfile(§this->GetIDForIdent("Mode"), "Rollo.Mode");
        
        // Klasse deklarieren - Klasssenvariable wird über BuffHelper deklariert.
        $this->WDclass = new WeatherApi();
       

        //anlegen eines Timers
        $this->RegisterTimer("TimerGetWeather", 0, 'W_update($_IPS[\'TARGET\']);');

        

    }

 

    #---------------------------------------------------------------#
    #     Function: ApplyChanges                                    #
    #     ApplyChanges() Wird ausgeführt, beim anlegen der Instanz. #
    #     und beim ändern der Parameter in der Form                 #
    #---------------------------------------------------------------#
    # SYSTEM-VARIABLE:
    #    InstanceID - $this->InstanceID.
    #
    # EVENTS:
    #    SwitchTimeEvent".$this->InstanceID   -   Wochenplan (Mo-Fr und Sa-So)
    #    SunRiseEvent".$this->InstanceID       -   cyclice Time Event jeden Tag at SunRise
    #---------------------------------------------------------------------------------------- 
    public function ApplyChanges() {   
	    $this->RegisterMessage(0, IPS_KERNELSTARTED);
        //Never delete this line!
        parent::ApplyChanges();

        $apiData = $this->WDclass;
        $apikey = $this->ReadPropertyString('APIkey'); 
        $apiData->params['apikey'] = $apikey;

        $stadtid = $this->ReadPropertyString("Town_ID");  
        $apiData->params['location'] = $stadtid;

   
        $this->setFields();

        $ModOn = $this->ModuleUp($this->ReadPropertyBoolean("ID_active"));
        if(!$ModOn){
            
            $this->SetTimerInterval("TimerGetWeather", 3600000);
        }
        else{
           $this->SetTimerInterval("TimerGetWeather", 0); 
        }
    }
 
    #------------------------------------------------------------# 
    #  Function: MessageSink                                     #
    #  MessageSink() wird nur bei registrierten                  #
    #  NachrichtenIDs/SenderIDs-Kombinationen aufgerufen.        #
    #------------------------------------------------------------#    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        switch ($Message) {
            case IPS_KERNELSTARTED: // Nach dem IPS-Start
                $this->KernelReady(); // Sagt alles.
                break;
        }
    }


    #-------------------------------------------------------------#
    #    Function: Destroy                                        #
    #        Destroy() wird beim löschen der Instanz              #
    #        und update der Module aufgerufen                     #
    #-------------------------------------------------------------#
    
    

    #------------------------------------------------------------# 
    #    Function: RequestAction                                 #
    #        RequestAction() wird von schaltbaren Variablen      #
    #        aufgerufen.                                         #
    #------------------------------------------------------------#
    public function RequestAction($Ident, $Value) {
         switch($Ident) {
            case "UpDown":
                SetValue($this->GetIDForIdent($Ident), $Value);
                if($this->getvalue($Ident)){
                    $this->SetRolloDown();  
                }
                else{
                    $this->SetRolloUp();
                }
                break;
             case "Mode":
                $this->SetMode($Value);  
                break;
            default:
                throw new Exception("Invalid Ident");
        }
 
    }

    #_________________________________________________________________________________________________________
    # Section: Public Functions
    #    Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" 
    #    eingefügt wurden.
    #    Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur 
    #    Verfügung gestellt:
    #_________________________________________________________________________________________________________


    public function setFields(){
        $apiData =  $this->WDclass;
        $felder_Json = $this->ReadPropertyString('WetterDaten');
        $felder_array = json_decode($felder_Json, true);  
        foreach ($felder_array as $key => $value) {
            if($value["nowData"]){
                $apiData->fieldsNow[$value["WetterData"]] = true;
            }
            else{
                $apiData->fieldsNow[$value["WetterData"]] = false;
            }
            if($value['weekData']){
                $apiData->fieldsWeek[$value["WetterData"]] = true;
            }
            else{
                $apiData->fieldsWeek[$value["WetterData"]] = false;
            }
        }
        $this->WDclass = $apiData;
        return $felder_array;
    }





    public function setParameters($range){  
        $apiData =  $this->WDclass;
        $apiData->params['apikey'] = $this->ReadPropertyString('APIkey'); 
        
        if ($range === 'week'){
            $apiData->params['startTime'] = 'now'; 
            $apiData->params['endTime'] = 'nowPlus5d';
            $apiData->params['timesteps'] = '1d';
            $apiData->params['fields'] = $apiData->getFields("week");
        }
        if ($range === 'now'){
            $apiData->params['startTime'] = 'now'; 
            $apiData->params['endTime'] = 'nowPlus1d';
            $apiData->params['timesteps'] = '1d';
            $apiData->params['fields'] = $apiData->getFields("now");
        }
        if ($range === 'dayH'){
            $apiData->params['startTime'] = 'now'; 
            $apiData->params['endTime'] = 'nowPlus6h';
            $apiData->params['timesteps'] = '1h';
            $apiData->params['fields'] = $apiData->getFields("dayH");
        }
        $this->WDclass = $apiData;
    
        return $apiData->params;
    }




public function getF($range){
    $apiData =  $this->WDclass;
    return $apiData->getFields($range);
}







    /*-----------------------------------------------------------------------------
    Function: getAPIData
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        $range: 'week'
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function getAPIData($range){
        
        $this->setparameters($range);
        $apiData =  $this->WDclass;
        //Daten holen
        $array_json = $apiData->getData();
        if($array_json[0] == true){
            if($range == "now"){
                return $array_json[1]['data']['timelines'][0]['intervals'];
            } else {
                return $array_json[1]['data']['timelines'][0]['intervals'];
            }
        }
        else{
            //Fehler!!
            return $array_json;
        }
    }  
            
 
    public function update(){
        $wetterNow = array();
        $wetterWeek = array();
        $tempday = array();

        $apiData =  $this->WDclass;

        $this->setParameters("dayH");
        $dayHdata = $this->getAPIData("dayH");

        for($i=0; $i < 7; $i++) {
        

            if (array_key_exists('temperature', $dayHdata[$i]['values'])) {
                $zeit = $dayHdata[$i]['startTime'];

                $tempday[$i]['time'] = substr($zeit,strpos($zeit,"T")+1,5) ;
                $tempday[$i]['temperature'] = round($dayHdata[$i]['values']['temperature']).$apiData->units['temperature_unit'];
            } else {
                $tempday[$i]['time'] = '-';
                $tempday[$i]['temperature'] = "-";
            }
           


        }    
        $this->setvalue('ID_dayhData', json_encode($tempday));


        $this->setParameters("now");
        //Daten holen
        $dayData = $this->getAPIData("now");
        //Tag berechnen
        
        $today = getdate();
        $wetterNow[0]['weekday']  =  $today['weekday'];
       

        if (array_key_exists('temperatureApparent', $dayData[0]['values'])) {
            $wetterNow[0]['temperatureApparent'] = round($dayData[0]['values']['temperatureApparent']);
        } else {
            $wetterNow[0]['temperatureApparent'] = "-";
        }

        if (array_key_exists('temperature', $dayData[0]['values'])) {
            $wetterNow[0]['temperature'] = round($dayData[0]['values']['temperature']);
        } else {
            $wetterNow[0]['temperature'] = "-";
        }

        if (array_key_exists('temperatureMin', $dayData[0]['values'])) {
            $wetterNow[0]['temperatureMin'] = round($dayData[0]['values']['temperatureMin']).$apiData->units['temperature_unit'];
        } else {
            $wetterNow[0]['temperatureMin'] = "-";
        }

        if (array_key_exists('temperatureMax', $dayData[0]['values'])) {
            $wetterNow[0]['temperatureMax'] = round($dayData[0]['values']['temperatureMax']).$apiData->units['temperature_unit'];
        } else {
            $wetterNow[0]['temperatureMax'] = "-";
        }

        if (array_key_exists('windSpeed', $dayData[0]['values'])) {
            $wetterNow[0]['windSpeed'] = round($dayData[0]['values']['windSpeed']);
        } else {
            $wetterNow[0]['windSpeed'] = "-";
        }

        if (array_key_exists('windGust', $dayData[0]['values'])) {
            $wetterNow[0]['windGust'] = round($dayData[0]['values']['windGust']);
        } else {
            $wetterNow[0]['windGust'] = "-";
        }

        if (array_key_exists('cloudCover', $dayData[0]['values'])) {
            $wetterNow[0]['cloudCover'] = round($dayData[0]['values']['cloudCover']);
        } else {
            $wetterNow[0]['cloudCover'] = "-";
        }
        
        if (array_key_exists('humidity', $dayData[0]['values'])) {
            $wetterNow[0]['humidity'] = round($dayData[0]['values']['humidity']);
        } else {
            $wetterNow[0]['humidity'] = "-";
        }

        if (array_key_exists('uvIndex', $dayData[0]['values'])) {
            $wetterNow[0]['uvIndex'] = $dayData[0]['values']['uvIndex'];
        } else {
            $wetterNow[0]['uvIndex'] = "-";
        }

        if (array_key_exists('rainIntensity', $dayData[0]['values'])) {
            $wetterNow[0]['rainIntensity'] = round($dayData[0]['values']['rainIntensity']);
        } else {
            $wetterNow[0]['rainIntensity'] = "-";
        }

        if (array_key_exists('weatherCode', $dayData[0]['values'])) {
            $wetterNow[0]['weatherCode'] = $dayData[0]['values']['weatherCode'];
            $wetterNow[0]['weatherText'] = $apiData->weatherCode($dayData[0]['values']['weatherCode']);
        } else {
            $wetterNow[0]['weatherCode'] = "-";
        }

        if (array_key_exists('moonPhase', $dayData[0]['values'])) {
            $wetterNow[0]['moonPhase'] = $dayData[0]['values']['moonPhase'];
        } else {
            $wetterNow[0]['moonPhase'] = "-";
        }

        $this->setvalue('ID_NowData', json_encode($wetterNow));
   
        $this->setParameters("week");
        //Daten holen
        
        $weekData = $this->getAPIData("week");
        $this->SendDebug("WochenDatenaus API:", $weekData, 0);
        //Tag berechnen
        $wochentage = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
        for($i=0; $i < 6; $i++) {
            $zeit = strtotime($weekData[$i]['startTime']);
            $wetterWeek[$i]['weekday']  =  $wochentage[date("w", $zeit)];
            $wetterWeek[$i]['weekdayNo']  =  date("w", $zeit);

            if (array_key_exists('temperatureApparent', $weekData[$i]['values'])) {
                $wetterWeek[$i]['temperatureApparent'] = $weekData[$i]['values']['temperatureApparent'].$apiData->units['temperature_unit'];
            } else {
                $wetterWeek[$i]['temperatureApparent'] = "-";
            }

            if (array_key_exists('temperature', $weekData[$i]['values'])) {
                $wetterWeek[$i]['temperature'] = $weekData[$i]['values']['temperature'].$apiData->units['temperature_unit'];
            } else {
                $wetterWeek[$i]['temperature'] = "-";
            }

            if (array_key_exists('temperatureMin', $weekData[$i]['values'])) {
                $wetterWeek[$i]['temperatureMin'] = round($weekData[$i]['values']['temperatureMin']).$apiData->units['temperature_unit'];
            } else {
                $wetterWeek[$i]['temperatureMin'] = "-";
            }

            if (array_key_exists('temperatureMax', $weekData[$i]['values'])) {
                $wetterWeek[$i]['temperatureMax'] = round($weekData[$i]['values']['temperatureMax']).$apiData->units['temperature_unit'];
            } else {
                $wetterWeek[$i]['temperatureMax'] = "-";
            }

            if (array_key_exists('windSpeed', $weekData[$i]['values'])) {
                $wetterWeek[$i]['windSpeed'] = 'Windgeschw: '.round($weekData[$i]['values']['windSpeed']).'m/s';
            } else {
                $wetterWeek[$i]['windSpeed'] = '-';
            }

            if (array_key_exists('cloudCover', $weekData[$i]['values'])) {
                $wetterWeek[$i]['cloudCover'] = 'Bewölkung: '.$weekData[$i]['values']['cloudCover'].$apiData->units['cloudCover_unit'];
            } else {
                $wetterWeek[$i]['cloudCover'] = "-";
            }

 

            if (array_key_exists('humidity', $weekData[$i]['values'])) {
                $wetterWeek[$i]['humidity'] = 'Feuchte: '.round($weekData[$i]['values']['humidity']).$apiData->units['humidity_unit'];
            } else {
                $wetterWeek[$i]['humidity'] = "-";
            }

            if (array_key_exists('uvIndex', $weekData[$i]['values'])) {
                $wetterWeek[$i]['uvIndex'] = 'UV-Index: '.$weekData[$i]['values']['uvIndex'];
            } else {
                $wetterWeek[$i]['uvIndex'] = "-";
            }

            if (array_key_exists('rainIntensity', $weekData[$i]['values'])) {
                $wetterWeek[$i]['rainIntensity'] = 'Niederschlag: '.$weekData[$i]['values']['rainIntensity'].$apiData->units['rainIntensity_unit'];
            }   else {
                $wetterWeek[$i]['rainIntensity'] = "-";
            }

            if (array_key_exists('weatherCode', $weekData[$i]['values'])) {
                $wetterWeek[$i]['weatherCode'] = $weekData[$i]['values']['weatherCode'];
                $wetterWeek[$i]['weatherText'] = 'Wetter: '.$apiData->weatherCode($weekData[$i]['values']['weatherCode']);
            } else {
                $wetterWeek[$i]['weatherCode'] = "-";
            }


            if (array_key_exists('windGust', $weekData[$i]['values'])) {
                $wetterWeek[$i]['windGust'] = $weekData[$i]['values']['windGust'].$apiData->units['windGust_unit'];
            } else {
                $wetterWeek[$i]['windGust'] = "-";
            }

            if (array_key_exists('moonPhase', $weekData[$i]['values'])) {
                $wetterWeek[$i]['moonPhase'] = 'RegenIntensitaet: '.$weekData[$i]['values']['moonPhase'];
            }   else {
                $wetterWeek[$i]['moonPhase'] = "-";
            }
    
        }
        $this->setvalue('ID_WeekData', json_encode($wetterWeek));
        return [$tempday, $wetterNow, $wetterWeek];
    }
 
}