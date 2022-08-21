<?php
// require_once(__DIR__ . "/../libs/SymconLib.php");
require_once(__DIR__ . "/../libs/NetworkTraits1.php");
require_once __DIR__.'/../libs/MyHelper.php';  // diverse Klassen

/* ============================================================================
 * Title: Alarm for MyIPS
 * author PiTo
 * 
 * GITHUB = <https://github.com/SymPiTo/MySymCodes/tree/master/MyIPSAlarm>
 * 
 * Version:1.0.2019.02.02
 =============================================================================== */
//Class: MyAlarm
class MyAlarm extends IPSModule
{
   //externe Klasse einbinden - ueberlagern mit TRAIT.
    use DebugHelper, 
    ModuleHelper,
    EventHelper,
    VersionHelper,
    ProfileHelper;
    
# ___________________________________________________________________________ 
#    Section: Internal Modul Functions
#    Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung.
# ___________________________________________________________________________ 

  
    #-----------------------------------------------------------# 
    #    Function: Create                                       #
    #    Create() Wird ausgeführt, beim Anlegen der Instanz.    #
    #-----------------------------------------------------------#    
    
/*
    CONFIG-Properties:
     * Battery         -   list json array of Battery Sensors
     * SecAlarms       -   list json array of Alarm Sensors
     * WaterSensors    -   list json array of Water Sensors
     * Password        -   String password for Alarm Code
     * EchoID          -   ID des Moduls Echo Remote
     * TelegramID      -   ID des Moduls Telegram Messenger
     * SenderID        -   Sender ID für Telegram Bot
     * AlexaTTS        -   Modul Echo Remote verwenden ja/nein (bool)
     * Telegram        -   Modul Telegram Messenger verwenden ja/nein (bool)
     * A_Webfront      -   WebFront Links anlegen ja/nein (bool)
     * A_BatAlarm      -   Meldetext (string)
     * A_WaterAlarm    -   Meldetext (string)
     * A_SecCode       -   Meldetext (string)
     * A_SecWarning    -   Meldetext (string)
    
    IPS Variable:
     * A_SecActive          -   Status Alarmanlage aktiv (Bool) Alarmanlage hat ausgelöst
     *  
    IPS Action Variable:
     * A_AlarmCode          -   Alarm.Code (integer)
     * A_SecActivate        -   Action Variable activate Alarmanlage (Bool)
     * Alexa_SecActivate    -   Alexa Trigger "aktiviere"Alexa schalte Alarmanlage ein" (bool)

    ------------------------------------------------------------- */
    public function Create()
    {
	//Never delete this line!
        parent::Create();
         
         // Variable aus dem Instanz Formular registrieren (zugänglich zu machen)
         // Aufruf dieser Form Variable mit  §this->ReadPropertyFloat(-IDENTNAME-)
        //$this->RegisterPropertyInteger(!IDENTNAME!, 0);
        //$this->RegisterPropertyFloat(!IDENTNAME!, 0.5);
        //$this->RegisterPropertyBoolean(!IDENTNAME!, false);
        
        //Listen Einträge als JSON regisrieren
        // zum umwandeln in ein Array 
        // $sensors = json_decode($this->ReadPropertyString("Battery"));
            $this->RegisterPropertyString("Battery", "[]");
            $this->RegisterPropertyString("SecAlarms", "[]");
            $this->RegisterPropertyString("WaterSensors", "[]");
            $this->RegisterPropertyString("Password", "");
            $this->RegisterPropertyString("WinOpen", "[]");

  
            $this->RegisterPropertyString("FTPPassword", "");
          


        //Profil anlegen //A_SecActivate
        $assoc[0]['value'] = "aus";
        $assoc[1]['value'] = "ein";
        $assoc[0]['icon'] =  NULL;
        $assoc[1]['icon'] =  NULL;
        $assoc[0]['color'] = "0xFFFF00";
        $assoc[1]['color'] = "0xFFA500";
        $name = "Alarm.Activate";
        $vartype = 0;  //bool
        $icon = NULL;
        $prefix = NULL;
        $suffix = NULL;
        $this->RegisterProfile($vartype, $name, $icon, $prefix, $suffix, "", "", "", "", $assoc);

      
        //Profil anlegen //A_SecActivate
        $assoc[0]['value'] = "deaktiviert";
        $assoc[1]['value'] = "aktiviert";
        $assoc[0]['icon'] =  NULL;
        $assoc[1]['icon'] =  NULL;
        $assoc[0]['color'] = "0xFFFF00";
        $assoc[1]['color'] = "0xFFA500";
        $name = "Alarm.Active";
        $vartype = 0;  //bool        
        $this->RegisterProfile($vartype, $name, "", "", "", "", "", "", "", $assoc);   





        //Integer Variable anlegen
        //integer RegisterVariableInteger ( string §Ident, string §Name, string §Profil, integer §Position )
        // Aufruf dieser Variable mit $his->GetIDForIdent("IDENTNAME)
        $variablenID = $this->RegisterVariableInteger("A_AlarmCode", "AlarmCode", "Alarm.Code");
        IPS_SetInfo ($variablenID, "WSS"); 
        //$this->RegisterVariableInteger("A_Activate", "Alarm Activate");
         $this->RegisterPropertyInteger("EchoID", 0);
         $this->RegisterPropertyInteger("TelegramID", 0);
         $this->RegisterPropertyInteger("SenderID", 671095116);
         
        //Boolean Variable anlegen
        // Aufruf dieser Variable mit §this->GetIDForIdent("IDENTNAME")
        $this->RegisterVariableBoolean("A_SecActivate", "Alarmanlage aktivieren","Alarm.Active");
        $variablenID = $this->RegisterVariableBoolean("A_SecActive", "Alarmanlage");
        IPS_SetInfo ($variablenID, "WSS");   
        //Alexa Sprachbefehl Trigger
        $this->RegisterVariableBoolean("Alexa_SecActivate", "Alexa Alarmanlage aktivieren","Alarm.Activate");
                //CanShot aktivieren
                $this->RegisterPropertyBoolean("FKBCamShot", false);
        //TTS Trigger
        $this->RegisterPropertyBoolean("AlexaTTS", false);
        //Telegram Messenger
        $this->RegisterPropertyBoolean("Telegram", false);
        //Webfront anlegen
        $this->RegisterPropertyBoolean("A_Webfront", true);
        
        $this->RegisterVariableInteger("A_No", "Bildnummer", "");
        
        //String Variable anlegen
        //RegisterVariableString (  §Ident,  §Name, §Profil, §Position )
         // Aufruf dieser Variable mit §this->GetIDForIdent(!IDENTNAME!)
        $variablenID = $this->RegisterVariableString("A_BatAlarm", "Battery Alarm");
        IPS_SetInfo ($variablenID, "WSS");
        $variablenID = $this->RegisterVariableString("A_WaterAlarm", "Water Alarm");
        IPS_SetInfo ($variablenID, "WSS");
        $variablenID = $this->RegisterVariableString("A_WOAlarm", "Waindow open Alarm");
        IPS_SetInfo ($variablenID, "WSS");
        $variablenID = $this->RegisterVariableString("A_SecCode", "Security Code");
        IPS_SetInfo ($variablenID, "WSS");
        $variablenID = $this->RegisterVariableString("A_SecWarning", "Security Meldung");  
        IPS_SetInfo ($variablenID, "WSS");    
        
            //HTML Box anlegen
             $this->RegisterVariableString("A_SecKeyboard", "Security Keyboard"); 
                   
            //HTML Box Profil zuordnen und befüllen
            IPS_SetVariableCustomProfile($this->GetIDForIdent("A_SecKeyboard"), "~HTMLBox");
            
            setvalue($this->GetIDForIdent("A_SecKeyboard"),'<center><iframe src="user/keyboard/index.html?ipsValue='.$this->GetIDForIdent("A_SecCode").'-'.$this->InstanceID.'" frameborder=0 height=300px width=180px></iframe></center>'); 
              
   
        // Aktiviert die Standardaktion der Statusvariable zur Bedienbarkeit im Webfront
        //$this->EnableAction("IDENTNAME");
        $this->EnableAction("A_SecActivate");
        $this->EnableAction("A_SecCode");
        $this->EnableAction("Alexa_SecActivate");
        
        //anlegen eines Timers
        //$this->RegisterTimer(!TimerName!, 0, !FSSC_reset(\§_IPS[!TARGET!>]);!); 
        /*    
        $alleEreignisse = IPS_GetEventList();
        foreach ($alleEreignisse as $EreignisID) {
            IPS_DeleteEvent($EreignisID);
        }
        */

        
        
             
    }
    
    
    #---------------------------------------------------------------#
    #     Function: ApplyChanges                                    #
    #     ApplyChanges() Wird ausgeführt, beim anlegen der Instanz. #
    #     und beim ändern der Parameter in der Form                 #
    #---------------------------------------------------------------#
    # SYSTEM-VARIABLE:
    #    InstanceID = $this->InstanceID.
    #
    # Profiles:
    #   Alarm.Activate
    #   Alarm.Active
    # 
    # Categories:
    #   Security                  (webfront)
    #   Keyboard                  (webfront)
    #   Meldungen                 (webfront)
    # WaterAlarmEvents
    # BatAlarmEvents
    # SecAlarmEvents
    
