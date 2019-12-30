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

        $variablenID = $this->RegisterVariableBoolean("HeatAlarm", "Störung");
        IPS_SetInfo ($variablenID, "WSS");  
        $variablenID = $this->RegisterVariableInteger("HeatStat", "Status", "Heat.Stat");  
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
            //Profil anlegen:
            $assoc[0] = "Störung";
            $assoc[1] = "Anwärmen";  
            $assoc[2] = "Heizen";
            $assoc[3] = "Kalt";  
            $this->RegisterProfile("Heat.Stat", "","", "", "", "", "", "", 0, "ProfHeatStat", $assoc);
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
    Function: RegisterProfile
    ...............................................................................
    Erstellt ein neues Profil und ordnet es einer Variablen zu.
    ...............................................................................
        Parameters: 
            $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype, $VarIdent, $Assoc
         * $Vartype: 0 boolean, 1 int, 2 float, 3 string,
         * $Assoc: array mit statustexte
        ..............................................................................
        Returns:   
            none
    ------------------------------------------------------------------------------- */
	protected function RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype, $VarIdent, $Assoc){
		if (!IPS_VariableProfileExists($Name)) {
			IPS_CreateVariableProfile($Name, $Vartype); // 0 boolean, 1 int, 2 float, 3 string,
        } 
        else {
			$profile = IPS_GetVariableProfile($Name);
			if ($profile['ProfileType'] != $Vartype)
				$this->SendDebug("Alarm.Reset:", "Variable profile type does not match for profile " . $Name, 0);
		}

		//IPS_SetVariableProfileIcon($Name, $Icon);
		//IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
		//IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
		//IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize); // string $ProfilName, float $Minimalwert, float $Maximalwert, float $Schrittweite

        foreach ($Assoc as $key => $value) {
            IPS_SetVariableProfileAssociation($Name, $key, $value, $Icon, 0xFFFFFF);  
        }
        IPS_SetVariableCustomProfile($this->GetIDForIdent($VarIdent), $Name);
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
            } elseif(IPS_GetEvent($eid)[!EventType!] <> $Typ) {
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