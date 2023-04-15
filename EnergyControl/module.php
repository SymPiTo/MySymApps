<?php
/***************************************************************************
 * Title: EnergyControl
 *
 * Author: PiTo
 * 
 * GITHUB: <https://github.com/SymPiTo/MySymApps/tree/master/EnergyControl>
 * 
 * Version: 0.1  20220604
 *************************************************************************** */

#___________________________________________________________________________ 
#    Section: Beschreibung
#    Das Modul dient zum automatisieren von Vorgängen, um Energie zu sparen.
#    Über Detector Sensoren wird erfasst welche Räume bzw. Wohnung leer ist.
#    In Abhängigkeit davon werden bestimmte Aktionen ausgelöst.
#    
#___________________________________________________________________________ 


require_once __DIR__ . '/../libs/MyHelper.php';  // diverse Klassen

class MyEnergyControl extends IPSModule {

     use DebugHelper;
     use EventHelper;
 

# ___________________________________________________________________________ 
#    Section: Internal Modul Functions
#    Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung.
# ___________________________________________________________________________ 

     
    #-----------------------------------------------------------# 
    #    Function: Create                                       #
    #    Create() Wird ausgeführt, beim Anlegen der Instanz.    #
    #-----------------------------------------------------------#    
    public function Create() {
    //Never delete this line!
        parent::Create();
        #REGISTER PROPERTIES
        //Register Properties from form.json
        $this->RegisterPropertyBoolean("ModAlive", false);

        //Listen Einträge als JSON registrieren
        // zum umwandeln in ein Array 
        // $sensors = json_decode($this->ReadPropertyString("Battery"));
        $this->RegisterPropertyString("PraesenzS", "[]");
        $this->RegisterPropertyString("Heizung", "[]");
        $this->RegisterPropertyString("Licht", "[]");
        $this->RegisterPropertyString("Alarm", "[]");
        
       // $this->ReadPropertyFloat("NAME", 0.0);

       $this->RegisterPropertyInteger("BLE1", 0);
       $this->RegisterPropertyInteger("BLE2", 0);
       $this->RegisterPropertyInteger("Handy1", 0);
       $this->RegisterPropertyInteger("Handy2", 0);

       // $this->ReadPropertyString("NAME", "");


        // Register Profiles
       // $this->RegisterProfiles();
       
        #REGISTER VARIABLES
        $variablenID = $this->RegisterVariableBoolean ("PierreAtHome", "Pierre is da", '~Switch', 0);
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableBoolean ("TorstenAtHome", "Torsten is da", '~Switch', 0);
        IPS_SetInfo ($variablenID, "WSS");

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

        #REGISTER TIMER 
        $this->RegisterTimer('T_WZ', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "WZ");');
        $this->RegisterTimer('T_SZ', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "SZ");');
        $this->RegisterTimer('T_KZ', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "KZ");');
        $this->RegisterTimer('T_D', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "D");');
        $this->RegisterTimer('T_AZ', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "AZ");');
        $this->RegisterTimer('T_K', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "K");');

        $this->RegisterTimer('T_Door', 0, 'EC_checkMovement($_IPS[\'TARGET\']);');

        $this->RegisterTimer('T_Wohn', 0, 'EC_checkEvent($_IPS[\'TARGET\'], "Wohn");');



    } //Function: Create End

    #---------------------------------------------------------------#
    #     Function: ApplyChanges                                    #
    #     ApplyChanges() Wird ausgeführt, beim anlegen der Instanz. #
    #     und beim ändern der Parameter in der Form                 #
    #---------------------------------------------------------------#