    # EVENTS:
    #   "WAE".$sensor->ID;    -   für alle Wasser Sensoren
    #   "AE".$sensor->ID;     -   für alle Batterie Sensoren
    #   "SecAE".$sensor->ID;  -   für alle Alarm Sensoren
    #---------------------------------------------------------------#
    public function ApplyChanges() {
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        

        
 
        
        //Never delete this line!        
        parent::ApplyChanges();        
        
        
             
        //Unterkategorie für Webfront anlegen 

        $WebFrontCatID = $this->RegisterCategory("WebFrontIdent", "Alarm_Webfront");// Kategorie unterhalb der Instanz anlegen.
        $secID = $this->CreateCategoryByIdent($this->GetIDForIdent("WebFrontIdent"), "SecurityIdent", "Security"); // Kategorie unterhalb der Instanz anlegen.
        $kbID = $this->CreateCategoryByIdent($this->GetIDForIdent("WebFrontIdent"), "KeyboardIdent", "Keyboard"); // Kategorie unterhalb der Instanz anlegen.
        $MeldID = $this->CreateCategoryByIdent($this->GetIDForIdent("WebFrontIdent"), "MeldungIdent", "Meldungen"); // Kategorie unterhalb der Instanz anlegen.

        
        @IPS_SetParent($this->GetIDForIdent("A_SecKeyboard"),$kbID ); 
        

        $this->CreateLink("Status", $secID, $this->GetIDForIdent("A_SecActive"));    
        $this->CreateLink("Alarm Meldung", $secID, $this->GetIDForIdent("A_SecWarning"));
        $this->CreateLink("Alarmanlage aktivieren", $secID, $this->GetIDForIdent("A_SecActivate"));  
        
        $this->CreateLink("Battery", $MeldID, $this->GetIDForIdent("A_BatAlarm")); 
        $this->CreateLink("Window Open", $MeldID, $this->GetIDForIdent("A_WOAlarm")); 

        if (@IPS_VariableExists($this->GetIDForIdent("A_SecKeyboard"))){
           @IPS_DeleteVariable($this->GetIDForIdent("A_SecKeyboard")); 
        }
     

        //Unterkategorie Water Alarme anlegen
        $WaterAlarmCatID = $this->RegisterCategory("WaterEvntIdent", "WaterAlarmEvents");
        // für jedes Liste ID ein Event anlegen
        $waterSensors = json_decode($this->ReadPropertyString("WaterSensors"));
        foreach($waterSensors as $sensor) {
            $ParentID = $WaterAlarmCatID;
            $Typ = 0;
            $Ident = "WAE".$sensor->ID;
            $Name = "WAEvent".$sensor->ID;
            $cmd = "A_WaterAlarm(".$this->InstanceID.");" ;
            $this->RegisterVarEvent($Name, $Ident, $Typ, $ParentID, 0, 1, $sensor->ID,  $cmd  );
        }    
        
        //Unterkategorie Batterie Alarme anlegen
        $AlarmCatID = $this->RegisterCategory("BatEvntIdent", "BatAlarmEvents");
        // für jedes Liste ID ein Event anlegen
        $batteries = json_decode($this->ReadPropertyString("Battery"));
        foreach($batteries as $sensor) {
            $ParentID = $AlarmCatID;
            $Typ = 0;
            $Ident = "AE".$sensor->ID;
            $Name = "AEvent".$sensor->ID;
            $cmd = "A_BatAlarm(".$this->InstanceID.");" ;
            $this->RegisterVarEvent($Name, $Ident, $Typ, $ParentID, 0, 1, $sensor->ID, $cmd);
        }       

        //Unterkategorie Window Open Alarme anlegen
        $AlarmCatID = $this->RegisterCategory("WOEvntIdent", "WOAlarmEvents");
        // für jedes Liste ID ein Event anlegen
        $windows = json_decode($this->ReadPropertyString("WinOpen"));
        foreach($windows as $sensor) {
            $ParentID = $AlarmCatID;
            $Typ = 0;
            $Ident = "AE".$sensor->ID;
            $Name = "AEvent".$sensor->ID;
            $cmd = "A_WinOpenAlarm(".$this->InstanceID.");" ;
            $this->RegisterVarEvent($Name, $Ident, $Typ, $ParentID, 0, 1, $sensor->ID, $cmd  );
        } 

         //Unterkategorie Sec  Alarme anlegen
        $SecAlarmCatID = $this->RegisterCategory("AlarmEvntIdent","SecAlarmEvents");
        // für jedes Liste ID ein Event anlegen
        $SecAlarms = json_decode($this->ReadPropertyString("SecAlarms"));
        foreach($SecAlarms as $sensor) {
            $ParentID = $SecAlarmCatID;
            $Typ = 0;
            $Ident = "SecAE".$sensor->ID;
            $Name = "SecAEvent".$sensor->ID;
            $cmd = "A_SecurityAlarm(".$this->InstanceID.");";
            $this->RegisterVarEvent($Name, $Ident, $Typ, $ParentID, 0, 1, $sensor->ID, $cmd );
        }        
        
        //check if Modul Alexa - Echo Remote installiert ist.
        if (IPS_ModuleExists("{496AB8B5-396A-40E4-AF41-32F4C48AC90D}")){
           
        } 
        else{
             $this->SetStatus(200);
        }
        /*check if Modul Telegram Messenger -  installiert ist.
        if (IPS_ModuleExists("{eaf404e1-7a2a-40a5-bb4a-e34ca5ac72e5}")){
             
        }
        else{
            $this->SetStatus(201);
        }
        */
    }
    
