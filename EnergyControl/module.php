<?php
/***************************************************************************
 * Title: EnergyControl
 *
 * Author: PiTo
 * 
 * GITHUB: <https://github.com/SymPiTo/MySymApps/tree/master/EnergyControl>
 * 
 * Version: 0.1
 *************************************************************************** */
//require_once __DIR__ . '/../libs/_TRAIT_';
require_once __DIR__ . '/../libs/traits.php';  // diverse Klassen
 
class MyEnergyControl extends IPSModule {

     use DebugHelper;
   // InstanceStatus,
   // BufferHelper,
   // Semaphore;
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
        $this->RegisterPropertyBoolean("ModAlive", false);

        //Listen Einträge als JSON regisrieren
        // zum umwandeln in ein Array 
        // $sensors = json_decode($this->ReadPropertyString("Battery"));
        $this->RegisterPropertyString("PraesenzS", "[]");
        $this->RegisterPropertyString("Heizung", "[]");
        $this->RegisterPropertyString("Licht", "[]");
        $this->RegisterPropertyString("Alarm", "[]");
        
       // $this->ReadPropertyFloat("NAME", 0.0);

       // $this->ReadPropertyInteger("NAME", 0);

       // $this->ReadPropertyString("NAME", "");


        // Register Profiles
       // $this->RegisterProfiles();
       
        //Register Variables
        $variablenID = $this->RegisterVariableBoolean ("StatWZ", "Wohnzimmer Person", '~Switch', 0);
        IPS_SetInfo ($variablenID, "WSS");
        
        $variablenID = $this->RegisterVariableBoolean ("StatSZ", "Schlafzimmer Person", '~Switch', 1);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableBoolean ("StatKZ", "Kinderzimmer Person", '~Switch', 2);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableBoolean ("StatD", "Diele Person", '~Switch', 3);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableBoolean ("StatAZ", "Arbeitszimmer Person", '~Switch', 4);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableBoolean ("StatK", "Küche Person", '~Switch', 5);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableBoolean ("StatWohn", "Wohnung Person", '~Switch', 5);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableInteger ("NrPerson", "Anzahl Person");
        IPS_SetInfo ($variablenID, "WSS");
        
        $variablenID = $this->RegisterVariableBoolean ("StatDoor", "Eingangstür", '~Switch', 6);
        IPS_SetInfo ($variablenID, "WSS");

        //Register Timer
        $this->RegisterTimer('T_WZ', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "WZ");');
        $this->RegisterTimer('T_SZ', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "SZ");');
        $this->RegisterTimer('T_KZ', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "KZ");');
        $this->RegisterTimer('T_D', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "D");');
        $this->RegisterTimer('T_AZ', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "AZ");');
        $this->RegisterTimer('T_K', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "K");');

        $this->RegisterTimer('T_Door', 0, 'EC_checkMovement($_IPS[\'TARGET\']);');

        $this->RegisterTimer('T_Appartment', 0, 'EC_emptyAppartment($_IPS[\'TARGET\']);');

        //IPS_SetInfo ($variablenID, "WSS");
        //$this->RegisterPropertyString("ID_Test", "MaxMustermann"); 



        /*
        $variablenID = $this->RegisterVariableFloat ($Ident, $Name, $Profil, $Position);
        IPS_SetInfo ($variablenID, "WSS");
        IPS_SetHidden($variablenID, true); //Objekt verstecken

        $variablenID = $this->RegisterPropertyInteger ($Name, $Standardwert, $Profil, $Position);
        IPS_SetInfo ($variablenID, "WSS");
        IPS_SetHidden($variablenID, true); //Objekt verstecken

        $variablenID = $this->RegisterPropertyString ($Name, $Standardwert, $Profil, $Position);
        IPS_SetInfo ($variablenID, "WSS");
        IPS_SetHidden($variablenID, true); //Objekt verstecken



        // Verbinde mit neu erstellten Splitter, falls noch keine Verbindung besteht
        $this->RequireParent("{_GUID-SPLITTER_}");
        // Verbinde mit neu erstellten IO, falls noch keine Verbindung besteht
        $this->RequireParent("{_GUID-IO_}");


        //Webfront Actions setzen
        $this->EnableAction("IDENT der registrierten Variable");
*/

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
        $this->SetBuffer("buffer_cM", 0);

