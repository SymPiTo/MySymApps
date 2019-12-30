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


    }
   /* ------------------------------------------------------------ 
    Function: ApplyChanges 
    ApplyChanges() Wird ausgeführt, wenn auf der Konfigurationsseite "Übernehmen" gedrückt wird 
    und nach dem unittelbaren Erstellen der Instanz.
     
        SYSTEM-VARIABLE:
            InstanceID - $this->InstanceID.

        Profiles:
        * Alarm.Activate

        EVENTS:
            SwitchTimeEvent".$this->InstanceID   -   Wochenplan (Mo-Fr und Sa-So)
            SunRiseEvent".$this->InstanceID       -   cyclice Time Event jeden Tag at SunRise
    ------------------------------------------------------------- */
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();
         
    }
    


  /* ______________________________________________________________________________________________________________________
     Section: Public Funtions
     Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
     Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt:
    
     FSSC_XYFunktion($Instance_id, ... );
     ________________________________________________________________________________________________________________________ */
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

    
 




		
}