    #------------------------------------------------------------# 
    #  Function: MessageSink                                     #
    #  MessageSink() wird nur bei registrierten                  #
    #  NachrichtenIDs/SenderIDs-Kombinationen aufgerufen.        #
    #------------------------------------------------------------#    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        //IPS_LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));
        $this->SendDebug('MessageSink', $Message, 0);
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
            break;
        }
      } //Function: MessageSink End


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
        A_SecActivate       -   Alarm Anlage aktivieren
        Alexa_SecActivate   -   Alexa Alarmanlage aktivieren
        A_SecCode           -   Code Eingabe
     ------------------------------------------------------------- */
    public function RequestAction($Ident, $Value) {
            
         switch($Ident) {
             case "A_SecActivate":
                if ($Value == true){ 
                    $this->activateSecAlarm();  
                    $this->setvalue("A_SecActivate",true);
                }
                else {
                    $this->setvalue("A_SecWarning","Sicherheits Code eingeben."); 
                    $this->setvalue("A_SecActivate",true); 
                }
                break;
             case "Alexa_SecActivate":
                $this->activateSecAlarm();  
                break;
              case "A_SecCode":
                $this->setvalue("A_SecCode","$Value");
                $this->checkCode();  
                break;
            default:
                throw new Exception("Invalid Ident");
        }
            
    }

    #-------------------------------------------------------------#
    #    Function: Destroy                                        #
    #        Destroy() wird beim löschen der Instanz              #
    #        und update der Module aufgerufen                     #
    #-------------------------------------------------------------#
    public function Destroy()
    {
        if (IPS_GetKernelRunlevel() <> KR_READY) {
            return parent::Destroy();
        }
        if (!IPS_InstanceExists($this->InstanceID)) {
             
        //Profile löschen
        $this->UnregisterProfile("Alarm.Activate");

             
        }
        parent::Destroy();
    }
    


    #------------------------------------------------------------# 
    #    Function: RequestAction                                 #
    #        RequestAction() wird von schaltbaren Variablen      #
    #        aufgerufen.                                         #
    #------------------------------------------------------------#


