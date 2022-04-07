<?php
/***************************************************************************
 * Title: EnergyControl
 *
 * Author: _AUTOR_
 * 
 * GITHUB: <https://github.com/SymPiTo/MySymApps/tree/master/EnergyControl>
 * 
 * Version: _VERSION_
 *************************************************************************** */
//require_once __DIR__ . '/../libs/_TRAIT_';
//require_once __DIR__ . '/../libs/_HELPERCLASS_';  // diverse Klassen

class MyEnergyControl extends IPSModule {

   // use DebugHelper,
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

       // $this->ReadPropertyFloat("NAME", 0.0);

       // $this->ReadPropertyInteger("NAME", 0);

       // $this->ReadPropertyString("NAME", "");


        // Register Profiles
       // $this->RegisterProfiles();
       
        //Register Variables
        $variablenID = $this->RegisterVariableBoolean ("StatRoom1", "Raum1 Person", '~Switch', 0);
        IPS_SetInfo ($variablenID, "WSS");
        
        $variablenID = $this->RegisterVariableBoolean ("StatRoom2", "Raum2 Person", '~Switch', 1);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableBoolean ("StatRoom3", "Raum3 Person", '~Switch', 2);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableBoolean ("StatRoom4", "Raum4 Person", '~Switch', 3);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableBoolean ("StatRoom5", "Raum5 Person", '~Switch', 4);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableBoolean ("StatRoom6", "Raum6 Person", '~Switch', 5);
        IPS_SetInfo ($variablenID, "WSS");

   
        $variablenID = $this->RegisterPropertyInteger ("ID_No", 999, , 6);
        IPS_SetInfo ($variablenID, "WSS");

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

        //Register Timer
        $this->RegisterTimer('Name', 0, '_PREFIX__Scriptname($_IPS[\'TARGET\']);');

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

        //Messages registrieren
 //       $this->RegisterMessage(0, IPS_KERNELSTARTED);
 //       $this->RegisterMessage($this->InstanceID, FM_CONNECT);
 //       $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);

        if($this->ReadPropertyBoolean("ModAlive")){
            //Splitter oder IO verbinden
 //           $this->ConnectParent("{8AA55C67-B28A-C67B-5332-99CCE8190ACA}");
            //Filter setzen – ForwardData wird nur aufgerufen wenn Filter passt (string $ErforderlicheRegexRegel )$this->SetForwardDataFilter(".*");  
            //Filter setzen – ReceiveData wird nur aufgerufen wenn Filter passt (string $ErforderlicheRegexRegel )$this->SetReceiveDataFilter(".*");  
            $arrString = $this->ReadPropertyString("PraesenzS");
            $arr = json_decode($arrString);
            $this->SetValue("ID_Test", $arr[0]);
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
        //IPS_LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));
        switch ($Message) {
            case IPS_KERNELSTARTED:
      //          $this->KernelReady();
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
    public function xxxx(){
       
    }  //xxxx End

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
