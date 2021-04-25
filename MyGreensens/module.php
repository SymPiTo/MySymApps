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
        $this->ReadPropertyString("ID_Login", "");
        $this->ReadPropertyString("ID_Passwort", "");

        // Register Profiles
        //$this->RegisterProfiles();

        //Register Variables
        for ($zaehler = 1; $zaehler <= $totalSensors; $zaehler++) {
            $variablenID = $this->RegisterVariableInteger ("sensorID", "Sensor ID", "" , 0);
            IPS_SetInfo ($variablenID, "");
            $variablenID = $this->RegisterVariableString ("sensorName", "Pflanzen Name", "", 0); 
            IPS_SetInfo ($variablenID, "");
            $variablenID = $this->RegisterVariableBoolean ("sensorStatus", "Sensor Status", "", 0);
            IPS_SetInfo ($variablenID, "");
            $variablenID = $this->RegisterVariableFloat ("ID_Temp", "Temperatur", "", 0);
            IPS_SetInfo ($variablenID, "WSS");
            $variablenID = $this->RegisterVariableFloat ("ID_Illumination", "Helligkeit", "", 0);
            IPS_SetInfo ($variablenID, "WSS");
            $variablenID = $this->RegisterVariableFloat ("ID_Moisture", "Feuchte", "", 0);
            IPS_SetInfo ($variablenID, "WSS");
        }
 
 

 
		 

        //Register Timer
        //$this->RegisterTimer('Name', 0, '_PREFIX__Scriptname($_IPS[\'TARGET\']);');








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
            //Splitter oder IO verbinden

            //Filter setzen – ForwardData wird nur aufgerufen wenn Filter passt (string $ErforderlicheRegexRegel )$this->SetForwardDataFilter(".*");  
            //Filter setzen – ReceiveData wird nur aufgerufen wenn Filter passt (string $ErforderlicheRegexRegel )$this->SetReceiveDataFilter(".*");  
        }
        else {
            //Timer ausschalten
            //$this->SetTimerInterval("Name", 0);
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
    Section: Public Funtions
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
        $login = getvalue($this->ReadPropertyString("ID_Login"));
        $password = getvalue($this->ReadPropertyString("ID_Passwort"));

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
        return $token;
    }  //End

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


