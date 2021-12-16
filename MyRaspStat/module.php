<?php
/**
 * Title: Status Werte eines Raspberry Pi auslesen
 * Vorraussetzung RPI Monitor muss aus Raspberry installiert sein
 * https://xavierberger.github.io/RPi-Monitor-docs/01_features.html
 * Abrufe der Daten mit: http://ips-raspi:8888/dynamic.json
 * Aufruf der Webseite: http://ips-raspi:8888/status.html
 * author PiTo
 * 
 * GITHUB = <https://github.com/SymPiTo/MySymApps/MyRaspStat/>
 * 
 * Version:1.0.2019.08.22
 */
//Class: MyRaspberryPi

require_once(__DIR__ . "/../libs/NetworkTraits1.php");

class MyRaspberryPi extends IPSModule
{
    //Traits verbinden
    use MyDebugHelper1;
     
/*_______________________________________________________________________ 
     Section: Internal Modul Funtions
     Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung.
  _______________________________________________________________________ */
 
            
    /* ------------------------------------------------------------ 
    Function: Create  
    Create() wird einmalig beim Erstellen einer neuen Instanz und 
    neu laden der Modulesausgeführt. Vorhandene Variable werden nicht veändert, auch nicht 
    eingetragene Werte (Properties).
    Variable können hier nicht verwendet werden nur statische Werte.
    Überschreibt die interne IPS_Create(§id)  Funktion
   
     CONFIG-VARIABLE:
      FS20RSU_ID   -   ID des FS20RSU Modules (selektierbar).
     
    STANDARD-AKTIONEN:
      FSSC_Position    -   Position (integer)

    ------------------------------------------------------------- */
    public function Create()
    {
	    //Never delete this line!
        parent::Create();
 
        // Variable aus dem Instanz Formular registrieren (zugänglich zu machen)
        // Aufruf dieser Form Variable mit  $this->ReadPropertyFloat("IDENTNAME")
        $this->RegisterPropertyInteger("UpdateInterval", 30000);
        $this->RegisterPropertyBoolean("Modul_Active", false);
        $this->RegisterPropertyString("IPAddress", "192.168.178.28");
        $this->RegisterPropertyBoolean("IPS_Server", false);
        


        //Float Variable anlegen
        $variablenID =  $this->RegisterVariableFloat("ID_cpuFreq", "CPU frequency","~Hertz", 1);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableFloat("ID_MemTotal", "Memory total","megabyte.MB", 8);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableFloat("ID_MemFree", "Memory free","megabyte.MB", 9);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableFloat("ID_SD_boot_used", "SD Card Boot used","megabyte.MB", 6);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableFloat("ID_SD_root_used", "SD Card Root used","megabyte.MB", 0);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableFloat("ID_Swap_used", "Swap used","", 10);
        IPS_SetInfo ($variablenID, "WSS"); 
        
         //Integer Variable anlegen
        //integer RegisterVariableInteger ( string $Ident, string $Name, string $Profil, integer $Position )
        //Aufruf dieser Variable mit $this->GetIDForIdent("IDENTNAME")
        //$this->RegisterVariableInteger("FSSC_Position", "Position", "Rollo.Position");
      
        //Boolean Variable anlegen
        //integer RegisterVariableBoolean ( string $Ident, string $Name, string $Profil, integer $Position )
        // Aufruf dieser Variable mit $this->GetIDForIdent("IDENTNAME")
        //$this->RegisterVariableBoolean("FSSC_Mode", "Mode");
        
        //String Variable anlegen
        //RegisterVariableString ($Ident,  $Name, $Profil, $Position )
        //Aufruf dieser Variable mit $this->GetIDForIdent("IDENTNAME")
        $variablenID =  $this->RegisterVariableFloat("ID_CPU_Volt", "CPU Voltage", "~Volt",2);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableString("ID_http", "Port http");
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableString("ID_https", "Port https");
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableString("ID_RPI_monitor", "Port RPI Monitor");
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableString("ID_ssh", "Port Telnet/ssh");
        IPS_SetInfo ($variablenID, "WSS"); 

        $variablenID =  $this->RegisterVariableString("ID_scal_Gov", "scaling govenor");
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableFloat("ID_CPU_Temp", "CPU Temperature", "~Temperature");
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableString("ID_upgrade", "Files upgradable");
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableString("ID_UpTime", "Up-Time");
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableFloat("ID_CPU_load1", "CPU load 1 min", "Prozent",3);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableFloat("ID_CPU_load5", "CPU load 5 min", "Prozent",4);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableFloat("ID_CPU_load15", "CPU load 15 min", "Prozent",5);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableString("ID_packages", "update for packages");
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableString("ID_ip", "IP Adress Device");
        IPS_SetInfo ($variablenID, "WSS"); 

        $variablenID =  $this->RegisterVariableBoolean("RPIServer", "RPI Monitor Server");
    

        // Aktiviert die Standardaktion der Statusvariable zur Bedienbarkeit im Webfront
        $this->EnableAction("RPIServer");
       
        
        IPS_SetVariableCustomProfile($this->GetIDForIdent("RPIServer"), "FBX.InternetState");
        

        //anlegen eines Timers zur Variablen Aktualisierung
        $this->RegisterTimer("update_Timer", $this->ReadPropertyInteger("UpdateInterval"), 'MyRPI_update($_IPS["TARGET"]);');
            


    }
   /* ------------------------------------------------------------ 
     Function: ApplyChanges 
      ApplyChanges() Wird ausgeführt, wenn auf der Konfigurationsseite "Übernehmen" gedrückt wird 
      und nach dem unittelbaren Erstellen der Instanz.
     
    SYSTEM-VARIABLE:
        InstanceID - $this->InstanceID.

    EVENTS:
        SwitchTimeEvent".$this->InstanceID   -   Wochenplan (Mo-Fr und Sa-So)
        SunRiseEvent".$this->InstanceID       -   cyclice Time Event jeden Tag at SunRise
    ------------------------------------------------------------- */
    public function ApplyChanges()
    {
	    //Never delete this line!
      parent::ApplyChanges();



      if($this->ReadPropertyBoolean("IPS_Server")){
        $variablenID =  $this->RegisterVariableString("ID_symcon", "Port symcon");
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableString("ID_wss", "Port WebSocketServer");
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableBoolean("IpsServer", "Symcon Server");
        IPS_SetVariableCustomProfile($this->GetIDForIdent("IpsServer"), "FBX.InternetState");

        $variablenID =  $this->RegisterVariableFloat("ID_IPS_Version", "IPS Version","", 0);
        IPS_SetInfo ($variablenID, "WSS"); 
        $variablenID =  $this->RegisterVariableString("ID_KernelStat", "IPS Kernel Status");
        IPS_SetInfo ($variablenID, "WSS"); 

        $this->EnableAction("IpsServer");
      }

      if(!$this->ReadPropertyBoolean("Modul_Active")){
        //Modul wurde deaktiviert
        $this->SetTimerInterval("update_Timer", 0);
      }
      else{
        //Modul ist aktiviert
        //prüfe of RPI Monitor Service läuft
        $ip = $this->ReadPropertyString("IPAddress");
        $connection = @fsockopen($ip, 8888,$errno, $errstr, 20);
          
        if ($errno != 0) {
            //Service läuft nicht => Versuche Service zu starten  
            $this->SendDebug('SocketOpen', $errstr , 0);
            $this->SetValue('RPIServer', false);
            exec("sudo /etc/init.d/rpimonitor start"); 
        }
        else{
          //Service läuft und Modul ist aktiviert
          if($this->ReadPropertyBoolean("Modul_Active")){
            @fclose($connection);
            $this->SetTimerInterval("update_Timer", $this->ReadPropertyInteger("UpdateInterval"));
            $this->SetValue('RPIServer', true);
            $this->update();
          }
      
        }
      }
    }
    
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
      FSSC_Position    -   Slider für Position
      UpDown           -   Switch für up / Down
      Mode             -   Switch für Automatik/Manual
     ------------------------------------------------------------- */
    public function RequestAction($Ident, $Value) {
         switch($Ident) {
            case "RPIServer":
                SetValue($this->GetIDForIdent($Ident), $Value);
                if($this->getvalue($Ident)){
                  //RPI Monitor Service starten
                  exec("sudo /etc/init.d/rpimonitor start"); 
                }
                else{
                  //RPI Monitor Service stoppen
                  exec("sudo /etc/init.d/rpimonitor stop"); 
                }
                break;
            case "IpsServer":
              SetValue($this->GetIDForIdent($Ident), $Value);
              if($this->getvalue($Ident)){
                //Symcon Service starten
                exec("sudo /etc/init.d/symcon start"); 
              }
              else{
                //Symcon Service stoppen
                exec("sudo /etc/init.d/symcon stop"); 
              } 
                break;
            default:
                throw new Exception("Invalid Ident");
        }
 
    }

