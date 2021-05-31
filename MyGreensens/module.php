<?php
/***************************************************************************
 * Title: _TITEL_
 *
 * Author: _AUTOR_
 * 
 * GITHUB: <https://github.com/SymPiTo/MySymApps/tree/master/_TITEL_>
 * 
 * Version: _VERSION_
 *************************************************************************** */
//require_once __DIR__ . '/../libs/_TRAIT_';
//require_once __DIR__ . '/../libs/_HELPERCLASS_';  // diverse Klassen

class MyGreensens extends IPSModule {

    //use DebugHelper,
    //InstanceStatus,
    //BufferHelper,
    //Semaphore;
/* 
___________________________________________________________________________ 
    Section: Internal Modul Funtions
    Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung.
___________________________________________________________________________ 
    */
    /* 
    ------------------------------------------------------------ 
        Function: Create  
        Create() Wird ausgeführt, beim anlegen der Instanz.
    -------------------------------------------------------------
    */
    public function Create() {
    //Never delete this line!
        parent::Create();

        //Register Properties from form.json
        $this->RegisterPropertyBoolean("ID_active", false);

        //$this->ReadPropertyFloat("NAME", 0.0);

        //$totalSensors = $this->ReadPropertyInteger("ID_Sensors", 0);
        $totalSensors = 6;
        $this->RegisterPropertyString("ID_Login", "");
        $this->RegisterPropertyString("ID_Passwort", "");

        $this->RegisterPropertyInteger("ID_Interval", 0);

        // Register Profiles
        //$this->RegisterProfiles();

        //Register Variables
        for ($zaehler = 0; $zaehler <= $totalSensors-1; $zaehler++) {
            $variablenID = $this->RegisterVariableInteger ("sensorID".$zaehler, $zaehler."Sensor ID", "" , $zaehler*8+1);
            IPS_SetInfo ($variablenID, "");
            $variablenID = $this->RegisterVariableString ("sensorName".$zaehler, $zaehler."Pflanzen Name", "", $zaehler*8+2); 
            IPS_SetInfo ($variablenID, "");
            $variablenID = $this->RegisterVariableBoolean ("sensorStatus".$zaehler, $zaehler."Sensor Status", "", $zaehler*8+3);
            IPS_SetInfo ($variablenID, "");
            $variablenID = $this->RegisterVariableFloat ("ID_Temp".$zaehler, $zaehler."Temperatur", "", $zaehler*8+4);
            IPS_SetInfo ($variablenID, "WSS");
            $variablenID = $this->RegisterVariableFloat ("ID_Illumination".$zaehler, $zaehler."Helligkeit", "", $zaehler*8+5);
            IPS_SetInfo ($variablenID, "WSS");
            $variablenID = $this->RegisterVariableFloat ("ID_Moisture".$zaehler, $zaehler."Feuchte", "", $zaehler*8+6);
            IPS_SetInfo ($variablenID, "WSS");
            $variablenID = $this->RegisterVariableInteger ("ID_State".$zaehler, $zaehler."Zustand", "", $zaehler*8+7);
            IPS_SetInfo ($variablenID, "WSS");
            $variablenID = $this->RegisterVariableString ("ID_Link".$zaehler, $zaehler."Image URL", "", $zaehler*8+8);
            IPS_SetInfo ($variablenID, "WSS");
        }
 
 

        
		 

        //Register Timer
        $this->RegisterTimer("updatePlant", 0, 'GS_Update($_IPS[\'TARGET\']);');

      
       




    } //Function: Create End
    /* 
    ------------------------------------------------------------ 
        Function: ApplyChanges  
        ApplyChanges() Wird ausgeführt, beim anlegen der Instanz.
        und beim ändern der Parameter in der Form
    -------------------------------------------------------------
    */
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();