#_________________________________________________________________________________________________________
# Section: Public Functions
#    Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" 
#    eingefügt wurden.
#    Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur 
#    Verfügung gestellt:
#_________________________________________________________________________________________________________

    
        //-----------------------------------------------------------------------------
        /* Function: ResetAlarm
        ...............................................................................
        Beschreibung:
         * setzt den ausgelösten Alarm der Alarmanlge zurück
        ...............................................................................
        Parameters: 
            none
        ...............................................................................
        Returns:    
            none
        ------------------------------------------------------------------------------  */
        public function ResetAlarm(){
            $this->setvalue("A_AlarmCode", 0);
        }  


        //-----------------------------------------------------------------------------
        /* Function: receiveCode
        ...............................................................................
        Beschreibung:
         * empfängt zeichen und schribt sie in Variable 
        ...............................................................................
        Parameters: 
             key = Zahlen Code
        ...............................................................................
        Returns:    
            none
        ------------------------------------------------------------------------------  */
        public function receiveCode(string $key){
            $code = $this->getvalue("A_SecCode");
            $this->setvalue("A_SecCode", $code.$key);    
        }  

        //-----------------------------------------------------------------------------
        /* Function: resetCode
        ...............................................................................
        Beschreibung
            löscht den eingegebenen ZahlenCode.
        ...............................................................................
        Parameters: 
            none
        ...............................................................................
        Returns:    
            none
        ------------------------------------------------------------------------------  */
        public function resetCode(){
            $this->setvalue("A_SecCode", "");    
        }  

        //-----------------------------------------------------------------------------
        /* Function: checkCode
        ...............................................................................
        Beschreibung
            überprüft den hash Code des eingegeben Codes mit dem Passwort
        ...............................................................................
        Parameters: 
            none
        ...............................................................................
        Returns:    
            none
        ------------------------------------------------------------------------------  */
        public function checkCode(string $password = ""){
            if ($password== ""){
                $password = $this->getvalue("A_SecCode");
            }
            //Passwort verschlüsseln
            $hash = $this->cryptPW($this->ReadPropertyString("Password"));
             
            $this->SendDebug("Password hash", $hash, 0);
            if (password_verify($password, $hash)) {
                $this->resetCode();
                $this->setvalue("A_SecWarning","Code wurde akzeptiert."); 
                $this->SetValue("A_SecActivate",false);
                $this->SetValue("A_SecActive",false);
                $this->SetValue("A_AlarmCode",0);

                if($this->ReadPropertyBoolean("AlexaTTS")){
                    //Sprachausgabe
                    $text_to_speech = "Code wurde akzeptiert";

                    
                    EchoRemote_TextToSpeech($this->ReadPropertyInteger("EchoID"), $text_to_speech);
                }
            }  
            else{
                $this->resetCode();
                $this->setvalue("A_SecWarning","Falscher Code."); 
                    //Sprachausgabe
                if($this->ReadPropertyBoolean("AlexaTTS")){
                    $text_to_speech = "falscher code";
                    EchoRemote_TextToSpeech($this->ReadPropertyInteger("EchoID"), $text_to_speech);
                }
            }
        }  


        //-----------------------------------------------------------------------------
        /* Function: activateSecAlarm
        ...............................................................................
        Beschreibung
            aktiviert die Alarmanlage
        ...............................................................................
        Parameters: 
            none
        ...............................................................................
        Returns:    
            none
        ------------------------------------------------------------------------------  */
        public function activateSecAlarm(){
            //Sprachausgabe
            if($this->ReadPropertyBoolean("AlexaTTS")){
                $text_to_speech = "Alarmanlage wird in 30Sekunden aktiv.";
                EchoRemote_TextToSpeech($this->ReadPropertyInteger("EchoID"), $text_to_speech);
            }
            sleep(30);
            SetValueBoolean($this->GetIDForIdent("A_SecActive"),true);
            SetValueBoolean($this->GetIDForIdent("A_SecActivate"),true);
            $this->setvalue("A_SecWarning","Alarm Anlage is aktiv."); 
            //Sprachausgabe
            if($this->ReadPropertyBoolean("AlexaTTS")){
                $text_to_speech = "Alarmanlage ist aktiviert.";
                EchoRemote_TextToSpeech($this->ReadPropertyInteger("EchoID"), $text_to_speech);
            }
            
        } 

        /* ----------------------------------------------------------------------------
         Function: WaterAlarm
        ...............................................................................
        Erzeugt einen Alarm bei Wasser oder Feuchte
        ...............................................................................
        Parameters: 
            none.
        ..............................................................................
        Returns:   
             none
        ------------------------------------------------------------------------------- */
	public function WaterAlarm(){
            //überprüfen welches Ereignis ausgelöst hat 
            $WaterSensors = json_decode($this->ReadPropertyString("WaterSensors"));
            $ParentID =   @IPS_GetObjectIDByName("WaterAlarmEvents", $this->InstanceID);
            $lastEvent = 0;
            $lastTriggerVarID = false; 
            foreach($WaterSensors as $sensor) {
                $EreignisID = @IPS_GetEventIDByName("WAEvent".$sensor->ID, $ParentID);
                $EreignisInfo = IPS_GetEvent($EreignisID);
                $aktEvent = $EreignisInfo["LastRun"];
                if($aktEvent > $lastEvent){
                    $lastEvent = $aktEvent;
                    $lastTriggerVarID = $EreignisInfo["TriggerVariableID"];
                }
            }
            if($lastTriggerVarID){
                $ltv =  getvalue($lastTriggerVarID);
                $VarWaterName = IPS_GetLocation($lastTriggerVarID);
                $this->SendDebug( "$lastTriggerVarID: ", $ltv, 0); 
                if($ltv == 1){
                    // Wasser erkannt, Alarm auslösen
                    $this->setvalue("A_WaterAlarm", "WaterSensor: ".$VarWaterName." Alarm");
                    //AlarmCode auf 2 setzen
                    $this->setvalue("A_AlarmCode", 3);
                    //Telegram message senden
                    if($this->ReadPropertyBoolean("Telegram")){
                        $message = "Achtung Wassersensor ".$VarWaterName." hat angesprochen!";
                        Telegram_SendText($this->ReadPropertyInteger("TelegramID"), $message, string($this->ReadPropertyInteger("EchoID")) );
                    }
                    //Sprachausgabe                    
                    if($this->ReadPropertyBoolean("AlexaTTS")){
                        $text_to_speech = "Achtung Wassersensor ".$VarWaterName." hat angesprochen!";
                        EchoRemote_TextToSpeech($this->ReadPropertyInteger("EchoID"), $text_to_speech);
                    }
                }
                else{
                    $this->setvalue("A_WaterAlarm", ""); 
                    $this->setvalue("A_AlarmCode", 0);   
                }
            } 
            else{
                $this->setvalue("A_WaterAlarm", ""); 
                $this->setvalue("A_AlarmCode", 0);
            }
        }          
        
        
        /* ----------------------------------------------------------------------------
         Function: BatAlarm
        ...............................................................................
        Erzeugt einen Alarm bei zu schwacher Batterie
        ...............................................................................
        Parameters: 
            none.
        ..............................................................................
        Returns:   
             none
        ------------------------------------------------------------------------------- */
	public function BatAlarm(){
            //überprüfen welches Ereignis ausgelöst hat 
            $batteries = json_decode($this->ReadPropertyString("Battery"));
            $ParentID =   @IPS_GetObjectIDByName("BatAlarmEvents", $this->InstanceID);
            $lastEvent = 0;
            $lastTriggerVarID = false; 
            foreach($batteries as $sensor) {
                $EreignisID = @IPS_GetEventIDByName("AEvent".$sensor->ID, $ParentID);
                $EreignisInfo = IPS_GetEvent($EreignisID);
                $aktEvent = $EreignisInfo["LastRun"];
                if($aktEvent > $lastEvent){
                    $lastEvent = $aktEvent;
                    $lastTriggerVarID = $EreignisInfo["TriggerVariableID"];
                }
            }
            if($lastTriggerVarID){
                $ltv =  getvalue($lastTriggerVarID);
                $VarBatName = IPS_GetLocation($lastTriggerVarID);
                $this->SendDebug( "$lastTriggerVarID: ", $ltv, 0); 
                if($ltv == 1){
                    // Batterie ist Low Alarm auslösen
                    $this->setvalue("A_BatAlarm", "Battery Low: ".$VarBatName);
                    //AlarmCode auf 1 setzen
                    $this->setvalue("A_AlarmCode", 1);
                    //Sprachausgabe
                    if($this->ReadPropertyBoolean("AlexaTTS")){
                        $text_to_speech = "Batterie ist leer.";
                        EchoRemote_TextToSpeech($this->ReadPropertyInteger("EchoID"), $text_to_speech);
                    }
                }
                else{
                    $this->setvalue("A_BatAlarm", ""); 
                    $this->setvalue("A_AlarmCode", 0);   
                }
            } 
            else{
                $this->setvalue("A_BatAlarm", ""); 
                $this->setvalue("A_AlarmCode", 0);
            }
        }  

        
        /* ----------------------------------------------------------------------------
         Function: WinOpenAlarm
        ...............................................................................
        Erzeugt einen Alarm wenn Fenster zu lange auf ist
        ...............................................................................
        Parameters: 
            none.
        ..............................................................................
        Returns:   
             none
        ------------------------------------------------------------------------------- */
        public function WinOpenAlarm(){
            //überprüfen welches Ereignis ausgelöst hat 
            $Windows = json_decode($this->ReadPropertyString("WinOpen"));
            $ParentID =   @IPS_GetObjectIDByName("WOAlarmEvents", $this->InstanceID);
            $lastEvent = 0;
            $lastTriggerVarID = false; 
            foreach($Windows as $sensor) {
                $EreignisID = @IPS_GetEventIDByName("AEvent".$sensor->ID, $ParentID);
                $EreignisInfo = IPS_GetEvent($EreignisID);
                $aktEvent = $EreignisInfo["LastRun"];
                if($aktEvent > $lastEvent){
                    $lastEvent = $aktEvent;
                    $lastTriggerVarID = $EreignisInfo["TriggerVariableID"];
                }
            }
            if($lastTriggerVarID){
                $ltv =  getvalue($lastTriggerVarID);
                $VarWOName = IPS_GetLocation($lastTriggerVarID);
                $this->SendDebug( "$lastTriggerVarID: ", $ltv, 0); 
                if($ltv == 1){
                    // Fenster zu lange auf Alarm auslösen
                    $this->setvalue("A_WOAlarm", "Fenster ist auf: ".$VarWOName);
                    //AlarmCode auf 1 setzen
                    $this->setvalue("A_AlarmCode", 1);
                    //Sprachausgabe
                    if($this->ReadPropertyBoolean("AlexaTTS")){
                        $text_to_speech = "Fenster ist auf.";
                        EchoRemote_TextToSpeech($this->ReadPropertyInteger("EchoID"), $text_to_speech);
                    }
                }
                else{
                    $this->setvalue("A_WOAlarm", ""); 
                    $this->setvalue("A_AlarmCode", 0);   
                }
            } 
            else{
                $this->setvalue("A_WOAlarm", ""); 
                $this->setvalue("A_AlarmCode", 0);
            }
        }  


        /* ----------------------------------------------------------------------------
         Function: SecurityAlarm
        ...............................................................................
        Erzeugt einen Alarm bei ansprechen von Alarm Sensoren
        ...............................................................................
        Parameters: 
            none.
        ..............................................................................
        Returns:   
             none
        ------------------------------------------------------------------------------- */
	public function SecurityAlarm(){   
            $AlarmAnlageActive = $this->getvalue("A_SecActive");
            if($AlarmAnlageActive){
                //überprüfen welches Ereignis ausgelöst hat 
                $SecAlarms = json_decode($this->ReadPropertyString("SecAlarms"));
                $ParentID =   @IPS_GetObjectIDByName("SecAlarmEvents", $this->InstanceID);
                $lastEvent = 0;
                $lastTriggerVarID = false; 
                foreach($SecAlarms as $sensor) {
                    $EreignisID = @IPS_GetEventIDByName("SecAEvent".$sensor->ID, $ParentID);
                    $EreignisInfo = IPS_GetEvent($EreignisID);
                    $aktEvent = $EreignisInfo["LastRun"];
                    if($aktEvent > $lastEvent){
                        $lastEvent = $aktEvent;
                        $lastTriggerVarID = $EreignisInfo["TriggerVariableID"];
                    }
                }
                if($lastTriggerVarID){
                    $ltv = getvalue($lastTriggerVarID);
                    //AlarmCode auf 2 setzen = Einbruch
                    $this->setvalue("A_AlarmCode", 2);
                    /* -------------- Cam-Bild erstellen und auf Webseite hochladen ------------- */
                    if($this->ReadPropertyBoolean("FKBCamShot")){
                        
                        $no = $this->GetValue("A_No"); 
                        if ($no > 100){
                            $no = 0;
                            $this->SetValue("A_No", 0);
                        }
                        else{
                            $this->SetValue("A_No", $no +1);
                        }
                         
                        $url = "http://192.168.178.6:2323/?cmd=getCamshot&password=sumatra";
                        $image = file("$url");
                        file_put_contents('/home/pi/pi-share/Einbrecher'.$no.'.jpg', $image);
                        
                        //FTP funktioniert nur als script
                        /*
                        $ftp_server= "www.tovipi-beck.de" ;
                        $ftp_user_name= "2006-963";
                        $ftp_user_pass= $this->ReadPropertyInteger("FTPPasswort");
                        $no = $this->getvalue("A_No");
                        $remote_file = 'test'.$no.'.jpg';
                        // Verbindung aufbauen
                        $ftp = ftp_connect($ftp_server);
                        // Login mit Benutzername und Passwort
                        $login_result = ftp_login($ftp, $ftp_user_name, $ftp_user_pass);
                        // Schalte passiven Modus ein
                        ftp_pasv($ftp, true);
                        // Lade eine Datei hoch
                        if (ftp_put($ftp, $remote_file, "/var/lib/symcon/scripts/flower.jpg", FTP_BINARY)) {
                            //echo " erfolgreich hochgeladen\n";
                            if ($no > 100){
                                $no = 0;
                            }
                            $this->SetValue("A_No", $no+1);
                            
                        } else {
                            //echo "Ein Fehler trat beim Hochladen von  auf\n";
                        }
                        // Verbindung schließen
                        ftp_close($ftp);
                         */
                    }
                   

                    //Meldung in Log File schreiben.
                    $text = "Unbefugter Zugang zur Wohnung. ";
                    $array = "wurde erkannt.";
                     
                    $this->setvalue("A_SecWarning","Alarm ausgelöst."); 
                    //Telegram Message senden
                    if($this->ReadPropertyBoolean("Telegram")){
                        $this->SendDebug("ALARM:", "Eine Telegram wird verschickt.", 0);
                        $message = "Achtung ein unbefugter Zugang zur Wohnung wurde erkannt!";
                        Telegram_SendText($this->ReadPropertyInteger("TelegramID"), $message, $this->ReadPropertyInteger("SenderID"));
                    }
                    //Sprachausgabe
                    if($this->ReadPropertyBoolean("AlexaTTS")){
                        $this->SendDebug("ALARM:", "Eine Sprachausgabe über Echo wird ausgegeben.", 0);
                        $text_to_speech = "Alarm wurde ausgelöst.";
                        EchoRemote_TextToSpeech($this->ReadPropertyInteger("EchoID"), $text_to_speech);
                    }
                } 
                else{
             
                    $this->setvalue("A_AlarmCode", 0);
                } 
            }
        }     

        /* --------------------------crypt password
        ...............................................................................
        verschlüsselt ein eingebenes Passort und generiert Code
        ...............................................................................
        Parameters: 
            Password as  String 
        ..............................................................................
        Returns:   
             none
        ------------------------------------------------------------------------------- */
	public function cryptPW(string $password){  
           $hash = password_hash($password, PASSWORD_DEFAULT); 
           $this->SendDebug("Password", $hash, 0);
           return $hash;
        }
        
        
#________________________________________________________________________________________
# Section: Private Functions
#    Die folgenden Funktionen stehen nur innerhalb des Moduls zur verfügung
#    Hilfsfunktionen: 
#_______________________________________________________________________________________

    
 






        /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady()
    {
        $this->ApplyChanges();
    }


}