        if($this->ReadPropertyBoolean("ModAlive")){
            $arrString = $this->ReadPropertyString("PraesenzS");
            $arr = json_decode($arrString);
            //Messages registrieren
            foreach ($arr as $key => $value) {
                $this->RegisterMessage($value->ID, VM_UPDATE);
            }
            
            #Variablen zurücksetzen
            $this->SetValue("StatWZ", false);
            $this->SetValue("StatSZ", false);
            $this->SetValue("StatKZ", false);
            $this->SetValue("StatK", false);
            $this->SetValue("StatD", false);
            $this->SetValue("StatAZ", false);
            $this->SetValue("StatWohn", false);

            //Timer ausschalten
            $this->SetTimerInterval("T_WZ", 0);
            $this->SetTimerInterval("T_SZ", 0);
            $this->SetTimerInterval("T_KZ", 0);
            $this->SetTimerInterval("T_AZ", 0);
            $this->SetTimerInterval("T_D", 0);
            $this->SetTimerInterval("T_K", 0);
            $this->SetTimerInterval("T_Appartment, 0");
        }
        else {
            //Timer ausschalten
   //         $this->SetTimerInterval("Name", 0);
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
    ------------------------------------------------------------ 
        Function: RequestAction  
            RequestAction() wird von schaltbaren Variablen 
            aufgerufen.
    -------------------------------------------------------------
    */ 
//    public function RequestAction($Ident, $Value) {     
     /*
        switch($Ident) {
            case "IDENT_Variable":
                if ($Value == true){ 

                }
                else {

                }
                break;
            default:
                throw new Exception("Invalid Ident");
            }
    */
  //  } //Function: RequestAction End
     
    /*------------------------------------------------------------ 
        Function: MessageSink  
        MessageSink() wird nur bei registrierten 
        NachrichtenIDs/SenderIDs-Kombinationen aufgerufen. 
    -------------------------------------------------------------*/
   public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        $this->SendDebug("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message);
        switch ($Message) {
            case VM_UPDATE:
                
                $arrString = $this->ReadPropertyString("PraesenzS");
                $arr = json_decode($arrString);
                foreach ($arr as $key => $value) {
                    $this->SendDebug($value->ID," == ".$SenderID);
                    if($value->ID == $SenderID){
                        $this->setRoomStat($arr[$key]->Raum, $SenderID);
                    }
                }
                break;
            default:
                # code...
                break;
        }
    } //Function: MessageSink End
    
 
/* 
_____________________________________________________________________________________________________________________
    Section: Public Funtions
    Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt:
    
    FSSC_XYFunktion($Instance_id, ... );
________________________________________________________________________________________________________________________ 
*/
    //-----------------------------------------------------------------------------
    /* Function: xxxx
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function setRoomStat(string $room, int $id){
        $this->SendDebug("setRoomStat: ",$room." - ".$id);
        switch ($room) {
            case "Wohnzimmer":
                # Person detektiert - Raum setzen/timer setzen
                # wenn Daten  = true, RaumVariable setzen
                # wenn Daten = false, Timer starten und bei Ablauf Raum auf 0 setzen
                if(GetValue($id) == true){
                    $this->SetValue("StatWZ", true);
                    $this->SetValue("StatWohn", true);
                }
                else{
                    #WZ Timer starten 5min = 5*60000 = 300 000
                    $this->SetTimerInterval("T_WZ", 300000);
                } 
                break;
            case "Kinderzimmer":
                if(GetValue($id) == true){
                    $this->SetValue("StatKZ", true);
                    $this->SetValue("StatWohn", true);
                }
                else{
                    $this->SetTimerInterval("T_KZ", 300000);
                }
                
                break;
            case "Schlafzimmer":
                if(GetValue($id) == true){
                    $this->SetValue("StatSZ", true);
                    $this->SetValue("StatWohn", true);
                }
                else{
                    $this->SetTimerInterval("T_SZ", 300000);
                }
                
                break;
            case "Küche":
                if(GetValue($id) == true){
                    $this->SetValue("StatK", true);
                    $this->SetValue("StatWohn", true);
                }
                else{
                    $this->SetTimerInterval("T_K", 300000);
                }
                
                break;   
            case "Diele":
                if(GetValue($id) == true){
                    $this->SetValue("StatD", true);
                    $this->SetValue("StatWohn", true);
                }
                else{
                    $this->SetTimerInterval("T_D", 300000);
                }
                
                break;   
            case "Arbeitszimmer":
                if(GetValue($id) == true){
                    $this->SetValue("StatAZ", true);
                    $this->SetValue("StatWohn", true);
                }
                else{
                    $this->SetTimerInterval("T_AZ", 300000);
                }
                
                break;  
            case "Eingangstür":
                if(GetValue($id) == true){
                    #Eingangstür öffnet
                    #nur prüfen ob Wohnung leer war, dann kommt einer rein
                    if($this->GetValue("StatDoor") == false){
                        $this->SetValue("StatDoor", true);

                       

                    }
                
                }
                else{
                    #Tür war auf und geht wieder zu
                    if($this->GetValue("StatDoor") == true){
                        #prüfen ob in Diele Bewegung erkannt wird
                        $this->SetTimerInterval("T_Door", 10000);
                    }
                }
                break;                        
            default:
                # code...
                break;
        }
    }  //xxxx End

    //-----------------------------------------------------------------------------
    /* Function: checkMovement
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function checkMovement(){
        if($this->GetBuffer("buffer_cM")>120){
            if($StatD){

                $this->SetValue("NrPerson", $no);
                SetBuffer("buffer_PersIn");
            }
            SetBuffer("buffer_cM",$this->GetBuffer("buffer_cM")+1);
        }
        else{
            #timer ausschalten und auswerten
            $this->SetTimerInterval("T_Door", 0);
            if($this->GetBuffer("buffer_PersIn")){
                #Person kam rein
                $no = $this->GetValue("NrPerson");
                $no = $No + 1;
                if($no<0){
                    $no = 0;
                }
            }
            else{
                #Person ging raus
                $no = $this->GetValue("NrPerson");
                $no = $no - 1;
                if($no<0){
                    $no = 0;
                }
            }
            $this->SetValue("StatDoor", false);
            $this->SetBuffer("buffer_cM", 0);
        }
    }

    //-----------------------------------------------------------------------------
    /* Function: xxxx
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function checkEvent($room){
        #Timer ist abgelaufen RaumStatus auf false setzen - keine Person im Raum seit 5 Minuten
        switch ($room) {
            case "WZ":
                $this->SetValue("StatWZ", false);
                $this->SetTimerInterval("T_WZ", 0);
                break;
            case "KZ":
                $this->SetValue("StatKZ", false);
                $this->SetTimerInterval("T_KZ", 0);
                break;
            case "SZ":
                $this->SetValue("StatSZ", false);
                $this->SetTimerInterval("T_SZ", 0);
                break;
            case "K":
                $this->SetValue("StatK", false);
                $this->SetTimerInterval("T_K", 0);
                break;   
            case "D":
                $this->SetValue("StatD", false);
                $this->SetTimerInterval("T_D", 0);
                break;   
            case "AZ":
                $this->SetValue("StatAZ", false);
                $this->SetTimerInterval("T_AZ", 0);
                break;                          
            default:
                # code...
                break;
        }

        #prüfen ob Wohnung leer
        if(!$this->GetValue("StatWZ") AND !$this->GetValue("StatKZ") AND !$this->GetValue("StatSZ") AND !$this->GetValue("StatAZ") AND !$this->GetValue("StatK") AND !$this->GetValue("StatD") ){
            $this->setTimerInterval('T_Appartment', 30000); //Timer auf 5 Minuten setzen

            
            #Wohnung ist leer nun können Licht ausgeschalten
            #Temperatur runtergeregelt
            #Alarmanlage aktiviert
            #werden


        }

    }
    //-----------------------------------------------------------------------------
    /* Function: xxxx
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function emptyAppartment(){
        $this->setValue("StatWohn", false);
        $this->SetValue("NrPerson", 0);
    }


/* 
_______________________________________________________________________
<<<<<<< Updated upstream
    Section: Private Funtions
=======
    Section: Private Functions
>>>>>>> Stashed changes
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
    }	//Function: createProfile End
    
    
    
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
