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
/***************************************************************************
 * Title: Status Werte eines Raspberry Pi auslesen
 *
 * Author: PiTo
 * 
 * GITHUB: <https://github.com/SymPiTo/MySymApps/tree/master/MyRaspStat>
 * 
 * Version: 1.0
 *************************************************************************** */
 
require_once __DIR__.'/../libs/MyHelper.php';  // diverse Klassen

class MyRaspberryPi extends IPSModule
{
    //Traits verbinden
    use DebugHelper,
        NMapHelper,
        ModuleHelper;
     
# ___________________________________________________________________________ 
#    Section: Internal Modul Functions
#    Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung.
# ___________________________________________________________________________ 

  
    #-----------------------------------------------------------# 
    #    Function: Create                                       #
    #    Create() Wird ausgeführt, beim Anlegen der Instanz.    #
    #-----------------------------------------------------------#    
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

    #---------------------------------------------------------------#
    #     Function: ApplyChanges                                    #
    #     ApplyChanges() Wird ausgeführt, beim anlegen der Instanz. #
    #     und beim ändern der Parameter in der Form                 #
    #---------------------------------------------------------------#
    public function ApplyChanges() {
      $this->RegisterMessage(0, IPS_KERNELSTARTED);
      //Never delete this line!
      parent::ApplyChanges();

      //prüfen ob Modul eingeschltet und Kernel hochgefahren
      $ModReady = false;


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



      $ModOn = $this->ModuleUp($this->ReadPropertyBoolean("Modul_Active"));
      if(!$ModOn){
        //Modul wurde deaktiviert
        $this->SetTimerInterval("update_Timer", 0);
      }
      else{
        //Modul ist aktiviert
        //prüfe of RPI Monitor Service läuft  
        $ip = $this->ReadPropertyString("IPAddress");
        $port = "8888";
        $RPIopen = $this->checkPortState($ip, $port, $type=false);
        if(!$RPIopen){
          $this->SetValue('RPIServer', false);
          $res = $this->restartRPI($ip);
          if($res){
            $this->SetTimerInterval("update_Timer", $this->ReadPropertyInteger("UpdateInterval"));
            $this->SetValue('RPIServer', true);
            $this->update();
          }
          else{
            $this->SetValue('RPIServer', false);
            $this->SetTimerInterval("update_Timer", 0);
          }
        }
        else {
          //Service läuft und Modul ist aktiviert
          if($this->ReadPropertyBoolean("Modul_Active")){ 
            $this->SetTimerInterval("update_Timer", $this->ReadPropertyInteger("UpdateInterval"));
            $this->SetValue('RPIServer', true);
            $this->update();
          }
      
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


    #-------------------------------------------------------------#
    #    Function: Destroy                                        #
    #        Destroy() wird beim löschen der Instanz              #
    #        und update der Module aufgerufen                     #
    #-------------------------------------------------------------#
    
    
    #------------------------------------------------------------# 
    #    Function: RequestAction                                 #
    #        RequestAction() wird von schaltbaren Variablen      #
    #        aufgerufen.                                         #
    #------------------------------------------------------------#
    public function RequestAction($Ident, $Value) {
         switch($Ident) {
            case "RPIServer":
              $this->SetValue($Ident, $Value);
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
              $this->SetValue($Ident, $Value);
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



#_________________________________________________________________________________________________________
# Section: Public Functions
#    Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" 
#    eingefügt wurden.
#    Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur 
#    Verfügung gestellt:
#_________________________________________________________________________________________________________

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
      $this->SendDebug('Update:', "Starte Update", 0);
      $ip = $this->ReadPropertyString("IPAddress");
      $port = "8888"; # RPI Monitor Port

       //$services =  exec("sudo service symcon status"); 

      //$this->SendDebug('ServiceListe', $services , 0);
      
      $SocketOpen = $this->checkPortState($ip, $port, $type=false);
      $this->SendDebug("RPImonitor:", $SocketOpen, 0);
      if (!$SocketOpen) {
        $this->SendDebug('Update:', "RPImonitor: ist down", 0); 
          exec("sudo /etc/init.d/rpimonitord -b start"); 
      }
      else {
        $this->SendDebug('Update:', "RPImonitor: ist offen", 0); 
        $dataJson = @file_get_contents("http://".$ip.":8888/dynamic.json");
        if($dataJson != false){
          $data = json_decode($dataJson, true); 
          $this->SendDebug('RPIMonitor:', $data, 0);  
          if (array_key_exists('cpu_frequency', $data)) {$this->SetValue("ID_cpuFreq", $data['cpu_frequency']);} 
          if (array_key_exists('memory_available', $data)) {$this->SetValue("ID_MemTotal", $data['memory_available']);}
          if (array_key_exists('memory_free', $data)) {$this->SetValue("ID_MemFree", $data['memory_free']);}
          if (array_key_exists('sdcard_boot_used', $data)) {$this->SetValue("ID_SD_boot_used", $data['sdcard_boot_used']);}
          if (array_key_exists('sdcard_root_used', $data)) {$this->SetValue("ID_SD_root_used", $data['sdcard_root_used']);}
          if (array_key_exists('swap_used', $data)) {$this->SetValue("ID_Swap_used", $data['swap_used']);}
          if (array_key_exists('cpu_voltage', $data)) {$this->SetValue("ID_CPU_Volt", $data['cpu_voltage']);}
          if (array_key_exists('http', $data)) {$this->SetValue("ID_http", $data['http']);}
          if (array_key_exists('https', $data)) {$this->SetValue("ID_https", $data['https']);}
          if (array_key_exists('rpimonitor', $data)) {$this->SetValue("ID_RPI_monitor", $data['rpimonitor']);}
          if (array_key_exists('ssh', $data)) {$this->SetValue("ID_ssh", $data['ssh']);}
          if (array_key_exists('scaling_governor', $data)) {$this->SetValue("ID_scal_Gov", $data['scaling_governor']);}
          if (array_key_exists('soc_temp', $data)) {$this->SetValue("ID_CPU_Temp", $data['soc_temp']);}
          if (array_key_exists('upgrade', $data)) {$this->SetValue("ID_upgrade", $data['upgrade']);}
          if (array_key_exists('uptime', $data)) {$this->SetValue("ID_UpTime", json_encode($this->calc_uptime($data['uptime'])));}
          if (array_key_exists('load1', $data)) {$this->SetValue("ID_CPU_load1", $data['load1']);}
          if (array_key_exists('load5', $data)) {$this->SetValue("ID_CPU_load5", $data['load5']);}
          if (array_key_exists('load15', $data)) {$this->SetValue("ID_CPU_load15", $data['load15']);}
          if (array_key_exists('packages', $data)) {$this->SetValue("ID_packages", $data['packages']);}
          $this->SetValue("ID_ip",  $ip);
          if($this->ReadPropertyBoolean("IPS_Server")){
            if (array_key_exists('symcon', $data)) {$this->SetValue("ID_symcon", $data['symcon']);}
            if (array_key_exists('websocketserver', $data)) {$this->SetValue("ID_wss", $data['websocketserver']);}
          }
        }
      }
      if($this->ReadPropertyBoolean("IPS_Server")){
          //check if service is running
          $result = $this->restartIPSservice($ip);
        if ($result) {
            $this->SendDebug('Update:', "IPS Server läuft.", 0); 
            //IP Symcon Service läuft  
            $this->SetValue('IpsServer', true);  
            $this->SetValue("ID_IPS_Version",  IPS_GetKernelVersion());
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
            $this->SetValue("ID_KernelStat", $ks);
         }
        else{
          $this->SendDebug('Update:', "IPS Server ist down.", 0); 
          $this->SetValue('IpsServer', false);
        }  
      }  
    }
 

 
#________________________________________________________________________________________
# Section: Private Functions
#    Die folgenden Funktionen stehen nur innerhalb des Moduls zur verfügung
#    Hilfsfunktionen: 
#_______________________________________________________________________________________ 

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
	private function calc_uptime($uptime)	{
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

      /* ----------------------------------------------------------------------------
      Function: restartRPI
    ...............................................................................
      versucht bis zu 10x den RPI Monitor service nachzustarten
    ...............................................................................
      Parameters: 
            $ip - IP Adresse
    ..............................................................................
      Returns:   
            false/true
    ------------------------------------------------------------------------------- */
  private function restartRPI(string $ip){
    //check if service is running
    $port = "8888"; 
    exec("sudo /etc/init.d/rpimonitor start"); 
    //wait until port is open
    $i= 11;
    while ($i <= 10) {
      $PortOpen = $this->checkPortState($ip, $port, $type=false);
      sleep(200);
      if($PortOpen){
        return true;
      }
      else{
        $i++  ;            // Wert wird um 1 erhöht
      }            
    }   
    //RPI Service lässt sich nicht starten.
    return false; 
  }

      /* ----------------------------------------------------------------------------
      Function: checkIPSservice
    ...............................................................................
      restart Symcon Service und prüft bis zu 10x ob er läuft. 
    ...............................................................................
      Parameters: 
            $ip
    ..............................................................................
      Returns:   
            true/false
    ------------------------------------------------------------------------------- */
  private function restartIPSservice(string $ip){
    //check if service is running
    $port = "3777"; 
    $PortOpen = $this->checkPortState($ip, $port, $type=false);
    if (!$PortOpen) { 
      //Service läuft nicht => Versuche Service zu starten    
      exec("sudo /etc/init.d/symcon start"); 
      //wait until port is open
      $i= 11;
      while ($i <= 10) {
        $PortOpen = $this->checkPortState($ip, $port, $type=false);
        sleep(200);
        if($PortOpen){
          return true;
        }
        else{
          $i++  ;            // Wert wird um 1 erhöht
        }            
      }   
    }
    else {
      //IP Symcon Service läuft  
      return true;
    }
    //IPS Service lässt sich nicht starten.
    
    return false; 
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

    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady()
    {
        $this->ApplyChanges();
    }

		
}