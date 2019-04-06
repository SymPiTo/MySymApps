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
class MyWeather extends IPSModule
{
    /* 
    _______________________________________________________________________ 
     Section: Internal Modul Funtions
     Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung.
    _______________________________________________________________________ 
     */
            
    /* ------------------------------------------------------------ 
    Function: Create  
    Create() wird einmalig beim Erstellen einer neuen Instanz und 
    neu laden der Modulesausgeführt. Vorhandene Variable werden nicht veändert, auch nicht 
    eingetragene Werte (Properties).
    Variable können hier nicht verwendet werden nur statische Werte.
    Überschreibt die interne IPS_Create(§id)  Funktion
   
     CONFIG-VARIABLE:
      FS20RSU_ID   -   ID des FS20RSU Modules (selektierbar).
     
    STANDARD-AKTIONEN:
      FSSC_Position    -   Position (integer)

    ------------------------------------------------------------- */
    public function Create()
    {
	//Never delete this line!
        parent::Create();
 
        // Variable aus dem Instanz Formular registrieren (zugänglich zu machen)
        // Aufruf dieser Form Variable mit  $this->ReadPropertyFloat("IDENTNAME")
        //
        //$this->RegisterPropertyFloat("IDENTNAME", 0.5);
        //$this->RegisterPropertyBoolean("IDENTNAME", false);
        $this->RegisterPropertyString("key", "111111111111111111");
        $this->RegisterPropertyString("Latitude", 49.3987524);  
        $this->RegisterPropertyString("longitude", 8.6724335);  
        
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
        $this->RegisterVariableString ("ID_Week", "WeekFrame", "~HTMLBox", 0);
        $this->RegisterVariableString ("ID_WeekData", "WeekData", "", 1);
        $this->RegisterVariableString ("ID_Now", "NowFrame", "~HTMLBox", 2);
        $this->RegisterVariableString ("ID_NowData", "NowData", "", 3);
        
        // Aktiviert die Standardaktion der Statusvariable zur Bedienbarkeit im Webfront
        //$this->EnableAction("IDENTNAME");
        
        //IPS_SetVariableCustomProfile(§this->GetIDForIdent("Mode"), "Rollo.Mode");
        
        //anlegen eines Timers
        //$this->RegisterTimer("TimerName", 0, "FSSC_reset($_IPS[!TARGET!>]);");
            


    }
   /* ------------------------------------------------------------ 
     Function: ApplyChanges 
      ApplyChanges() Wird ausgeführt, wenn auf der Konfigurationsseite "Übernehmen" gedrückt wird 
      und nach dem unittelbaren Erstellen der Instanz.
     
    SYSTEM-VARIABLE:
        InstanceID - $this->InstanceID.

    EVENTS:
        SwitchTimeEvent".$this->InstanceID   -   Wochenplan (Mo-Fr und Sa-So)
        SunRiseEvent".$this->InstanceID       -   cyclice Time Event jeden Tag at SunRise
    ------------------------------------------------------------- */
    public function ApplyChanges()
    {
	//Never delete this line!
        parent::ApplyChanges();
       
    }
    