    /*------------------------------------------------------------ 
      Function: MessageSink  
      MessageSink() wird nur bei registrierten 
      NachrichtenIDs/SenderIDs-Kombinationen aufgerufen. 
    -------------------------------------------------------------*/
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
      //IPS_LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));
      $this->SendDebug('MessageSink', $Message, 0);
      switch ($Message) {
          case IPS_KERNELSTARTED:
              $this->KernelReady();
          break;
      }
    } //Function: MessageSink End

  /* ______________________________________________________________________________________________________________________
     Section: Public Funtions
     Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
     Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt:
    
     FSSC_XYFunktion($Instance_id, ... );
     ________________________________________________________________________________________________________________________ */
    //-----------------------------------------------------------------------------
    /* Function: update
    ...............................................................................
    Beschreibung:
      liest Statuswerte als Json des Raspberry aus und schreibt Werte in die Variable
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function update(){
      $this->SendDebug('Update:', "hole Werte", 0);
      $ip = $this->ReadPropertyString("IPAddress");
      $connection = @fsockopen($ip, 8888,$errno, $errstr, 20);
      $services =  exec("sudo service symcon status"); 

      $this->SendDebug('ServiceListe', $services , 0);


      if ($errno != 0) {
          @fclose($connection);  
          $this->SendDebug('SocketOpen', $errstr , 0);
          exec("sudo /etc/init.d/rpimonitord -b start"); 
      }
      else{

        @fclose($connection);

      $ip = $this->ReadPropertyString("IPAddress");
 
        $data = @file_get_contents("http://".$ip.":8888/dynamic.json");
        //$this->SendDebug('Update', $data, 0);

          
      $data = json_decode($data, true); 
      $this->SendDebug('Update:DATA: ', $data, 0);
      SetValue($this->GetIDForIdent("ID_cpuFreq"), $data['cpu_frequency']); 
      SetValue($this->GetIDForIdent("ID_MemTotal"), $data['memory_available']);
      SetValue($this->GetIDForIdent("ID_MemFree"), $data['memory_free']);
      SetValue($this->GetIDForIdent("ID_SD_boot_used"), $data['sdcard_boot_used']);
      SetValue($this->GetIDForIdent("ID_SD_root_used"), $data['sdcard_root_used']);
      SetValue($this->GetIDForIdent("ID_Swap_used"), $data['swap_used']);
      SetValue($this->GetIDForIdent("ID_CPU_Volt"), $data['cpu_voltage']);
      SetValue($this->GetIDForIdent("ID_http"), $data['http']);
      //SetValue($this->GetIDForIdent("ID_https"), $data['https']);
      SetValue($this->GetIDForIdent("ID_RPI_monitor"), $data['rpimonitor']);
      SetValue($this->GetIDForIdent("ID_ssh"), $data['ssh']);

      SetValue($this->GetIDForIdent("ID_scal_Gov"), $data['scaling_governor']);
      SetValue($this->GetIDForIdent("ID_CPU_Temp"), $data['soc_temp']);
      SetValue($this->GetIDForIdent("ID_upgrade"), $data['upgrade']);
      SetValue($this->GetIDForIdent("ID_UpTime"), json_encode($this->calc_uptime($data['uptime'])));
      SetValue($this->GetIDForIdent("ID_CPU_load1"), $data['load1']);
      SetValue($this->GetIDForIdent("ID_CPU_load5"), $data['load5']);
      SetValue($this->GetIDForIdent("ID_CPU_load15"), $data['load15']);
      SetValue($this->GetIDForIdent("ID_packages"), $data['packages']);
      SetValue($this->GetIDForIdent("ID_ip"),  $ip);
if($this->ReadPropertyBoolean("IPS_Server")){
  //SetValue($this->GetIDForIdent("ID_symcon"), $data['symcon']);
  SetValue($this->GetIDForIdent("ID_wss"), $data['websocketserver']);
}

    }
      if($this->ReadPropertyBoolean("IPS_Server")){
        //check if service is running
        $ip = $this->ReadPropertyString("IPAddress");
        $connection = @fsockopen($ip, 3777,$errno, $errstr, 20);
        if ($errno != 0) {
            //Service läuft nicht => Versuche Service zu starten  
            $this->SendDebug('IP Symcon Service:', $errstr , 0);
            $this->SetValue('IpsServer', false);
            @fclose($connection);
            exec("sudo /etc/init.d/symcon start"); 
        }
        else{
        //IP Symcon Service läuft  
        @fclose($connection);
        $this->SetValue('IpsServer', true);  
        SetValue($this->GetIDForIdent("ID_IPS_Version"),  IPS_GetKernelVersion());
        $kernelStat = IPS_GetKernelRunlevel();
        switch ($kernelStat) {
          case KR_CREATE:
            $ks = "Kernel wird erstellt.";
            break;
            case KR_INIT:
            $ks = "Kernel wird initialisiert.";
            break;
            case KR_READY:
            $ks = "Kernel ist bereit und läuft.";
            break;
            case KR_UNINIT:
            $ks = "Kernel wird heruntergefahren.";
            break;
            case KR_SHUTDOWN:
            $ks = "Kernel wurde beendet.";
            break;
          default:
            # code...
            break;
        }
      
        SetValue($this->GetIDForIdent("ID_KernelStat"),  $ks);
      }
        
    }  
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
            $uptime (array)  days - hours - minutes - seconds
    ------------------------------------------------------------------------------- */
	protected function calc_uptime($uptime)
	{
    $sek = intval($uptime);
    $min = ($sek/60); 
    $std =  ($min/60);
    $tag =  ($std/24);
    $days = intval($tag);
    $h = $std - $days*24;
    $hours = intval($h);
    $m = $min - $hours*60 - $days*24*60;
    $minutes = intval($m);
    $s = $sek - $hours*60*60 - $days*24*60*60 - $minutes*60;
    $seconds = intval($s);
    $Laufzeit['seconds'] = $seconds;
    $Laufzeit['$minutes'] = $minutes;
    $Laufzeit['$hours'] = $hours;
    $Laufzeit['$days'] = $days;
     
    return $Laufzeit;
  }

    protected function SendToSplitter(string $payload)
		{						
			//an Splitter schicken
			$result = $this->SendDataToParent(json_encode(Array("DataID" => "{687E15E1-5C42-A35E-AD38-C4F1659B0DAA}", "Buffer" => $payload))); // Interface GUI
			return $result;
		}
		
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
    }
    
 
    /* ----------------------------------------------------------------------------------------------------- 
    Function: RegisterScheduleAction
    ...............................................................................
     *  Legt eine Aktion für den Event fest
     * Beispiel:
     * ("SwitchTimeEvent".$this->InstanceID), 1, "Down", 0xFF0040, "FSSC_SetRolloDown(\$_IPS["TARGET"]);");
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