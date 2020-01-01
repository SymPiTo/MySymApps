<?


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

        $this->RegisterProfiles();

        $variablenID = $this->RegisterVariableBoolean("HeatAlarm", "Störung");
        IPS_SetInfo ($variablenID, "WSS");  
        $variablenID = $this->RegisterVariableInteger("HeatStat", "Status", "Heat.Status");  
        IPS_SetInfo ($variablenID, "WSS");    
        $variablenID = $this->RegisterVariableString("Message", "Meldung");  
        IPS_SetInfo ($variablenID, "WSS");  

        // Timer erstellen
        //$this->RegisterTimer("T_TodZeit", 0,  'HS_Todzeit_Reached(' . $this->InstanceID . ');');
        $this->RegisterTimer("T_TodZeit", 0, 'HS_Todzeit_Reached($_IPS[\'TARGET\']);');

        
    }
   /* ------------------------------------------------------------ 
    Function: ApplyChanges 
    ApplyChanges() Wird ausgeführt, wenn auf der Konfigurationsseite "Übernehmen" gedrückt wird 
    und nach dem unittelbaren Erstellen der Instanz.
     
        SYSTEM-VARIABLE:
            InstanceID - $this->InstanceID.

        EVENTS:
  
    ------------------------------------------------------------- */
   

    public function ApplyChanges(){


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
        

        $this->Mem = new puffer();
        $this->SendDebug("Start:MemVal->test", $Mem->test, 0);

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

  
     
        


        if($this->ReadPropertyBoolean("ID_active")){
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
    


  /* ______________________________________________________________________________________________________________________
     Section: Public Funtions
     Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
     Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt:
    
     HS_XYFunktion($Instance_id, ... );
     ________________________________________________________________________________________________________________________ */
    //-----------------------------------------------------------------------------
    /* Function: Heat_Stat
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function Heat_Stat(){
      
          
            $MemVal instanceof $this->Mem ;
        
   
        $this->SendDebug("Start:MemVal->test", $MemVal->test, 0);
        $this->SendDebug("Start:MemVal->Todzeit", $MemVal->Todzeit, 0);
        $this->SendDebug("Start:MemVal->timerOn", $MemVal->timerOn, 0);
        $this->SendDebug("Start:MemVal->RT_before", $MemVal->RT_before, 0);
        $this->SendDebug("Start:MemVal->RLFT_before", $MemVal->RLFT_before, 0);

        if($this->ReadPropertyBoolean("ID_active")){
            if($this->ReadPropertyBoolean("DTsens")){
                $VorlaufTemp = getvalue($this->ReadPropertyInteger("TempVor"));
                $RücklaufTemp = getvalue($this->ReadPropertyInteger("TempRueck"));
            }

            $RaumTemp = getvalue($this->ReadPropertyInteger("RaumTemp"));
            $VtlPos =  getvalue($this->ReadPropertyInteger("VtlPos"));
            if($this->ReadPropertyBoolean("DTsens")){
                // Heizung ist in Störung 
                // Ventil ist auf aber Rücklauftemperatur erhöht sich nicht nach 5 Min
                if($MemVal->Todzeit){
                    $this->SendDebug("MemVal->Todzeit", $MemVal->Todzeit, 0);

                    if($VtlPos > 0 and ($RücklaufTemp <= $MemVal->RLFT_before)){
                        setvalue($this->GetIDForIdent("HeatStat"), 0);	// Störung - RaumTemperatur wurde innerhalb 5 Minuten nicht erhöht
                        setvalue($this->GetIDForIdent("Message"), "Vtl öffnet nicht.");  //Ventil öffnet nicht.
                        $this->SendDebug("Störung :", "Ventil öffent nicht, weil VtlPos= ".$VtlPos." und RücklaufTemp = ".$RücklaufTemp." <= MemVal->RLFT_before = ".$MemVal->RLFT_before , 0);

                    }
                    // Ventil ist auf aber Raumtemperatur erhöht sich nicht nach Todzeit  (5min)  
                    elseif ($VtlPos > 0 and ($MemVal->RT_before <= $RaumTemp)){
                        setvalue($this->GetIDForIdent("HeatStat"), 0);	// Störung - RaumTemperatur wurde innerhalb 5 Minuten nicht erhöht
                        setvalue($this->GetIDForIdent("Message"), "Vtl schwergängig.");  //Ventil ist schwergängig
                        $this->SendDebug("Störung :", "Raumtemp steigt nicht, weil VtlPos= ".$VtlPos." und RaumTemp >= ".$RücklaufTemp." <= MemVal->RLFT_before = ".$MemVal->RLFT_before , 0);
                    }
                    else{
                        //Anwärmvorgang der Heizung - Heizung wird mit heßem Wasser befüllt
                        if ($VorlaufTemp > ($RaumTemp + 1) and ($RücklaufTemp < ($RaumTemp + 1))){
                            setvalue($this->GetIDForIdent("HeatStat"), 1);	
                            $this->SendDebug("Anwärmen: ", "VorlaufTemp = ".$VorlaufTemp. " und RücklaufTemp = ".$RücklaufTemp, 0);
                            // Timer starten wenn nicht schon am laufen - Todzeit - Zeit bis Raumtemperatur sich ändert beim heizen
                            if($MemVal->timerOn === false){
                                $this->SetTimerInterval('T_TodZeit', 1800);   //Timer auf 5 Minuten setzen
                                $MemVal->RT_before = $RaumTemp;
                                $MemVal->RLFT_before = $RücklaufTemp;
                                $this->SendDebug("Anwärmen:", "Timer gestartet, in 5 Minuten muss sich RcklfTemp  und RaumTemp: ".$MemVal->RLFT_before." - ".$MemVal->RT_before, 0);
                            }
                        }
                        // Heizen - Heizkörper ist mit heißem Wasser gefüllt und Rücklauf zeigt Temperatur
                        if ($RücklaufTemp > ($RaumTemp + 1) and ($VorlaufTemp > ($RaumTemp + 1))){
                            setvalue($this->GetIDForIdent("HeatStat"), 2);	

                            $this->SendDebug("Heizen", "Rücklauf zeigt Temperatur = ".$RücklaufTemp, 0);
                        }
                        // Heizung ist aus (Kalt) 
                        if ($RücklaufTemp < ($RaumTemp + 1) and ($VorlaufTemp < ($RaumTemp + 1))) {
                            setvalue($this->GetIDForIdent("HeatStat"), 3);	
                            $this->SendDebug("Kalt", "Vorlauf und Rücklauf kalt  = ".$VorlaufTemp." - ".$RücklaufTemp, 0);
                        }
                    }
                }
                else{
                        //Anwärmvorgang der Heizung - Heizung wird mit heßem Wasser befüllt
                        if ($VorlaufTemp > ($RaumTemp + 1) and ($RücklaufTemp < ($RaumTemp + 1))){
                            setvalue($this->GetIDForIdent("HeatStat"), 1);	
                            $this->SendDebug("Anwärmen: Todzeit = 0: ", "Anwärmen", 0);
                            // Timer starten wenn nicht schon am laufen - Todzeit - Zeit bis Raumtemperatur sich ändert beim heizen
                            $this->SendDebug("Status TimerOn: ", $MemVal->timerOn, 0);
                            if($MemVal->timerOn === false){
                                $this->SetTimerInterval('T_TodZeit', 1800);   //Timer auf 5 Minuten setzen
                                $MemVal->RT_before = $RaumTemp;
                                $MemVal->RLFT_before = $RücklaufTemp;
                                $this->SendDebug("Anwärmen: Todzeit = 0: ", "Timer starten - RT und RLfT".$MemVal->RT_before." - ".$MemVal->RLFT_before, 0);
                            }
                        }
                        // Heizen - Heizkörper ist mit heißem Wasser gefüllt und Rücklauf zeigt Temperatur
                        if ($RücklaufTemp > ($RaumTemp + 1) and ($VorlaufTemp > ($RaumTemp + 1))){
                            setvalue($this->GetIDForIdent("HeatStat"), 2);	
                            $this->SendDebug("Heizen", "Rücklauf zeigt Temperatur = ".$RücklaufTemp, 0);
                        }
                        // Heizung ist aus (Kalt) 
                        if ($RücklaufTemp < ($RaumTemp + 1) and ($VorlaufTemp < ($RaumTemp + 1))) {
                            setvalue($this->GetIDForIdent("HeatStat"), 3);	
                            $this->SendDebug("Kalt", "Vorlauf und Rücklauf kalt  = ".$VorlaufTemp." - ".$RücklaufTemp, 0);
                        }  
                }
            }
            else{
                // keine Vor Rücklauf Sensoren vorhanden
                //----------------------------------------
                // Heizung ist in Störung 
                
                if($MemVal->Todzeit){
 
                    // Ventil ist auf aber Raumtemperatur erhöht sich nicht nach Todzeit  (5min)  
                    if ($VtlPos > 0 and ($MemVal->RT_before <= $RaumTemp)){
                        setvalue($this->GetIDForIdent("HeatStat"), 0);	// Störung - RaumTemperatur wurde innerhalb 5 Minuten nicht erhöht
                        setvalue($this->GetIDForIdent("Message"), "Vtl schwergängig.");  //Ventil ist schwergängig
                    }
                    else{
 
                        // Heizen - Heizkörper ist mit heißem Wasser gefüllt und Rücklauf zeigt Temperatur
                        if ($VtlPos > 0){
                            setvalue($this->GetIDForIdent("HeatStat"), 2);	
                        }
                        // Heizung ist aus (Kalt) 
                        if ($VtlPos === 0) {
                            setvalue($this->GetIDForIdent("HeatStat"), 3);	
                        }
                    }
                }
                else{
                        // Heizen - Heizkörper ist mit heißem Wasser gefüllt und Rücklauf zeigt Temperatur
                        if ($VtlPos > 0){
                            setvalue($this->GetIDForIdent("HeatStat"), 2);	
                        }
                        // Heizung ist aus (Kalt) 
                        if ($VtlPos === 0) {
                            setvalue($this->GetIDForIdent("HeatStat"), 3);	
                        }  
                }

            }
        }
        else{

        }
        
    }  


     //-----------------------------------------------------------------------------
    /* Function: Todzeit_Reached
    ...............................................................................
    Beschreibung:
        Funktion wird vom Timer Todzeit getriggert
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function Todzeit_Reached(){  
        setvalue(37056,true);
         $MemVal = $this->Mem;
       

        $MemVal->Todzeit = true;                           // Merker setzen
        $this->SendDebug("Todzeit_Reached", "Timer ist abgelaufen: ".$MemVal->Todzeit, 0);
        $this->SetTimerInterval('T_TodZeit', 0);     //Timer abschalten
        $this->Heat_Stat();
        $this->SendDebug("Todzeit_Reached", "Heat_Stat() starten. ", 0);
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

    /* ----------------------------------------------------------------------------
     Function: RegisterProfiles()
    ...............................................................................
        Profile fürVaiable anlegen falls nicht schon vorhanden
    ...............................................................................
    Parameters: 
        $Vartype => 0 boolean, 1 int, 2 float, 3 string
    ..............................................................................
    Returns:   
    ------------------------------------------------------------------------------- */
    protected function RegisterProfiles(){
        $Assoc[0]['value'] = "Störung";
        $Assoc[1]['value'] = "Anwärmen";
        $Assoc[2]['value'] = "Heizen";
        $Assoc[3]['value'] = "Kalt";
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
        $this->createProfile($Name, $Vartype,  $Assoc, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits);
       
    }

    /* ----------------------------------------------------------------------------
     Function: RegisterProfile
    ...............................................................................
    Erstellt ein neues Profil und ordnet es einer Variablen zu.
    ...............................................................................
    Parameters: 
        $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype, $VarIdent, $Assoc
     * $Vartype: 0 boolean, 1 int, 2 float, 3 string,
     * $Assoc: array mit statustexte
     *         $assoc[0] = "aus";
     *         $assoc[1] = "ein";
     * RegisterProfile("Rollo.Mode", "", "", "", "", "", "", "", 0, "", $Assoc)
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
                    IPS_SetVariableProfileAssociation($Name, $key, $data['value'], $data['icon'], $data['color']);  
                }
            }
        } 
        else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != $Vartype){
                   // $this->SendDebug("Alarm.Reset:", "Variable profile type does not match for profile " . $Name, 0);
            }
        }
}	

    
     /* --------------------------------------------------------------------------- 
    Function: RegisterVarEvent
    ...............................................................................
    legt einen Event an wenn nicht schon vorhanden
      Beispiel:
      ("Wochenplan", "SwitchTimeEvent".$this->InstanceID, 2, $this->InstanceID, 20);  
      ...............................................................................
    Parameters: 
      $Name        -   Name des Events
      $Ident       -   Ident Name des Events
      $Typ         -   Typ des Events (1=cyclic 2=Wochenplan)
      $Trigger
                0	Bei Variablenaktualisierung
                1	Bei Variablenänderung
                2	Bei Grenzüberschreitung. Grenzwert wird über IPS_SetEventTriggerValue festgelegt
                3	Bei Grenzunterschreitung. Grenzwert wird über IPS_SetEventTriggerValue festgelegt
                4	Bei bestimmtem Wert. Wert wird über IPS_SetEventTriggerValue festgelegt

      $Parent      -   ID des Parents
      $Position    -   Position der Instanz
    ...............................................................................
    Returns:    
        none 
    -------------------------------------------------------------------------------*/
    private function RegisterVarEvent($Name, $Ident, $Typ, $ParentID, $Position, $trigger, $var, $cmd){
            $eid =  @IPS_GetEventIDByName($Name, $ParentID);
            if($eid === false) {
                //we need to create a new one
                $EventID = IPS_CreateEvent($Typ);
                IPS_SetParent($EventID, $ParentID);
                @IPS_SetIdent($EventID, $Ident);
                IPS_SetName($EventID, $Name);
                IPS_SetPosition($EventID, $Position);
                IPS_SetEventTrigger($EventID, $trigger, $var);   //OnChange für Variable $var
                
                IPS_SetEventScript($EventID, $cmd );
                IPS_SetEventActive($EventID, true);
                return $EventID;
            } 
            else{
                return $eid;
            }
            
    }




		
}

Class puffer {
    Public $timerOn = false;
    Public $Todzeit = false;
    Public $RT_before = 0;
    Public $RLFT_before = 0;
    Public $test = "Hallo";
  

 
    public function __construct()
    {
 
    }

 
} 