   /* ------------------------------------------------------------ 
      Function: RequestAction  
      RequestAction() Wird ausgeführt, wenn auf der Webfront eine Variable
      geschaltet oder verändert wird. Es werden die System Variable des betätigten
      Elementes übergeben.
      Ausgaben über echo werden an die Visualisierung zurückgeleitet
     
   
    SYSTEM-VARIABLE:
      $this->GetIDForIdent($Ident)     -   ID der von WebFront geschalteten Variable
      $Value                           -   Wert der von Webfront geänderten Variable

   STANDARD-AKTIONEN:
      FSSC_Position    -   Slider für Position
      UpDown           -   Switch für up / Down
      Mode             -   Switch für Automatik/Manual
     ------------------------------------------------------------- */
    public function RequestAction($Ident, $Value) {
         switch($Ident) {
            case "UpDown":
                SetValue($this->GetIDForIdent($Ident), $Value);
                if(getvalue($this->GetIDForIdent($Ident))){
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

  /* ______________________________________________________________________________________________________________________
     Section: Public Funtions
     Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
     Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt:
    
     FSSC_XYFunktion($Instance_id, ... );
     ________________________________________________________________________________________________________________________ */
    /*-----------------------------------------------------------------------------
    Function: getAPIData
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function getAPIData(){
        $api = $this->ReadPropertyString("key"); 
        $latitude = $this->ReadPropertyString("Latitude");  
        $longitude = $this->ReadPropertyString("longitude");  



        //$json_string = file_get_contents("https://api.darksky.net/forecast/$api/$latitude,$longitude?exclude=minutely&lang=de&units=si");  
        $json_string = '{"latitude":49.3987524,"longitude":8.6724335,"timezone":"Europe/Berlin","currently":{"time":1554490902,"summary":"Nebel","icon":"fog","precipIntensity":0.0152,"precipProbability":0.03,"precipType":"rain","temperature":6.8,"apparentTemperature":6.02,"dewPoint":6.76,"humidity":1,"pressure":1004.54,"windSpeed":5.33,"windGust":8.32,"windBearing":356,"cloudCover":0.92,"uvIndex":0,"visibility":1.14,"ozone":323},"daily":{"summary":"Leichter Regen am Dienstag und Mittwoch mit steigender Temperatur von 16°C am Montag.","icon":"rain","data":[{"time":1554415200,"summary":"Nebel am Vormittag.","icon":"fog","sunriseTime":1554440136,"sunsetTime":1554487362,"moonPhase":0.01,"precipIntensity":0.0178,"precipIntensityMax":0.0533,"precipIntensityMaxTime":1554418800,"precipProbability":0.4,"precipType":"rain","temperatureHigh":9.02,"temperatureHighTime":1554480000,"temperatureLow":4.37,"temperatureLowTime":1554526800,"apparentTemperatureHigh":8.33,"apparentTemperatureHighTime":1554480000,"apparentTemperatureLow":4.37,"apparentTemperatureLowTime":1554526800,"dewPoint":4.49,"humidity":0.89,"pressure":1005.54,"windSpeed":1.87,"windGust":16.35,"windGustTime":1554415200,"windBearing":22,"cloudCover":0.95,"uvIndex":3,"uvIndexTime":1554458400,"visibility":7.29,"ozone":355.91,"temperatureMin":3.51,"temperatureMinTime":1554447600,"temperatureMax":9.02,"temperatureMaxTime":1554480000,"apparentTemperatureMin":1.81,"apparentTemperatureMinTime":1554447600,"apparentTemperatureMax":8.33,"apparentTemperatureMaxTime":1554480000},{"time":1554501600,"summary":"Den ganzen Tag lang überwiegend bewölkt.","icon":"partly-cloudy-day","sunriseTime":1554526409,"sunsetTime":1554573853,"moonPhase":0.03,"precipIntensity":0.0025,"precipIntensityMax":0.0102,"precipIntensityMaxTime":1554562800,"precipProbability":0.05,"precipType":"rain","temperatureHigh":15.74,"temperatureHighTime":1554566400,"temperatureLow":8.23,"temperatureLowTime":1554616800,"apparentTemperatureHigh":15.74,"apparentTemperatureHighTime":1554566400,"apparentTemperatureLow":7.96,"apparentTemperatureLowTime":1554613200,"dewPoint":5.04,"humidity":0.69,"pressure":1003.73,"windSpeed":7.37,"windGust":22.18,"windGustTime":1554562800,"windBearing":73,"cloudCover":0.76,"uvIndex":3,"uvIndexTime":1554544800,"visibility":16.09,"ozone":335.27,"temperatureMin":4.37,"temperatureMinTime":1554526800,"temperatureMax":15.74,"temperatureMaxTime":1554566400,"apparentTemperatureMin":4.37,"apparentTemperatureMinTime":1554526800,"apparentTemperatureMax":15.74,"apparentTemperatureMaxTime":1554566400},{"time":1554588000,"summary":"Den ganzen Tag lang überwiegend bewölkt.","icon":"partly-cloudy-day","sunriseTime":1554612683,"sunsetTime":1554660344,"moonPhase":0.07,"precipIntensity":0.0076,"precipIntensityMax":0.0533,"precipIntensityMaxTime":1554670800,"precipProbability":0.14,"precipType":"rain","temperatureHigh":16.01,"temperatureHighTime":1554642000,"temperatureLow":8.79,"temperatureLowTime":1554703200,"apparentTemperatureHigh":16.01,"apparentTemperatureHighTime":1554642000,"apparentTemperatureLow":7.93,"apparentTemperatureLowTime":1554703200,"dewPoint":6.16,"humidity":0.65,"pressure":1004.65,"windSpeed":0.64,"windGust":18.04,"windGustTime":1554660000,"windBearing":145,"cloudCover":0.91,"uvIndex":3,"uvIndexTime":1554631200,"visibility":16.09,"ozone":354.7,"temperatureMin":8.23,"temperatureMinTime":1554616800,"temperatureMax":16.01,"temperatureMaxTime":1554642000,"apparentTemperatureMin":7.96,"apparentTemperatureMinTime":1554613200,"apparentTemperatureMax":16.01,"apparentTemperatureMaxTime":1554642000},{"time":1554674400,"summary":"Den ganzen Tag lang überwiegend bewölkt.","icon":"partly-cloudy-day","sunriseTime":1554698958,"sunsetTime":1554746836,"moonPhase":0.1,"precipIntensity":0.0533,"precipIntensityMax":0.1956,"precipIntensityMaxTime":1554739200,"precipProbability":0.47,"precipType":"rain","temperatureHigh":16.08,"temperatureHighTime":1554732000,"temperatureLow":9.78,"temperatureLowTime":1554789600,"apparentTemperatureHigh":16.08,"apparentTemperatureHighTime":1554732000,"apparentTemperatureLow":8.89,"apparentTemperatureLowTime":1554789600,"dewPoint":7.51,"humidity":0.72,"pressure":1008.25,"windSpeed":7.55,"windGust":15.53,"windGustTime":1554735600,"windBearing":234,"cloudCover":0.89,"uvIndex":3,"uvIndexTime":1554717600,"visibility":15.85,"ozone":362.99,"temperatureMin":8.79,"temperatureMinTime":1554703200,"temperatureMax":16.08,"temperatureMaxTime":1554732000,"apparentTemperatureMin":7.93,"apparentTemperatureMinTime":1554703200,"apparentTemperatureMax":16.08,"apparentTemperatureMaxTime":1554732000},{"time":1554760800,"summary":"Den ganzen Tag lang überwiegend bewölkt.","icon":"partly-cloudy-day","sunriseTime":1554785233,"sunsetTime":1554833327,"moonPhase":0.13,"precipIntensity":0.3277,"precipIntensityMax":0.7595,"precipIntensityMaxTime":1554840000,"precipProbability":0.86,"precipType":"rain","temperatureHigh":15.17,"temperatureHighTime":1554825600,"temperatureLow":8.46,"temperatureLowTime":1554876000,"apparentTemperatureHigh":15.17,"apparentTemperatureHighTime":1554825600,"apparentTemperatureLow":6.63,"apparentTemperatureLowTime":1554876000,"dewPoint":8.22,"humidity":0.79,"pressure":1008.54,"windSpeed":2.12,"windGust":18.59,"windGustTime":1554789600,"windBearing":181,"cloudCover":0.89,"uvIndex":3,"uvIndexTime":1554807600,"visibility":15.9,"ozone":372.33,"temperatureMin":9.6,"temperatureMinTime":1554796800,"temperatureMax":15.17,"temperatureMaxTime":1554825600,"apparentTemperatureMin":8.85,"apparentTemperatureMinTime":1554793200,"apparentTemperatureMax":15.17,"apparentTemperatureMaxTime":1554825600},{"time":1554847200,"summary":"Den ganzen Tag lang überwiegend bewölkt.","icon":"partly-cloudy-day","sunriseTime":1554871509,"sunsetTime":1554919819,"moonPhase":0.17,"precipIntensity":0.3988,"precipIntensityMax":0.6706,"precipIntensityMaxTime":1554847200,"precipProbability":0.9,"precipType":"rain","temperatureHigh":12.68,"temperatureHighTime":1554901200,"temperatureLow":6.14,"temperatureLowTime":1554962400,"apparentTemperatureHigh":12.68,"apparentTemperatureHighTime":1554901200,"apparentTemperatureLow":3.66,"apparentTemperatureLowTime":1554962400,"dewPoint":7.06,"humidity":0.79,"pressure":1008.07,"windSpeed":11.78,"windGust":30.3,"windGustTime":1554926400,"windBearing":38,"cloudCover":0.57,"uvIndex":3,"uvIndexTime":1554890400,"visibility":16.09,"ozone":373.92,"temperatureMin":8.38,"temperatureMinTime":1554879600,"temperatureMax":12.68,"temperatureMaxTime":1554901200,"apparentTemperatureMin":6.56,"apparentTemperatureMinTime":1554879600,"apparentTemperatureMax":12.68,"apparentTemperatureMaxTime":1554901200},{"time":1554933600,"summary":"Den ganzen Tag lang überwiegend bewölkt.","icon":"partly-cloudy-day","sunriseTime":1554957785,"sunsetTime":1555006310,"moonPhase":0.2,"precipIntensity":0.0965,"precipIntensityMax":0.3429,"precipIntensityMaxTime":1554933600,"precipProbability":0.65,"precipType":"rain","temperatureHigh":9.54,"temperatureHighTime":1554987600,"temperatureLow":2.58,"temperatureLowTime":1555048800,"apparentTemperatureHigh":7.73,"apparentTemperatureHighTime":1554987600,"apparentTemperatureLow":0.42,"apparentTemperatureLowTime":1555048800,"dewPoint":3.71,"humidity":0.75,"pressure":1011.85,"windSpeed":11.36,"windGust":26.33,"windGustTime":1554933600,"windBearing":36,"cloudCover":0.83,"uvIndex":3,"uvIndexTime":1554976800,"visibility":16.09,"ozone":385.7,"temperatureMin":6.14,"temperatureMinTime":1554962400,"temperatureMax":9.54,"temperatureMaxTime":1554987600,"apparentTemperatureMin":3.58,"apparentTemperatureMinTime":1554966000,"apparentTemperatureMax":7.73,"apparentTemperatureMaxTime":1554987600},{"time":1555020000,"summary":"Den ganzen Tag lang Klar.","icon":"clear-day","sunriseTime":1555044062,"sunsetTime":1555092802,"moonPhase":0.24,"precipIntensity":0.0025,"precipIntensityMax":0.0051,"precipIntensityMaxTime":1555020000,"precipProbability":0.04,"precipType":"rain","temperatureHigh":11.91,"temperatureHighTime":1555081200,"temperatureLow":4.43,"temperatureLowTime":1555131600,"apparentTemperatureHigh":11.91,"apparentTemperatureHighTime":1555081200,"apparentTemperatureLow":3.05,"apparentTemperatureLowTime":1555135200,"dewPoint":0.23,"humidity":0.63,"pressure":1014.77,"windSpeed":7.77,"windGust":14.98,"windGustTime":1555020000,"windBearing":49,"cloudCover":0.18,"uvIndex":4,"uvIndexTime":1555063200,"visibility":16.09,"ozone":395.41,"temperatureMin":2.58,"temperatureMinTime":1555048800,"temperatureMax":11.91,"temperatureMaxTime":1555081200,"apparentTemperatureMin":0.42,"apparentTemperatureMinTime":1555048800,"apparentTemperatureMax":11.91,"apparentTemperatureMaxTime":1555081200}]},"flags":{"sources":["meteoalarm","cmc","gfs","icon","isd","madis"],"meteoalarm-license":"Based on data from EUMETNET - MeteoAlarm [https://www.meteoalarm.eu/]. Time delays between this website and the MeteoAlarm website are possible; for the most up to date information about alert levels as published by the participating National Meteorological Services please use the MeteoAlarm website.","nearest-station":1.629,"units":"ca"},"offset":2}';
        $array_json = json_decode($json_string, true); 
        
        return $array_json;
    }  
            
    
    
    /*-----------------------------------------------------------------------------
    Function: $weather_now
    ...............................................................................
    Beschreibung: Holt sich die aktuellen Wetter Daten (1000 Zugriffe pro Tag sind kostenlos)
    ...............................................................................
    Parameters: 
        $array_json - API Daten als Array umgewandelt
    ...............................................................................
    Returns:    
        $wetterNowData - aktuelle Wetter Daten als Array
    ------------------------------------------------------------------------------  */
    Public function Weather_Now($array_json) 
    { 
        $weather_now = $array_json['currently'];
        $html = '<head> 
        <meta charset="utf-8"> 
        <title>Wetter</title> 
        <!--The following script tag downloads a font from the Adobe Edge Web Fonts server for use within the web page. We recommend that you do not modify it.--> 
        <script>var __adobewebfontsappname__="dreamweaver"</script> 
        <script src="http://use.edgefonts.net/source-sans-pro:n6:default;acme:n4:default;bilbo:n4:default.js" type="text/javascript"></script>'. 
        $this->Get_CSS().' 
        </head> 

        <body>'; 
           $html .= '<table>'; 
           $html.= '<tr> 
                   <td class="weathertablecell"> 
                   <section class="weatherframe"> 
                         <div class="weathertitledate">Aktuell</div> 
                       <figure class="cap-bot"><img src="https://darksky.net/images/weather-icons/'.$weather_now['icon'].'.png" alt="Wettericon" width="70" height="70"><figcaption>'.$weather_now['summary'].'</figcaption></figure> 
                       <section class="weatherpicright"> 
                           <div class="temperature">'.round($weather_now['temperature'], 1).' °C</div> 
                                 <div class="humidity">Luftfeuchtigkeit '.$weather_now['humidity'].'%</div> 
                          </section> 
                          <section class="weatherpicbottom"> 
                              <div class="wind">Ø Wind: '.$weather_now['windSpeed'].' km/h</div> 
                              <div class="temperaturefeel">Temperatur '.round($weather_now['apparentTemperature'], 1).' °C gefühlt</div> 
                              <div class="pressure">Luftdruck '.$weather_now['pressure'].' hPa</div> 
                              <div class="visibility">Sichtweite '.$weather_now['visibility'].' km</div> 
                           </section> 
                     </section> 
                    </td>'; 

           $html .= "</tr> 
                    </table>"; 
              $html .= '</body> 
        </html>'; 
              
        $wetterNowData['visibility'] = $weather_now['visibility'].' km';
        $wetterNowData['pressure'] = $weather_now['pressure'].' hPa';
        $wetterNowData['windSpeed'] = $weather_now['windSpeed'].' km/h';
        $wetterNowData['apparentTemperature'] = round($weather_now['apparentTemperature'], 1).' °C gefühlt';
        $wetterNowData['temperature'] = round($weather_now['temperature'], 1).' °C';
        $wetterNowData['humidity'] = $weather_now['humidity'].'%';
        $wetterNowData['icon'] = 'https://darksky.net/images/weather-icons/'.$weather_now['icon'].'.png'; 

        setvalue($this->GetIDForIdent("ID_Now"),$html);
        setvalue($this->GetIDForIdent("ID_NowData"), json_encode($wetterNowData));      
        return $wetterNowData; 
    } 

    
            
    /*-----------------------------------------------------------------------------
    Function: Weather_Now_And_Next_Days
    ...............................................................................
    Beschreibung:
     * Holt sich alle Wetter Daten der ganzen Woche
    ...............................................................................
    Parameters: 
        $array_json - API Daten als Array umgewandelt
    ...............................................................................
    Returns:    
        $wetterData - Wetter Daten der Woche
    ------------------------------------------------------------------------------  */
    public  function Weather_Now_And_Next_Days($array_json){  
        
        $weather_daily = $array_json['daily']['data'];
        $html = '<head> 
        <meta charset="utf-8"> 
        <title>Wetter</title> 
        <!--The following script tag downloads a font from the Adobe Edge Web Fonts server for use within the web page. We recommend that you do not modify it.--> 
        <script>var __adobewebfontsappname__="dreamweaver"</script> 
        <script src="http://use.edgefonts.net/source-sans-pro:n6:default;acme:n4:default;bilbo:n4:default.js" type="text/javascript"></script>'. 
        $this->Get_CSS().' 
        </head> 

        <body>'; 
            $html .= '<table>'; 

            $html.= '<tr>'; 
            $i=0;
            foreach ($weather_daily as $day => $data){ 
                $i = $i +1;
                if ($this->isToday($data['time'])){ 
                   $weekday = "Heute"; 
                   $wetterData[$i]['weekday'] = $weekday;
                } else { 
                   $day_names = array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag"); 
                   $weekday = $day_names[date("w",intval($data['time']))]; 
                   $wetterData[$i]['weekday'] = $weekday;
                } 

                $html.= '<td class="weathertablecell"> 
                            <section class="weatherframe"> 
                                 <div class="weathertitledate">'.$weekday.'</div> 
                             <figure class="cap-bot"><img src="https://darksky.net/images/weather-icons/'.$data['icon'].'.png" alt="Wettericon" width="70" height="70"><figcaption>'.$data['summary'].'</figcaption></figure> 
                             <section class="weatherpicright"> 
                                 <div class="temperaturemax">'.round($data['temperatureHigh'], 1).' °C</div> 
                                 <div class="temperaturemin">'.round($data['temperatureLow'], 1).' °C</div> 
                             </section> 
                             <section class="weatherpicbottom"> 
                                    <div class="wind">Ø Wind: '.$data['windSpeed'].' km/h</div> 
                                    <div class="wind">Ø Wind Böen: '.$data['windGust'].' km/h</div> 
                                    <div class="cloud">Wolken: '.$this->ConvertPercent($data['cloudCover']).' %</div> 
                                    <div class="humidity">Ø Feuchtigkeit: '.$this->ConvertPercent($data['humidity']).' %</div>'; 
                
                if(isset($data['precipType'])) 
                { 
                    $precipitation_type = $this->Get_PrecipitationType($data['precipType']); 
                    if($precipitation_type != "") 
                    { 
                    $html.= '<div class="precipitationtype">Niederschlagstyp: '.$this->$precipitation_type.'</div>'; 
                    $wetterData[$i]['precipitationtype'] = $this->$precipitation_type;
                    } 
                    else{
                        $wetterData[$i]['precipitationtype'] = "";
                    }
                } 
                else{
                        $wetterData[$i]['precipitationtype'] = "";
                    }

                $html.= '<div class="precipitationprobability">Regen: '.$this->ConvertPercent($data['precipProbability']).' %</div>                           
                               </section> 
                               </section> 
                               </td>'; 
                $wetterData[$i]['temperatureHigh'] = round($data['temperatureHigh'], 1).' °C';
                $wetterData[$i]['temperatureLow'] = round($data['temperatureLow'], 1).' °C';
                $wetterData[$i]['windSpeed'] = $data['windSpeed'].' km/h';
                $wetterData[$i]['windGust'] = $data['windGust'].' km/h';
                $wetterData[$i]['cloudCover'] = $this->ConvertPercent($data['cloudCover']).' %';
                $wetterData[$i]['humidity'] = $this->ConvertPercent($data['humidity']).' %';
                $wetterData[$i]['icon'] = 'https://darksky.net/images/weather-icons/'.$data['icon'].'.png';
 
            } 
           
           
                $html .= "</tr> 
                         </table>"; 
                $html .= '</body> 
            </html>'; 
                
            setvalue($this->GetIDForIdent("ID_Week"),$html);
            setvalue($this->GetIDForIdent("ID_WeekData"), json_encode($wetterData));
            return $wetterData; 
        }  
        
            
    /*-----------------------------------------------------------------------------
    Function: Get_PrecipitationType
    ...............................................................................
    Beschreibung:
     *  Umwandulung Engliche Begriffe in Deutsche
    ...............................................................................
    Parameters: 
        $precipitation_type - englischer Begriff
    ...............................................................................
    Returns:    
        $precipitation_type - Deutscher Begriff
    ------------------------------------------------------------------------------  */     
    private function Get_PrecipitationType($precipitation_type) 
    { 
        $precipitation_type = ""; 
        if ($precipitation_type == "rain") 
        { 
            $precipitation_type = "Regen"; 
        }  
        if ($precipitation_type == "snow") 
        { 
            $precipitation_type = "Schnee"; 
        } 
        if ($precipitation_type == "sleet") 
        { 
            $precipitation_type = "Schneeregen"; 
        } 

        return $precipitation_type; 
    }
        

    /*-----------------------------------------------------------------------------
    Function: isToday
    ...............................................................................
    Beschreibung:
     * Wandelt die UNIX timeStamp um und prüft ob dies Heute ist
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        Abfangszeit - EndZeit
    ------------------------------------------------------------------------------  */   
    private function isToday($time){ 
       $begin = mktime(0, 0, 0); 
       $end = mktime(23, 59, 59); 
       // check if given time is between begin and end 
       return (($time >= $begin) && ($time <= $end)); 
    } 
    
    
    /*-----------------------------------------------------------------------------
    Function: ConvertPercent
    ...............................................................................
    Beschreibung:
     * wandelt übergebenen Wert in Prozent um
    ...............................................................................
    Parameters: 
        $value - übergbener Wert
    ...............................................................................
    Returns:    
        $percentage - Prozentwert
    ------------------------------------------------------------------------------  */   
   private function ConvertPercent($value) 
    { 
        $percentage = $value * 100; 
        return $percentage; 
    }
    
    
    
        
    /*-----------------------------------------------------------------------------
    Function: Get_CSS
    ...............................................................................
    Beschreibung:
     * CSS Text
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        CSS Text
    ------------------------------------------------------------------------------  */  
    private function Get_CSS() 
    { 
        $style = '<style> 
        body { background-color:transparent; } 
        .weathertablecell { 
            width: 170px; 
            text-shadow: 1px 1px 0px rgba(66,66,66,1.00); 
            vertical-align: top; 
            padding-bottom: 0px; 
            padding-top: 0px; 
            margin-top: 0px; 
            margin-right: 0px; 
            margin-bottom: 0px; 
            margin-left: 0px; 
        } 
        .weatherframe { 
            background-color: rgba(136,123,123,0.55); 
            border-radius: 25px; 
            padding-left: 0px; 
            margin-left: 4px; 
            margin-right: 4px; 
            padding-top: 0px; 
            margin-top: 3px; 
            -webkit-box-shadow: 2px 2px 13px 0px rgba(50,49,49,1.00); 
            box-shadow: 2px 2px 13px 0px rgba(50,49,49,1.00); 
            padding-bottom: 0px; 
            margin-bottom: 0px; 
            position: relative; 
            height: 275px; 
        } 

        .weathertitlehour { 
            color: rgba(241,241,241,1.00); 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            text-align: center; 
            padding-top: 6px; 
        } 
        figure { 
            display: block; 
            position: relative; 
            overflow: hidden; 
            margin-left: 1px; 
            margin-right: 1px; 
            margin-top: 0px; 
        } 
        figcaption { 
            position: absolute; 
            background: rgba(0,0,0,0.55); 
            color: white; 
            padding: 10px 20px; 
            opacity: 0; 
            bottom: 0; 
            left: -30%; 
            -webkit-transition: all 0.6s ease; 
            -moz-transition: all 0.6s ease; 
            -o-transition: all 0.6s ease; 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            font-size: xx-small; 
        } 
        figure:hover figcaption { 
          opacity: 1; 
          left: 0; 
        } 
        .cap-left figcaption { bottom: 0; left: -30%; } 
        .cap-left:hover figcaption { left: 0; } 

        .cap-right figcaption { bottom: 0; right: -30%; } 
        .cap-right:hover figcaption { right: 0; } 

        .cap-top figcaption { left: 0; top: -30%; } 
        .cap-top:hover figcaption { top: 0; } 

        .cap-bot figcaption { left: 0; bottom: -30%;} 
        .cap-bot:hover figcaption { bottom: 0; } 
        figure:before {  
          content: "?";  
          position: absolute;  
          background: rgba(255,255,255,0.75);  
          color: black; 
          width: 24px; 
          height: 24px; 
          -webkit-border-radius: 12px; 
          -moz-border-radius:    12px; 
          border-radius:         12px; 
          text-align: center; 
          font-size: 14px; 
          line-height: 24px; 
          /* Only Fx 4 supporting transitions on psuedo elements so far... */ 
          -webkit-transition: all 0.6s ease; 
          -moz-transition: all 0.6s ease; 
          -o-transition: all 0.6s ease; 
          opacity: 0.75;     
        } 
        figure:hover:before { 
          opacity: 0; 
        } 
        .temperature { 
            position: relative; 
            text-align: right; 
            color: rgba(235,235,235,1.00); 
            padding-right: 5px; 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            text-shadow: 2px 2px 4px rgba(51,51,51,1.00); 
        } 
        .humidity { 
            position: relative; 
            text-align: right; 
            color: rgba(235,235,235,1.00); 
            padding-right: 5px; 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            text-shadow: 2px 2px 4px rgba(51,51,51,1.00); 
        } 
        .wind { 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            color: rgba(225,225,225,1.00); 
            font-size: x-small; 
            text-align: center; 
            text-shadow: 1px 1px 1px rgba(37,36,36,1.00); 
        } 
        .weathertitledate { 
            padding-top: 6px; 
            text-align: center; 
            color: rgba(241,241,241,1.00); 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
        } 
        .temperaturefeel { 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            color: rgba(225,225,225,1.00); 
            font-size: x-small; 
            text-align: center; 
            text-shadow: 1px 1px 1px rgba(37,36,36,1.00); 
        } 
        .pressure { 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            color: rgba(225,225,225,1.00); 
            font-size: x-small; 
            text-align: center; 
            text-shadow: 1px 1px 1px rgba(37,36,36,1.00); 
        } 
        .rain { 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            color: rgba(225,225,225,1.00); 
            font-size: x-small; 
            text-align: center; 
            text-shadow: 1px 1px 1px rgba(37,36,36,1.00); 
        } 
        .precipitationtype { 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            color: rgba(225,225,225,1.00); 
            font-size: x-small; 
            text-align: center; 
            text-shadow: 1px 1px 1px rgba(37,36,36,1.00); 
        } 
        .precipitationprobability { 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            color: rgba(225,225,225,1.00); 
            font-size: x-small; 
            text-align: center; 
            text-shadow: 1px 1px 1px rgba(37,36,36,1.00); 
        } 
        .cloud { 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            color: rgba(225,225,225,1.00); 
            font-size: x-small; 
            text-align: center; 
            text-shadow: 1px 1px 1px rgba(37,36,36,1.00); 
        } 
        .humidity { 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            color: rgba(225,225,225,1.00); 
            font-size: x-small; 
            text-align: center; 
            text-shadow: 1px 1px 1px rgba(37,36,36,1.00); 
        } 
        .weatherframe .weatherpicright { 
            position: relative; 
            top: -18px; 
        } 
        .weatherframe .weatherpicbottom { 
            position: relative; 
            left: auto; 
            right: auto; 
            top: -20px; 
        } 


        .visibility { 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            color: rgba(225,225,225,1.00); 
            font-size: x-small; 
            text-align: center; 
            text-shadow: 1px 1px 1px rgba(37,36,36,1.00); 
        } 
        .temperaturemax { 
            position: relative; 
            text-align: right; 
            color: rgba(235,235,235,1.00); 
            padding-right: 5px; 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            text-shadow: 2px 2px 4px rgba(51,51,51,1.00); 
        } 
        .temperaturemin { 
            position: relative; 
            text-align: right; 
            color: rgba(235,235,235,1.00); 
            padding-right: 5px; 
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, sans-serif; 
            text-shadow: 2px 2px 4px rgba(51,51,51,1.00); 
        } 
        </style>'; 
        return $style; 
    }  
        
        
        
        
        
        
        
        
        
        
        
        
    
    
   /* _______________________________________________________________________
    * Section: Private Funtions
    * Die folgenden Funktionen sind nur zur internen Verwendung verfügbar
    *   Hilfsfunktionen
    * _______________________________________________________________________
    */  

            
		
        /* ----------------------------------------------------------------------------
         Function: GetIPSVersion
        ...............................................................................
        gibt die instalierte IPS Version zurück
        ...............................................................................
        Parameters: 
            none
        ..............................................................................
        Returns:   
            $ipsversion (floatint)
        ------------------------------------------------------------------------------- */
	protected function GetIPSVersion()
	{
		$ipsversion = floatval(IPS_GetKernelVersion());
		if ($ipsversion < 4.1) // 4.0
		{
			$ipsversion = 0;
		} elseif ($ipsversion >= 4.1 && $ipsversion < 4.2) // 4.1
		{
			$ipsversion = 1;
		} elseif ($ipsversion >= 4.2 && $ipsversion < 4.3) // 4.2
		{
			$ipsversion = 2;
		} elseif ($ipsversion >= 4.3 && $ipsversion < 4.4) // 4.3
		{
			$ipsversion = 3;
		} elseif ($ipsversion >= 4.4 && $ipsversion < 5) // 4.4
		{
			$ipsversion = 4;
		} else   // 5
		{
			$ipsversion = 5;
		}

		return $ipsversion;
	}

 
    /* --------------------------------------------------------------------------- 
    Function: RegisterEvent
    ...............................................................................
    legt einen Event an wenn nicht schon vorhanden
      Beispiel:
      ("Wochenplan", "SwitchTimeEvent".$this->InstanceID, 2, $this->InstanceID, 20);  
      ...............................................................................
    Parameters: 
      $Name        -   Name des Events
      $Ident       -   Ident Name des Events
      $Typ         -   Typ des Events (1=cyclic 2=Wochenplan)
      $Parent      -   ID des Parents
      $Position    -   Position der Instanz
    ...............................................................................
    Returns:    
        none
    -------------------------------------------------------------------------------*/
    private function RegisterEvent($Name, $Ident, $Typ, $Parent, $Position)
    {
            $eid = @$this->GetIDForIdent($Ident);
            if($eid === false) {
                    $eid = 0;
            } elseif(IPS_GetEvent($eid)['EventType'] <> $Typ) {
                    IPS_DeleteEvent($eid);
                    $eid = 0;
            }
            //we need to create one
            if ($eid == 0) {
                    $EventID = IPS_CreateEvent($Typ);
                    IPS_SetParent($EventID, $Parent);
                    IPS_SetIdent($EventID, $Ident);
                    IPS_SetName($EventID, $Name);
                    IPS_SetPosition($EventID, $Position);
                    IPS_SetEventActive($EventID, false);  
            }
    }
    
 
    /* ----------------------------------------------------------------------------------------------------- 
    Function: RegisterScheduleAction
    ...............................................................................
     *  Legt eine Aktion für den Event fest
     * Beispiel:
     * ("SwitchTimeEvent".$this->InstanceID), 1, "Down", 0xFF0040, "FSSC_SetRolloDown(\$_IPS[!TARGET!]);");
    ...............................................................................
    Parameters: 
      $EventID
      $ActionID
      $Name
      $Color
      $Script
    .......................................................................................................
    Returns:    
        none
    -------------------------------------------------------------------------------------------------------- */
    private function RegisterScheduleAction($EventID, $ActionID, $Name, $Color, $Script)
    {
            IPS_SetEventScheduleAction($EventID, $ActionID, $Name, $Color, $Script);
    }



		
}