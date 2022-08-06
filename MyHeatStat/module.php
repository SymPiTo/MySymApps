<?
require_once(__DIR__ . "/../libs/MemHelper.php");
require_once __DIR__ . '/../libs/MyHelper.php';  // diverse Klassen

/**
 * Title: Heat Alarm
  *
 * author PiTo
 * 
 * GITHUB = <https://github.com/SymPiTo/MySymApps/tree/master/MyHeatStat>
 * 
 * Version:1.0.2019.12.30
 */
//Class: MyHeatAlarm
class MyHeatStat extends IPSModule
{    
    use DebugHelper,
    EventHelper,
    ProfileHelper,
    ModuleHelper;
    
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
        * RaumTemp          -   integer
        * TempSoll          -   Integer 
        * VtlPos            -   integer
        * TempVor           -   Integer 
        * TempRueck         -   integer
        * TempRueck         -   Boolean
         
    IPS Variable:
        * HeatAlarm         -   StrörFlag (binary)
        * HeatStat          -   Störungsindex (0), Anwärmen (1), Heizen (2),  Kalt (3)  (Integer)
    ------------------------------------------------------------- */
    public function Create(){

	    //Never delete this line!
        parent::Create();

        $this->RegisterPropertyBoolean("ID_active", false);
        $this->RegisterPropertyInteger("RaumTemp", 0);
        $this->RegisterPropertyInteger("TempSoll", 0);
        $this->RegisterPropertyInteger("VtlPos", 0);
        $this->RegisterPropertyInteger("TempVor", 0);
        $this->RegisterPropertyInteger("TempRueck", 0);
        $this->RegisterPropertyBoolean("DTsens", false);

        $this->RegisterAllProfiles();

        $variablenID = $this->RegisterVariableBoolean("HeatAlarm", "Störung");
        IPS_SetInfo ($variablenID, "WSS");  
        $variablenID = $this->RegisterVariableInteger("HeatStat", "Status", "Heat.Status");  
        IPS_SetInfo ($variablenID, "WSS");    
        $variablenID = $this->RegisterVariableString("Message", "Meldung");  
        IPS_SetInfo ($variablenID, "WSS");  
        $variablenID = $this->RegisterVariableString("puffer", "Puffer"); 
        IPS_SetHidden($variablenID, true); //Objekt verstecken
        // Timer erstellen
        //$this->RegisterTimer("T_TodZeit", 0,  'HS_Todzeit_Reached(' . $this->InstanceID . ');');
        $this->RegisterTimer("T_TodZeit", 0, 'HS_Todzeit_Reached($_IPS[\'TARGET\']);');
    }

    
    #---------------------------------------------------------------#
    #     Function: ApplyChanges                                    #
    #     ApplyChanges() Wird ausgeführt, beim anlegen der Instanz. #
    #     und beim ändern der Parameter in der Form                 #
    #---------------------------------------------------------------#
    public function ApplyChanges(){

        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        //Never delete this line!
        parent::ApplyChanges();

        $this->SendDebug("Status Meldung: ", $this->ReadPropertyInteger("TempVor"), 0);
        $this->SendDebug("Status Meldung: ", $this->ReadPropertyInteger("TempRueck"), 0);
        //Difff Temp Sensoren wurden aktiviert - Links zu den Sensoren fehlen !
        if(($this->ReadPropertyInteger("TempVor") === 0) or ($this->ReadPropertyInteger("TempRueck") === 0)){
    
            if($this->ReadPropertyBoolean("DTsens")){
                $this->SetStatus(200);
            }
            else{
                $this->SetStatus(102);
            }
        } 
        else{
            $this->SetStatus(102);
        }       
        

        $Mem = new puffer($this->GetIDForIdent("puffer"));
        $VarArray = array("timerOn", "Todzeit", "RT_before", "RLFT_before");
        $Mem->defineVars($VarArray);
        $Mem->setMem("timerOn", false);
        $Mem->setMem("Todzeit", false);
        $Mem->setMem("RT_before", 0);
        $Mem->setMem("RLFT_before", 0);

        $this->SendDebug("Apply: ", "Memory Variable setzen.", 0);


        //Event kann erst erstellt werden, wenn ID von VtlPos eingetragen wurde
        if($this->ReadPropertyInteger("VtlPos") >0){
           //Event bei Änderung der Variablen "VtlPos"
           $EventName = "PosEvnt";
           $varID = $this->ReadPropertyInteger("VtlPos");
           $Ident = "IDPosEvnt";
           $ParentID = $varID; //Event unter die Variable hängen
           $cmd = "HS_Heat_Stat(".$this->InstanceID.");" ;
           $EventID = $this->RegisterVarEvent($EventName, $Ident, 0, $ParentID, 0, 1, $varID, $cmd); 
        }

        $ModOn = $this->ModuleUp($this->ReadPropertyBoolean("ID_active"));

        if($ModOn){
            //Überprüfen dass die Links gesetzt wurden
            if($this->ReadPropertyInteger("VtlPos") >0){
                //Event aktivieren - wenn Postion von Aktor sich ändert dann Trigger Event
                IPS_SetEventActive($EventID, true);
            }
            // initiales ausführen
            $this->Heat_Stat();
        }
        else{
            if($this->ReadPropertyInteger("VtlPos") >0){    
                IPS_SetEventActive($EventID, false);            //Event deaktivieren
                $this->SetTimerInterval('T_TodZeit', 0);     //Timer abschalten
            }
        }
         
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

    #-----------------------------------------------------------------------------#
    # Function: Heat_Stat                                                         #
    #.............................................................................#
    # Beschreibung                                                                #
    #.............................................................................#
    # Parameters:    none                                                         #
    #.............................................................................#
    # Returns:       none                                                         #  
    #-----------------------------------------------------------------------------#
    public function Heat_Stat(){
      
        $MemVal = new puffer($this->GetIDForIdent("puffer"));
    
        $this->SendDebug("Start:MemVal->Todzeit", $MemVal->getMem("Todzeit"), 0);
        $this->SendDebug("Start:MemVal->timerOn", $MemVal->getMem("timerOn"), 0);
        $this->SendDebug("Start:MemVal->RT_before", $MemVal->getMem("RT_before"), 0);
        $this->SendDebug("Start:MemVal->RLFT_before", $MemVal->getMem("RLFT_before"), 0);
        $ModOn = $this->ModuleUp($this->ReadPropertyBoolean("ID_active"));
        if($ModOn){
            if($this->ReadPropertyBoolean("DTsens")){
                $VorlaufTemp = getvalue($this->ReadPropertyInteger("TempVor"));
                $RücklaufTemp = getvalue($this->ReadPropertyInteger("TempRueck"));
            }

            $RaumTemp = getvalue($this->ReadPropertyInteger("RaumTemp"));
            $VtlPos =  getvalue($this->ReadPropertyInteger("VtlPos"));
            if($this->ReadPropertyBoolean("DTsens")){
                // Heizung ist in Störung 
                // Ventil ist auf aber Rücklauftemperatur erhöht sich nicht nach 5 Min
                if($MemVal->getMem("Todzeit")){
                    $this->SendDebug("MemVal->Todzeit", $MemVal->getMem("Todzeit"), 0);

                    if($VtlPos > 0 and ($RücklaufTemp <= $MemVal->getMem("RLFT_before"))){
                        $this->setvalue("HeatStat", 3);	// Störung - RaumTemperatur wurde innerhalb 5 Minuten nicht erhöht
                        $this->setvalue("Message", "Vtl öffnet nicht.");  //Ventil öffnet nicht.
                        $this->SendDebug("Störung :", "Ventil öffent nicht, weil VtlPos= ".$VtlPos." und RücklaufTemp = ".$RücklaufTemp." <= MemVal->RLFT_before = ".$MemVal->getMem("RLFT_before") , 0);

                    }
                    // Ventil ist auf aber Raumtemperatur erhöht sich nicht nach Todzeit  (5min)  
                    elseif ($VtlPos > 0 and ($MemVal->getMem("RT_before") <= $RaumTemp)){
                        $this->setvalue("HeatStat", 3);	// Störung - RaumTemperatur wurde innerhalb 5 Minuten nicht erhöht
                        $this->setvalue("Message", "Vtl schwergängig.");  //Ventil ist schwergängig
                        $this->SendDebug("Störung :", "Raumtemp steigt nicht, weil VtlPos= ".$VtlPos." und RaumTemp >= ".$RücklaufTemp." <= MemVal->RLFT_before = ".$MemVal->getMem("RLFT_before") , 0);
                    }
                    else{
                        //Anwärmvorgang der Heizung - Heizung wird mit heßem Wasser befüllt
                        if ($VorlaufTemp > ($RaumTemp + 1) and ($RücklaufTemp < ($RaumTemp + 1))){
                            $this->setvalue("HeatStat", 1);	
                            $this->SendDebug("Anwärmen: ", "VorlaufTemp = ".$VorlaufTemp. " und RücklaufTemp = ".$RücklaufTemp, 0);
                            // Timer starten wenn nicht schon am laufen - Todzeit - Zeit bis Raumtemperatur sich ändert beim heizen
                            if($MemVal->getMem("timerOn") === false){
                                $this->SetTimerInterval('T_TodZeit', 1800000);   //Timer auf 5 Minuten setzen
                                $MemVal->setMem("RT_before", $RaumTemp);
                                $MemVal->setMem("RLFT_before", $RücklaufTemp);
                                $this->SendDebug("Anwärmen:", "Timer gestartet, in 5 Minuten muss sich RcklfTemp  und RaumTemp: ".$MemVal->getMem("RLFT_before")." - ".$MemVal->getMem("RT_before"), 0);
                            }
                        }
                        // Heizen - Heizkörper ist mit heißem Wasser gefüllt und Rücklauf zeigt Temperatur
                        if ($RücklaufTemp > ($RaumTemp + 1) and ($VorlaufTemp > ($RaumTemp + 1))){
                            $this->setvalue("HeatStat", 2);	
                            $this->setvalue("Message", "");	// Störung behoben Meldung zurücksetzen
                            $this->SendDebug("Heizen", "Rücklauf zeigt Temperaturerhöhung = ".$RücklaufTemp, 0);
                        }
                        // Heizung ist aus (Kalt) 
                        if ($RücklaufTemp < ($RaumTemp + 1) and ($VorlaufTemp < ($RaumTemp + 1))) {
                            $this->setvalue("HeatStat", 0);	
                            $this->setvalue("Message", "");	// Störung behoben Meldung zurücksetzen
                            $this->SendDebug("Kalt", "Vorlauf und Rücklauf kalt  = ".$VorlaufTemp." - ".$RücklaufTemp, 0);
                        }
                    }
                }
                else{
                        //Anwärmvorgang der Heizung - Heizung wird mit heßem Wasser befüllt
                        if ($VorlaufTemp > ($RaumTemp + 1) and ($RücklaufTemp < ($RaumTemp + 1))){
                            $this->setvalue("HeatStat", 1);	
                            $this->SendDebug("Anwärmen: Todzeit = 0: ", "Anwärmen", 0);
                            // Timer starten wenn nicht schon am laufen - Todzeit - Zeit bis Raumtemperatur sich ändert beim heizen
                            $this->SendDebug("Status TimerOn: ", $MemVal->getMem("timerOn"), 0);
                            if($MemVal->getMem("timerOn") === false){
                                $this->SetTimerInterval('T_TodZeit', 1800000);   //Timer auf 5 Minuten setzen
                                $MemVal->setMem("RT_before", $RaumTemp);
                                $MemVal->setMem("RLFT_before", $RücklaufTemp);
                                $this->SendDebug("Anwärmen: Todzeit = 0: ", "Timer starten - RT und RLfT".$MemVal->getMem("RT_before")." - ".$MemVal->getMem("RLFT_before"), 0);
                            }
                        }
                        // Heizen - Heizkörper ist mit heißem Wasser gefüllt und Rücklauf zeigt Temperatur
                        if ($RücklaufTemp > ($RaumTemp + 1) and ($VorlaufTemp > ($RaumTemp + 1))){
                            $this->setvalue("HeatStat", 2);	
                            $this->setvalue("Message", "");	// Störung behoben Meldung zurücksetzen
                            $this->SendDebug("Heizen", "Rücklauf zeigt Temperatur = ".$RücklaufTemp, 0);
                        }
                        // Heizung ist aus (Kalt) 
                        if ($RücklaufTemp < ($RaumTemp + 1) and ($VorlaufTemp < ($RaumTemp + 1))) {
                            $this->setvalue("HeatStat", 0);	
                            $this->setvalue("Message", "");	// Störung behoben Meldung zurücksetzen
                            $this->SendDebug("Kalt", "Vorlauf und Rücklauf kalt  = ".$VorlaufTemp." - ".$RücklaufTemp, 0);
                        }  
                }
            }
            else{
                // keine Vor Rücklauf Sensoren vorhanden
                //----------------------------------------
                // Heizung ist in Störung 
                
                if($MemVal->getMem("Todzeit")){
 
                    // Ventil ist auf aber Raumtemperatur erhöht sich nicht nach Todzeit  (5min)  
                    if ($VtlPos > 0 and ($MemVal->getMem("RT_before") <= $RaumTemp)){
                        $this->setvalue("HeatStat", 3);	// Störung - RaumTemperatur wurde innerhalb 5 Minuten nicht erhöht
                        $this->setvalue("Message", "Vtl schwergängig.");  //Ventil ist schwergängig
                    }
                    else{
 
                        // Heizen - Heizkörper ist mit heißem Wasser gefüllt und Rücklauf zeigt Temperatur
                        if ($VtlPos > 0){
                            $this->setvalue("HeatStat", 2);	
                        }
                        // Heizung ist aus (Kalt) 
                        if ($VtlPos === 0) {
                            $this->setvalue("HeatStat", 0);	
                        }
                    }
                }
                else{
                        // Heizen - Heizkörper ist mit heißem Wasser gefüllt und Rücklauf zeigt Temperatur
                        if ($VtlPos > 0){
                            $this->setvalue("HeatStat", 2);	
                        }
                        // Heizung ist aus (Kalt) 
                        if ($VtlPos === 0) {
                            $this->setvalue("HeatStat", 0);	
                        }  
                }

            }
        }
        else{

        }
        
    }  


    #------------------------------------------------------------------------------#
    # Function: Todzeit_Reached                                                    #
    #..............................................................................#
    # Beschreibung:                                                                #
    #    Funktion wird vom Timer Todzeit getriggert                                #
    #..............................................................................#
    # Parameters:    none                                                          #
    #..............................................................................#
    # Returns:       none                                                          #
    #------------------------------------------------------------------------------#
    public function Todzeit_Reached(){  
         
         $MemVal = new puffer($this->GetIDForIdent("puffer"));
       

        $MemVal->setMem("Todzeit", true);                           // Merker setzen
        $this->SendDebug("Todzeit_Reached", "Timer ist abgelaufen: ".$MemVal->getMem("Todzeit"), 0);
        $this->SetTimerInterval('T_TodZeit', 0);     //Timer abschalten
        $this->Heat_Stat();
        $this->SendDebug("Todzeit_Reached", "Heat_Stat() starten. ", 0);
    }



#________________________________________________________________________________________
# Section: Private Functions
#    Die folgenden Funktionen stehen nur innerhalb des Moduls zur verfügung
#    Hilfsfunktionen: 
#_______________________________________________________________________________________
  
    #---------------------------------------------------------------------------------#
    # Function: KernelReady                                                           #
    #.................................................................................#
    # Beschreibung:                                                                   #
    #     Wird ausgeführt wenn der Kernel hochgefahren wurde.                         #
    #.................................................................................#
    # Parameters:   none                                                              #
    #.................................................................................#
    # Returns:      none                                                              #
    #---------------------------------------------------------------------------------#
    protected function KernelReady() {
        $this->ApplyChanges();
    }
		

    #---------------------------------------------------------------------------------#
    # Function: RegisterAllProfiles                                                   #
    #.................................................................................#
    # Beschreibung:                                                                   #
    #     Profile für Variable anlegen falls nicht schon vorhanden.                   #
    #.................................................................................#
    # Parameters:   $Vartype => 0 boolean, 1 int, 2 float, 3 string                   #
    #.................................................................................#
    # Returns:      none                                                              #
    #---------------------------------------------------------------------------------#
    protected function RegisterAllProfiles(){
        $Assoc[0]['value'] = "Kalt";
        $Assoc[1]['value'] = "Anwärmen";
        $Assoc[2]['value'] = "Heizen";
        $Assoc[3]['value'] = "Störung";
        $Assoc[0]['icon'] =  NULL;
        $Assoc[1]['icon'] =  NULL;
        $Assoc[2]['icon'] = NULL;
        $Assoc[3]['icon'] = NULL;
        $Assoc[0]['color'] = "0xFFFF00";
        $Assoc[1]['color'] = "0xFFA500";
        $Assoc[2]['color'] = "0xFF0000";
        $Assoc[3]['color'] = "0x0000FF";
        $Name = "Heat.Status";
        $Vartype = 1;
        $Icon = NULL;
        $Prefix = NULL;
        $Suffix = NULL;
        $MinValue = 0;
        $MaxValue = 4;
        $StepSize = 1;
        $Digits = NULL;
        $this->RegisterProfile($Vartype, $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Assoc);  
    }


		
}