        if($this->ReadPropertyBoolean("ID_active")){
            $this->SetBuffer("token", "");
            $this->SetBuffer("timestamp", "");
            $this->SetBuffer("valid", false);

            $this->FetchToken();
            $updateTime = $this->ReadPropertyInteger("ID_Interval");
            $this->SetTimerInterval("updatePlant", $updateTime);
        }
        else {
            //Timer ausschalten
            $this->SetTimerInterval("updatePlant", 0);
        }                   
    } //Function: ApplyChanges  End
    /* 
    ------------------------------------------------------------ 
        Function: Destroy  
            Destroy() wird beim löschen der Instanz 
            und update der Module aufgerufen
    -------------------------------------------------------------
    */
    public function Destroy() {
        //Never delete this line!
        parent::Destroy();
    } //Function: Destroy End
    /* 




















    /* 














    /* 





























_____________________________________________________________________________________________________________________
    Section: Public Functions
    Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt:
    
    FSSC_XYFunktion($Instance_id, ... );
________________________________________________________________________________________________________________________ 
*/
    //-----------------------------------------------------------------------------
    /* Function: FetchToken
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function FetchToken(){
        $url = "https://api.greensens.de";   
        $auth_url ="/api/users/authenticate";
        $path =$url.$auth_url;
        $login = $this->ReadPropertyString("ID_Login");
        $password = $this->ReadPropertyString("ID_Passwort");

        $curl = curl_init($path);
        curl_setopt($curl, CURLOPT_URL, $path);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
           "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
        //$data = '{"login":$login,"password":$password}';
        $data = array(
            "login" => $login,
            "password" => $password
        );

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        $obj= json_decode($resp);
        
        $token =  $obj->data->token;
        $timestamp = time();
        //Schreibt  in den Buffer "Databuffer"
        $this->SetBuffer("token", $token);
        $this->SetBuffer("timestamp", $timestamp);
        if($token != ""){
            $this->SetBuffer("valid", true);
            return $token;
        }
        return false;
    }  //End





    
    //-----------------------------------------------------------------------------
    /* Function: CheckValidToken
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function CheckValidToken(){
        $timestamp = $this->GetBuffer("timestamp");
        $aktTime = time();
        $time = $aktTime - $timestamp;
        // 7 Tage = 7*24*60*60  =  604800 Sekunden

        if(($aktTime - $timestamp) > 604800){
            $this->SetBuffer("valid", false); 
            $valid = false;
        }
        else{
            $this->SetBuffer("valid", true);
            $valid = true;
        }

        return $valid;
    }  //End

    
    //-----------------------------------------------------------------------------
    /* Function: GetPlantData
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function GetPlantData(){
        //prüfen ob t0ken noch aktuell ist
        $valid = $this->CheckValidToken();
        if($valid == false){
            $this->FetchToken();
            $valid = $this->CheckValidToken();
        }
        if($valid == true){
            $token = $this->GetBuffer("token");
            $url = "https://api.greensens.de/api/Plants";

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            
            if(curl_exec($curl) === false)
            {
                //echo 'Curl-Fehler: ' . curl_error($curl);
                if(curl_errno($curl))
                {
                   // echo 'cURL-Fehler: ' . curl_error($ch);
                   $errorno = curl_errno($curl);
                   $error_message = curl_strerror($errno);

                    $this->SendDebug("cURL error ({$errno}):\n {$error_message}","");
                }
            }
            else
            {
                $this->SendDebug('Operation ohne Fehler vollständig ausgeführt','');
            }



            $headers = array(
            "Accept: application/json",
            "Authorization: Bearer $token",
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            //for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            
            $resp = curl_exec($curl);
            curl_close($curl);
            //update data
            $data = json_decode($resp, true);
            $plantdata = $data['data']['registeredHubs'][0]['plants'];
            //Daten in Variablen schreiben
            $totalSensors = count($plantdata);
            for ($zaehler = 0; $zaehler <= $totalSensors-1; $zaehler++) {
                
                $this->SetValue("sensorID".$zaehler, $plantdata[$zaehler]['sensorID']);
                 
                $this->SetValue("sensorName".$zaehler, $plantdata[$zaehler]['plantNameDE']); 

                $this->SetValue("sensorStatus".$zaehler, $plantdata[$zaehler]['status']); 

                $this->SetValue("ID_Temp".$zaehler, $plantdata[$zaehler]['temperature']); 

                $this->SetValue("ID_Illumination".$zaehler, $plantdata[$zaehler]['illumination']); 

                $this->SetValue("ID_Moisture".$zaehler, $plantdata[$zaehler]['moisture']);  

                $this->SetValue("ID_State".$zaehler, $plantdata[$zaehler]['state']);  

                $this->SetValue("ID_Link".$zaehler, $plantdata[$zaehler]['link']);  
            }

            return $plantdata;
        }
        else{
            return false;
        }
    }  //End



    //-----------------------------------------------------------------------------
    /* Function: GetPlantData
    ...............................................................................
    Beschreibung
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * THS_Update($id);
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function Update()
    {
        $this->GetPlantData();
    }





/* 
_______________________________________________________________________
    Section: Private Funtions
    Die folgenden Funktionen sind nur zur internen Verwendung verfügbar
    Hilfsfunktionen
______________________________________________________________________
*/ 
    /* ----------------------------------------------------------------------------
    Function: createProfile
    ...............................................................................
    Erstellt ein neues Profil und ordnet es einer Variablen zu.
    ...............................................................................
    Parameters: 
        $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype, $VarIdent, $Assoc
     * $Vartype: 0 boolean, 1 int, 2 float, 3 string,
     * $Assoc: array mit statustexte
     *         $assoc[0] = "aus";
     *         $assoc[1] = "ein";
     *  
    ..............................................................................
    Returns:   
        none
    ------------------------------------------------------------------------------- */
    protected function createProfile(string $Name, int $Vartype, $Assoc, $Icon,  $Prefix,  $Suffix,   $MinValue,   $MaxValue,  $StepSize,  $Digits){
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, $Vartype); // 0 boolean, 1 int, 2 float, 3 string,
            if(!is_Null($Icon)){
                IPS_SetVariableProfileIcon($Name, $Icon);
            }
            if(!is_Null($Prefix)){
                IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
            }
            if(!is_Null($Digits)){
                IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
            }
            if(!is_Null($MinValue)){
                IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
            }
            if(!is_Null($Assoc)){
                foreach ($Assoc as $key => $data) {
                    if(is_null($data['icon'])){$data['icon'] = "";}; 
                    if(is_null($data['color'])){$data['color'] = "";}; 
                    IPS_SetVariableProfileAssociation($Name, $data['value'], $data['text'], $data['icon'], $data['color']);  
                }
            }
        } 
        else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != $Vartype){
                // $this->SendDebug("Alarm.Reset:", "Variable profile type does not match for profile " . $Name, 0);
            }
        }
    }   //Function: createProfile End
    
    
    
    /* ----------------------------------------------------------------------------
     Function: RegisterProfiles()
    ...............................................................................
        Profile für Variable anlegen falls nicht schon vorhanden
    ...............................................................................
    Parameters: 
        $Vartype => 0 boolean, 1 int, 2 float, 3 string
    ..............................................................................
    Returns:   
        $ipsversion
    ------------------------------------------------------------------------------- */
    protected function RegisterProfiles(){
       /*   Profile "UPNP_Browse";   
        $Assoc[0]['value'] = 0;
        $Assoc[1]['value'] = 1;
        $Assoc[2]['value'] = 2;
        $Assoc[3]['value'] = 3;
        $Assoc[0]['text'] = "Up";
        $Assoc[1]['text'] = "Select";
        $Assoc[2]['text'] = "Left";
        $Assoc[3]['text'] = "Right";
        $Assoc[0]['icon'] = NULL;
        $Assoc[1]['icon'] = NULL;
        $Assoc[2]['icon'] = NULL;
        $Assoc[3]['icon'] = NULL;
        $Assoc[0]['color'] = NULL;
        $Assoc[1]['color'] = NULL;
        $Assoc[2]['color'] = NULL;
        $Assoc[3]['color'] = NULL;
        $Name = "UPNP_Browse";
        $Vartype = 1;
        $Icon = NULL;
        $Prefix = NULL;
        $Suffix = NULL;
        $MinValue = 0;
        $MaxValue = 3;
        $StepSize = 1;
        $Digits = 0;
        if (!IPS_VariableProfileExists($Name)){
            $this->createProfile($Name, $Vartype,  $Assoc, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits);  
        }
        */
    } //Function: RegisterProfiles End

    /** Wird ausgeführt wenn der Kernel hochgefahren wurde. */
    protected function KernelReady(){
        $this->ApplyChanges();
    }
    /* ----------------------------------------------------------------------------
     Function: GetIPSVersion()
    ...............................................................................
        gibt die eingestezte IPS Version zurück
    ...............................................................................
    Parameters: 
        none
    ..............................................................................
    Returns:   
        $ipsversion
    ------------------------------------------------------------------------------- */
    protected function GetIPSVersion(){
        $ipsversion = floatval(IPS_GetKernelVersion());
        if ($ipsversion < 4.1) {    // 4.0
            $ipsversion = 0;
        } elseif ($ipsversion >= 4.1 && $ipsversion < 4.2){ // 4.1
            $ipsversion = 1;
        } elseif ($ipsversion >= 4.2 && $ipsversion < 4.3){ // 4.2
            $ipsversion = 2;
        } elseif ($ipsversion >= 4.3 && $ipsversion < 4.4){ // 4.3
            $ipsversion = 3;
        } elseif ($ipsversion >= 4.4 && $ipsversion < 5){ // 4.4
            $ipsversion = 4;
        } else {  // 5
            $ipsversion = 5;
        }
        return $ipsversion;
    } //Function: GetIPSVersion End
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
      $Typ         -   Typ des Events (0=ausgelöstes 1=cyclic 2=Wochenplan)
      $Parent      -   ID des Parents
      $Position    -   Position der Instanz
    ...............................................................................
    Returns:    
        none
    -------------------------------------------------------------------------------*/
    private function RegisterEvent($Name, $Ident, $Typ, $Parent, $Position){
        $eid = @$this->GetIDForIdent($Ident);
        if($eid === false) {
                $eid = 0;
        } elseif(IPS_GetEvent($eid)["EventType"] <> $Typ) {
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
    } //Function: RegisterEvent End

} //end Class