    public function ApplyChanges(){
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        //Never delete this line!
        parent::ApplyChanges();

        # Register Events
 
        if($this->ReadPropertyInteger("BLE2") >0){
            //Event bei Änderung der Variablen "BLE1"
            $EventName = "BLE2Evnt";
            $varID = $this->ReadPropertyInteger("BLE2");
            $Ident = "IDBLE2Evnt";
            $ParentID = $varID; //Event unter die Variable hängen
            $cmd = "EC_PierreAtHome(".$this->InstanceID.");" ;
            $EventID = $this->RegisterVarEvent($EventName, $Ident, 0, $ParentID, 0, 1, $varID,  $cmd); 
         }
         if($this->ReadPropertyInteger("BLE1") >0){
            //Event bei Änderung der Variablen "BLE2"
            $EventName = "BLE1Evnt";
            $varID = $this->ReadPropertyInteger("BLE1");
            $Ident = "IDBLE1Evnt";
            $ParentID = $varID; //Event unter die Variable hängen
            $cmd = "EC_TorstenAtHome(".$this->InstanceID.");" ;
            $EventID = $this->RegisterVarEvent($EventName, $Ident, 0, $ParentID, 0, 1, $varID,  $cmd); 
         }

         if($this->ReadPropertyInteger("Handy1") >0){
            //Event bei Änderung der Variablen "Handy1"
            $EventName = "Handy1Evnt";
            $varID = $this->ReadPropertyInteger("Handy1");
            $Ident = "IDHandy1Evnt";
            $ParentID = $varID; //Event unter die Variable hängen
            $cmd = "EC_TorstenAtHome(".$this->InstanceID.");" ;
            $EventID = $this->RegisterVarEvent($EventName, $Ident, 0, $ParentID, 0, 1, $varID,  $cmd); 
         }
         if($this->ReadPropertyInteger("Handy2") >0){
            //Event bei Änderung der Variablen "Handy2"
            $EventName = "Handy2Evnt";
            $varID = $this->ReadPropertyInteger("Handy2");
            $Ident = "IDHandy2Evnt";
            $ParentID = $varID; //Event unter die Variable hängen
            $cmd = "EC_PierreAtHome(".$this->InstanceID.");" ;
            $EventID = $this->RegisterVarEvent($EventName, $Ident, 0, $ParentID, 0, 1, $varID,  $cmd); 
         }


         #Ein-Ausgangszähler
        $this->SetBuffer("buffer_cM", 0);

        # Messages deregistrieren
        $MessageList = $this->GetMessageList();

        //$this->UnregisterMessage(10603);

        $arrString = $this->ReadPropertyString("PraesenzS");
        $arr = json_decode($arrString);
        foreach ($arr as $key => $value) {
            //$this->SendDebug($value->ID," == ".$SenderID);
            $this->UnregisterMessage($value->ID, VM_UPDATE);
        }

        if($this->ReadPropertyBoolean("ModAlive")){
            $arrString = $this->ReadPropertyString("PraesenzS");
            $arr = json_decode($arrString);
            //Messages registrieren
            foreach ($arr as $key => $value) {
                $this->RegisterMessage($value->ID, VM_UPDATE);
            }
        }
        else {
            //Timer ausschalten
             //$this->SetTimerInterval("Name", 0);
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
 				
            $this->PierreAtHome();
            $this->TorstenAtHome();
    } //Function: ApplyChanges  End


    #------------------------------------------------------------# 
    #  Function: MessageSink                                     #
    #  MessageSink() wird nur bei registrierten                  #
    #  NachrichtenIDs/SenderIDs-Kombinationen aufgerufen.        #
    #------------------------------------------------------------#
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        //$this->SendDebug("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message);

        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
            case VM_UPDATE:
                
                $MessageList = $this->GetMessageList();
                
                $this->sendDebug("Messagelist: ",$MessageList, 0);

                $arrString = $this->ReadPropertyString("PraesenzS");
                $arr = json_decode($arrString);

                foreach ($arr as $key => $value) {
                   //$this->SendDebug($value->ID," == ".$SenderID);

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




    #-------------------------------------------------------------#
    #    Function: Destroy                                        #
    #        Destroy() wird beim löschen der Instanz              #
    #        und update der Module aufgerufen                     #
    #-------------------------------------------------------------#
    public function Destroy() {
        //Never delete this line!
        parent::Destroy();
    } //Function: Destroy End
    
    #------------------------------------------------------------# 
    #    Function: RequestAction                                 #
    #        RequestAction() wird von schaltbaren Variablen      #
    #        aufgerufen.                                         #
    #------------------------------------------------------------#
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
     
 

 
 

    
 
#_________________________________________________________________________________________________________
# Section: Public Functions
#    Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" 
#    eingefügt wurden.
#    Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur 
#    Verfügung gestellt:
#_________________________________________________________________________________________________________
    #---------------------------------------------------------------------------------#
    # Function: PierreAtHome                                                          #
    #.................................................................................#
    # Beschreibung:                                                                   #
    #.................................................................................#
    # Parameters:                                                                     #
    #.................................................................................#
    # Returns:                                                                        #
    #---------------------------------------------------------------------------------#
    public function PierreAtHome(){
        $this->sendDebug("PierreAtHome: ","Event wurde ausgelöst", 0);
        # prüfen og BLE oder Handy eingeloggt.
        $BLE2 = $this->ReadPropertyInteger('BLE2');
        $Handy2 = $this->ReadPropertyInteger('Handy2');
        $BLE_P = false;
        $Handy_P = false;
        if($BLE2 > 0){
            $BLE_P = GetValue($BLE2);
        }
        if($Handy2 > 0){
            $Handy_P = GetValue($Handy2);
        }
        if($BLE_P or $Handy_P){
            $this->SetValue('PierreAtHome', true);
        } 
        if(!$BLE_P and !$Handy_P){
            $this->SetValue('PierreAtHome', false); 
        }
    }

    #---------------------------------------------------------------------------------#
    # Function: TorstenAtHome                                                          #
    #.................................................................................#
    # Beschreibung:                                                                   #
    #.................................................................................#
    # Parameters:                                                                     #
    #.................................................................................#
    # Returns:                                                                        #
    #---------------------------------------------------------------------------------#
    public function TorstenAtHome(){
        $this->sendDebug("TorstenAtHome: ","Event wurde ausgelöst", 0);
        # prüfen og BLE oder Handy eingeloggt.
        $BLE1 = $this->ReadPropertyInteger('BLE1');
        $Handy1 = $this->ReadPropertyInteger('Handy1');
        $BLE_T = false;
        $Handy_T = false;
        if($BLE1 > 0){
            $BLE_T = GetValue($BLE1);
        }
        if($Handy1 > 0){
            $Handy_T = GetValue($Handy1);
        }
        if($BLE_T or $Handy_T){
            $this->SetValue('TorstenAtHome', true);
        } 
        if(!$BLE_T and !$Handy_T){
            $this->SetValue('TorstenAtHome', false);
        }
    }

    #---------------------------------------------------------------------------------#
    # Function: Wohnung Leer                                                          #
    #.................................................................................#
    # Beschreibung:                                                                   #
    #.................................................................................#
    # Parameters:                                                                     #
    #.................................................................................#
    # Returns:                                                                        #
    #---------------------------------------------------------------------------------#
    public function ApertmentEmty(){
        # Wohnung ist leer -> Alle Verbraucher ausschalten
        
    }



    #---------------------------------------------------------------------------------#
    # Function: setRoomStat                                                           #
    #.................................................................................#
    # Beschreibung:                                                                   #
    #    Wurde über MessageSink eine Änderung der Registrierten Variable (Detector)   #
    #    erkannt wird dieses Unterprogramm gestartet.                                 #
    #            * Person detektiert -                                                #
    #                Raum setzen                                                      #
    #                Raum-Timer nachstarten                                           #
    #                Wohnung setzen                                                   #
    #                Wohnungs-Timer nachstarten.                                      #
    #.................................................................................#
    # Parameters:                                                                     #
    #    room - Raum Detector angesprochen                                            #
    #    id   - ID der Raum Variablen                                                 #
    #.................................................................................#
    # Returns:                                                                        #
    #    none                                                                         #
    #---------------------------------------------------------------------------------#
    public function setRoomStat(string $room, int $id){
        //$this->SendDebug("setRoomStat: ",$room." - ".$id);
        switch ($room) {
            case "Wohnzimmer":
                    $this->SetValue("StatWZ", true);  
                    #WZ Timer starten 5min = 5*60000 = 300 000
                    $this->SetTimerInterval("T_WZ", 300000);
                                    #Wohnungs-Timer nachstarten
                                    $this->SetValue("StatWohn", true);
                                    #Timer nachstarten
                                    $this->SetTimerInterval("T_Wohn", 0);
                                    $this->SetTimerInterval("T_Wohn", 900000);
                break; 
            case "Kinderzimmer":            
                    $this->SetValue("StatKZ", true);     
                    $this->SetTimerInterval("T_KZ", 300000);
                    $this->SetValue("StatWohn", true);
                    #Timer nachstarten
                    $this->SetTimerInterval("T_Wohn", 0);
                    $this->SetTimerInterval("T_Wohn", 900000);         
                break;
            case "Schlafzimmer":            
                    $this->SetValue("StatSZ", true);          
                    $this->SetTimerInterval("T_SZ", 300000);
                    $this->SetValue("StatWohn", true);
                    #Timer nachstarten
                    $this->SetTimerInterval("T_Wohn", 0);
                    $this->SetTimerInterval("T_Wohn", 900000);           
                break;
            case "Küche":             
                    $this->SetValue("StatK", true);
                    $this->SetValue("StatWohn", true);        
                    $this->SetTimerInterval("T_K", 300000);
                        #Timer nachstarten
                        $this->SetTimerInterval("T_Wohn", 0);
                        $this->SetTimerInterval("T_Wohn", 900000);           
                break;   
            case "Diele":           
                    $this->SetValue("StatD", true);         
                    $this->SetTimerInterval("T_D", 300000);
                    $this->SetValue("StatWohn", true);
                    #Timer nachstarten
                    $this->SetTimerInterval("T_Wohn", 0);
                    $this->SetTimerInterval("T_Wohn", 900000);        
                break;   
            case "Arbeitszimmer":   
                    $this->SetValue("StatAZ", true);
                    $this->SetValue("StatWohn", true);
                    $this->SetTimerInterval("T_AZ", 300000);
                          #Timer nachstarten
                          $this->SetTimerInterval("T_Wohn", 0);
                          $this->SetTimerInterval("T_Wohn", 900000);
                break;  
            case "Eingangstür":
          
    //wenn zuerst Türsensor vor Dielendetector anspricht, dann kommt Person rein sonst raus.
    if(GetValue(36168) == false){
        SetValue(13087, GetValue(13087) + 1);
                }
                else{
                    $counter = GetValue(13087) - 1;
                    if ($counter < 0) {$counter = 0;} 
                    SetValue(13087, $counter );
                }
                

                break;                        
            default:
                # code...
                break;
        }
    }  //xxxx End


    #---------------------------------------------------------------------------------#
    # Function: checkMovement                                                         #
    #.................................................................................#
    # Beschreibung:                                                                   #
    #.................................................................................#
    # Parameters:                                                                     #
    #.................................................................................#
    # Returns:                                                                        #
    #---------------------------------------------------------------------------------#
    public function checkMovement(){
        if($this->GetBuffer("buffer_cM")>120){
            if($StatD){

                //$this->SetValue("NrPerson", $no);
                SetBuffer("buffer_PersIn");
            }
            SetBuffer("buffer_cM",$this->GetBuffer("buffer_cM")+1);
        }
        else{
            #timer ausschalten und auswerten
            $this->SetTimerInterval("T_Door", 0);
            if($this->GetBuffer("buffer_PersIn")){
                #Person kam rein
                //$no = $this->GetValue("NrPerson");
                $no = $No + 1;
                if($no<0){
                    $no = 0;
                }
            }
            else{
                #Person ging raus
                //$no = $this->GetValue("NrPerson");
                $no = $no - 1;
                if($no<0){
                    $no = 0;
                }
            }
            $this->SetValue("StatDoor", false);
            $this->SetBuffer("buffer_cM", 0);
        }
    }

   

    #---------------------------------------------------------------------------------#
    # Function: checkEvent                                                            #
    #.................................................................................#
    # Beschreibung:                                                                   #
    #   Funktion wird von den Timern aufgerufen, wenn die Timer Zeit abgelaufen ist.  #
    #   Der entsprechende Raum wird dann zurückgesetzt. => Raum ist leer.             #
    #   Der zugehörige Timer wird abgeschaltet.                                       #                        
    #.................................................................................#
    # Parameters:    $room                                                            #
    #.................................................................................#
    # Returns:      none                                                              #
    #---------------------------------------------------------------------------------#
    public function checkEvent($room){
        #Timer ist abgelaufen RaumStatus auf false setzen - keine Person im Raum seit 5 Minuten
        switch ($room) {
            case "Wohn":
                $this->SetValue("StatWohn", false);
                #nur zurücksetzen wenn alle Räume inaktiv und Wohn Timer abgelaufen
                if(!$this->GetValue("StatWZ") AND !$this->GetValue("StatKZ") AND !$this->GetValue("StatSZ") AND !$this->GetValue("StatK") AND !$this->GetValue("StatD") AND !$this->GetValue("StatAZ")){
                    $this->SetTimerInterval("T_Wohn", 0);
                    //$this->SetValue("NrPerson", 0);
                } 
                break; 
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
      if($this->GetValue("StatWohn") == false) {

        }       
            #Wohnung ist leer nun können Licht ausgeschalten
            #Temperatur runtergeregelt
            #Alarmanlage aktiviert
            #werden



    
        }

    


#________________________________________________________________________________________
# Section: Private Functions
#    Die folgenden Funktionen stehen nur innerhalb des Moduls zur verfügung
#    Hilfsfunktionen: 
#_______________________________________________________________________________________


 
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
        return $eid = @$this->GetIDForIdent($Ident);
    } //Function: RegisterEvent End


} //end Class
