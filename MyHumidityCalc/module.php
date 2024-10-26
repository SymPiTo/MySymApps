<?php
//require_once __DIR__.'/../libs/traits.php';  // Allgemeine Funktionen
require_once __DIR__.'/../libs/MyHelper.php';  // diverse Klassen


// CLASS HumitidySensor
class MyHumidityCalc extends IPSModule
{
    use ProfileHelper, DebugHelper, ModuleHelper;

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
        $this->RegisterPropertyBoolean("ID_active", false);
        // Outdoor variables
        $this->RegisterPropertyInteger('TempOutdoor', 0);
        $this->RegisterPropertyInteger('HumyOutdoor', 0);
        // Indoor variables
        $this->RegisterPropertyInteger('TempIndoor', 0);
        $this->RegisterPropertyInteger('HumyIndoor', 0);
        // Fenster Kontakt variables
        $this->RegisterPropertyInteger('FensterKontakt', 0);
        // Dashboard
        $this->RegisterPropertyInteger('ScriptMessage', 0);
        $this->RegisterPropertyString('RoomName', 'Unknown');
        $this->RegisterPropertyInteger('LifeTime', 0);
        // Settings
        $this->RegisterPropertyInteger('MessageThreshold', 100);
        $this->RegisterPropertyInteger('UpdateTimer', 15);
        $this->RegisterPropertyBoolean('CreateDewPoint', true);
        $this->RegisterPropertyBoolean('CreateWaterContent', true);
        // Update trigger
        $this->RegisterTimer('UpdateTrigger', 0, "THS_Update(\$_IPS['TARGET']);");
    }

    #---------------------------------------------------------------#
    #     Function: ApplyChanges                                    #
    #     ApplyChanges() Wird ausgeführt, beim anlegen der Instanz. #
    #     und beim ändern der Parameter in der Form                 #
    #---------------------------------------------------------------#
    public function ApplyChanges()
    {
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        //Never delete this line!
        parent::ApplyChanges();

        $ModOn = $this->ModuleUp($this->ReadPropertyBoolean("ID_active"));
        if($ModOn){
            // Update Trigger Timer
            $this->SetTimerInterval('UpdateTrigger', 1000 * 60 * $this->ReadPropertyInteger('UpdateTimer'));
        }
        
        // Profile "THS.AirOrNot"
        $association = [
            [0, 'Nicht Lüften!', 'Window-100', 0xFF0000],
            [1, 'Lüften möglich!', 'Window-0', 0x00FF00],
        ];
        $this->RegisterProfile(VARIABLETYPE_BOOLEAN, 'THS.AirOrNot', 'Window', '', '', 0, 0, 0, 0, $association);
        // Profile "THS.WaterContent"
        $association = [
            [0, '%0.2f', '', 0x808080],
        ];
        $this->RegisterProfile(VARIABLETYPE_FLOAT, 'THS.WaterContent', 'Drops', '', ' g/m³', 0, 0, 0, 0, $association);
        // Profile "THS.Difference"
        $association = [
            [-500, '%0.2f %%', 'Window-100', 32768],
            [0, '%0.2f %%', 'Window-100', 32768],
            [0.01, '+%0.2f %%', 'Window-100', 16744448],
            [10, '+%0.2f %%', 'Window-0', 16711680],
        ];
        $this->RegisterProfile(VARIABLETYPE_FLOAT, 'THS.Difference', 'Window', '', '', 0, 0, 0, 2, $association);
        // Warnmeldung Fenster offen
        $this->MaintainVariable('WinOpen', 'WindowOpen', VARIABLETYPE_BOOLEAN, '~Alert', 10, true);
        IPS_SetInfo ($this->GetIDForIdent("WinOpen"), "WSS");
        // Ergebnis & Hinweis & Differenz
        $this->MaintainVariable('Hint', 'Hinweis', VARIABLETYPE_BOOLEAN, 'THS.AirOrNot', 1, true);
        IPS_SetInfo ($this->GetIDForIdent("Hint"), "WSS");   
        $this->MaintainVariable('Result', 'Ergebnis', VARIABLETYPE_STRING, '', 2, true);
        IPS_SetInfo ($this->GetIDForIdent("Result"), "WSS");  
        $this->MaintainVariable('Difference', 'Differenz', VARIABLETYPE_FLOAT, 'THS.Difference', 3, true);
        IPS_SetInfo ($this->GetIDForIdent("Difference"), "WSS");  
        // Taupunkt
        $create = $this->ReadPropertyBoolean('CreateDewPoint');
        $this->MaintainVariable('DewPointOutdoor', 'Taupunkt Aussen', VARIABLETYPE_FLOAT, '~Temperature', 4, $create);
        IPS_SetInfo ($this->GetIDForIdent("DewPointOutdoor"), "WSS");  
        $this->MaintainVariable('DewPointIndoor', 'Taupunkt Innen', VARIABLETYPE_FLOAT, '~Temperature', 5, $create);
        IPS_SetInfo ($this->GetIDForIdent("DewPointIndoor"), "WSS");  
        // Wassergehalt (WaterContent)
        $create = $this->ReadPropertyBoolean('CreateWaterContent');
        $this->MaintainVariable('WaterContentOutdoor', 'Wassergehalt Aussen', VARIABLETYPE_FLOAT, 'THS.WaterContent', 6, $create);
        IPS_SetInfo ($this->GetIDForIdent("WaterContentOutdoor"), "WSS");  
        $this->MaintainVariable('WaterContentIndoor', 'Wassergehalt Innen', VARIABLETYPE_FLOAT, 'THS.WaterContent', 7, $create);
        IPS_SetInfo ($this->GetIDForIdent("WaterContentIndoor"), "WSS");  

        $this->MaintainVariable('Auswertung', "Auswertung", VARIABLETYPE_STRING, "", 9, true);
        IPS_SetInfo ($this->GetIDForIdent("Auswertung"), "WSS");  
        $this->MaintainVariable('KlimaAussen', 'gefühltes Klima Aussen', VARIABLETYPE_STRING, "", 8, $create);
        $this->MaintainVariable('KlimaInnen', 'Klima Innen', VARIABLETYPE_STRING, "", 8, $create);
        IPS_SetInfo ($this->GetIDForIdent("KlimaInnen"), "WSS");  
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


    #---------------------------------------------------------------------------------#
    # Function: Update                                                               #
    #.................................................................................#
    # Beschreibung:                                                                   #
    #.................................................................................#
    # Parameters:    none                                                             #
    #.................................................................................#
    # Returns:       none                                                             #
    #---------------------------------------------------------------------------------#
    public function Update() {
        $result = 'Ergebnis konnte nicht ermittelt werden!';
        // Daten lesen
        $state = true;
        // Temp Outdoor
        $to = $this->ReadPropertyInteger('TempOutdoor');
        if ($to != 0) {
            $to = GetValue($to);
        } else {
            $this->SendDebug('UPDATE', 'Temperature Outdoor not set!');
            $state = false;
        }
        // Humidity Outdoor
        $ho = $this->ReadPropertyInteger('HumyOutdoor');
        if ($ho != 0) {
            $ho = GetValue($ho);
            // Kann man bestimmt besser lösen
            if ($ho < 1) {
                $ho = $ho * 100.;
            }
        } else {
            $this->SendDebug('UPDATE', 'Humidity Outdoor not set!');
            $state = false;
        }
        // Temp Indoor
        $ti = $this->ReadPropertyInteger('TempIndoor');
        if ($ti != 0) {
            $ti = GetValue($ti);
        } else {
            $this->SendDebug('UPDATE', 'Temperature Indoor not set!');
            $state = false;
        }
        // Humidity Indoor
        $hi = $this->ReadPropertyInteger('HumyIndoor');
        if ($hi != 0) {
            $hi = GetValue($hi);
            // Kann man bestimmt besser lösen
            if ($hi < 1) {
                $hi = $hi * 100.;
            }
        } else {
            $this->SendDebug('UPDATE', 'Humidity Indoor not set!');
            $state = false;
        }
        // All okay
        if ($state == false) {
            $this->SetValueString('Result', $result);
            return;
        }
        // Minus oder Plus ;-)
        if ($ti >= 0) {
            // Plustemperaturen
            $ao = 7.5;
            $bo = 237.7;
            $ai = $ao;
            $bi = $bo;
        } else {
            // Minustemperaturen
            $ao = 7.6;
            $bo = 240.7;
            $ai = $ao;
            $bi = $bo;
        }
        // universelle Gaskonstante in J/(kmol*K)
        $rg = 8314.3;
        // Molekulargewicht des Wasserdampfes in kg
        $m = 18.016;
        // Umrechnung in Kelvin
        $ko = $to + 273.15;
        $ki = $ti + 273.15;
        // Berechnung Sättigung Dampfdruck in hPa
        $so = 6.1078 * pow(10, (($ao * $to) / ($bo + $to)));
        $si = 6.1078 * pow(10, (($ai * $ti) / ($bi + $ti)));
        // Dampfdruck in hPa
        $do = ($ho / 100) * $so;
        $di = ($hi / 100) * $si;
        // Berechnung Taupunkt Aussen
        $vo = log10($do / 6.1078);
        $dpo = $bo * $vo / ($ao - $vo);
        // Berechnung Taupunkt Innen
        $vi = log10($di / 6.1078);
        $dpi = $bi * $vi / ($ai - $vi);
        // Speichern Taupunkt?
        $update = $this->ReadPropertyBoolean('CreateDewPoint');
        if ($update == true) {
            $this->SetValue('DewPointOutdoor', $dpo);
            $this->SetValue('DewPointIndoor', $dpi);
        }
        // WaterContent
        $wco = pow(10, 5) * $m / $rg * $do / $ko;
        $wci = pow(10, 5) * $m / $rg * $di / $ki;
        // Speichern Wassergehalt?
        $update = $this->ReadPropertyBoolean('CreateWaterContent');
        if ($update == true) {
            $this->SetValue('WaterContentOutdoor', $wco);
            $this->SetValue('WaterContentIndoor', $wci);
        }
        // Result (diff out / in)
        $wc = $wco - $wci;
        $wcy = ($wci / $wco) * 100;
        $difference = round(($wcy - 100) * 100) / 100;
        if ($wc >= 0) {
            $difference = round((100 - $wcy) * 100) / 100;
            $result = 'Lüften führt nicht zur Trocknung der Innenraumluft.';
            $hint = false;
        } elseif ($wcy <= 110) {
            $result = 'Zwar ist es innen etwas feuchter, aber es lohnt nicht zu lüften!';
            $hint = false;
        } else {
            $result = 'Lüften führt zur Trocknung der Innenraumluft!';
            $hint = true;
        }
        $this->SetValue('Result', $result);
        $this->SetValue('Hint', $hint);
        $this->SetValue('Difference', $difference);
        $scriptId = $this->ReadPropertyInteger('ScriptMessage');
        $threshold = $this->ReadPropertyInteger('MessageThreshold');
        if ($scriptId != 0 && $hint == true && $difference > $threshold) {
            $room = $this->ReadPropertyString('RoomName');
            $time = $this->ReadPropertyInteger('LifeTime');
            $time = $time * 60;
            if (IPS_ScriptExists($scriptId)) {
                if ($time > 0) {
                    IPS_RunScriptWaitEx($scriptId,
                        ['action'       => 'add', 'text' => $room.': '.$result, 'expires' => time() + $time,
                            'removable' => true, 'type' => 3, 'image' => 'Ventilation', ]);
                } else {
                    IPS_RunScriptWaitEx($scriptId,
                        ['action'       => 'add', 'text' => $room.': '.$result,
                            'removable' => true, 'type' => 3, 'image' => 'Ventilation', ]);
                }
            }
        }

        // gefühltes Klima auswerten
        $value = $this->GetValue("DewPointOutdoor");
        if($value <= 13){
            $this->SetValue("KlimaAussen", "trocken");
        }
        elseif($value > 13 and $value <16){
            $this->SetValue("KlimaAussen", "trocken - feucht");
        }
        elseif($value >= 16 and $value <18){
            $this->SetValue("KlimaAussen", "feucht");
        }
        elseif($value > 16 and $value <18){
            $this->SetValue("KlimaAussen", "feucht - schwül");
        }
        elseif($value >= 18 and $value <21){
            $this->SetValue("KlimaAussen", "schwül");
        }
        elseif($value >=23){
            $this->SetValue("KlimaAussen","drückend");
        }
        
        // Klima Innen
        // wenn Taupunkt über 13 liegt => Raum zu Feucht
        $TPInnen = $this->GetValue("DewPointIndoor");
        $TI08 = Getvalue($this->ReadPropertyInteger("TempIndoor")) * 0.8;
        if($TI08 <= $TPInnen){
            $this->SetValue("KlimaInnen", "zu Feucht");
        } 
        else{
            $this->SetValue("KlimaInnen", "OK");
        }
        // Kritisch wenn innen Temperatur unterhalb des Taupunktes Innen liegt. Grenzwert ist 80%
        //Bsp.: TP = 14 *  und T = 16 * 0,8 = 12,8 => kritisch 

        $this->warning();

    }

    #---------------------------------------------------------------------------------#
    # Function: warning                                                               #
    #.................................................................................#
    # Beschreibung:                                                                   #
    #.................................................................................#
    # Parameters:    nnone                                                            #
    #.................................................................................#
    # Returns:       none                                                             #
    #---------------------------------------------------------------------------------#
    private function warning(){
        $Diff = $this->GetValue('Difference');
        $Hinweis = $this->GetValue('Hint');  //Bool 
        $Tin = $this->ReadPropertyInteger("TempIndoor");
        $HumidtyID = $this->ReadPropertyInteger('HumyIndoor');
        $TPi = $this->getvalue("DewPointIndoor");
        $Humidity = getvalue($HumidtyID);
        if (IPS_VariableExists($this->ReadPropertyInteger('FensterKontakt'))){
            $windowId = $this->ReadPropertyInteger('FensterKontakt');
            $window = getValue($windowId);
            if($window){
                //prüfen wie lange das Fenster geöffnet ist - zumachen
                $t_open = IPS_GetVariable($windowId)['VariableChanged'];  // Wert in Unix time in Sekunden seit
                
                // wenn Meldung Lüften und Fenster > 5 Minuten offen dann Meldung Lüfen beendet.
                if(($this->getvalue('Auswertung') == 'lüften') && ((time() - $t_open) > 600)){
                    $this->SetValue('Auswertung', 'lüften beenden.');
                    $this->SetValue('WinOpen', true);
                }
                // wenn draussen zu feucht und Fenster auf dann Alarm
                elseif(!$Hinweis){
                    $this->SetValue('Auswertung', 'Fenster schliessen.');
                    $this->SetValue('WinOpen', true);
                }
            }
            else{
                $this->SetValue('WinOpen', false);
                // Fenster ist zu . relative Luftfeuchtigkeit >60% und Differenz >50% und Lüften erlaubt
                //if($TPi >13 and $Hinweis){
                if(($Humidity > 72) && ($Diff > 50) && $Hinweis){
                    $this->SetValue('Auswertung', 'Schimmel Alarm');
                    $VisID = 21477; 
                    #$VisID = $this->ReadPropertyInteger("VisID");
                    VISU_PostNotification ($VisID, 'Dringend Lüften-Schimmelalarm', 'Kinderzimmer', 'Info', 0);
                }    
                elseif (($Humidity > 60) && ($Diff > 50) && $Hinweis){
                    $this->SetValue('Auswertung', 'lüften!');
                    $VisID = 21477; 
                    #$VisID = $this->ReadPropertyInteger("VisID");
                    #VISU_PostNotification ($VisID, 'Bitte Lüften', 'Kinderzimmer', 'Info', 0);
                }
                elseif(($Humidity > 60) && ($Diff > 40) && $Hinweis){
                    $this->SetValue('Auswertung', 'gelegentlich lüften!'); 
                }
                elseif(($Tin *0.8)<$TPi && $Hinweis){
                    $this->SetValue('Auswertung', 'dringend lüften!');
                }
                else{
                    if ($Hinweis){
                        $this->SetValue('Auswertung', 'Lüften erlaubt.');
                    } else {
                        $this->SetValue('Auswertung', '');
                    }
                    
                } 
            }
            // wenn Werte ok dann Meldung zurücksetzen
            if(($Diff < 40)){
                $this->SetValue('Auswertung', '');
            }

        }
        else{
            // kein Fensterkontakt vorhanden
           
            // Fenster ist zu . relative Luftfeuchtigkeit >60% und Differenz >50% und Lüften erlaubt
            //if($TPi >13 and $Hinweis){
            if (($Humidity > 720) && ($Diff > 50) && $Hinweis){
                $this->SetValue('Auswertung', 'Schimmel Alarm');
                $VisID = 21477; 
                #$VisID = $this->ReadPropertyInteger("VisID");
                VISU_PostNotification ($VisID, 'Dringend Lüften - Schimmelalarm!', 'Kinderzimmer', 'Info', 0);
            }    
            elseif (($Humidity > 60) && ($Diff > 50) && $Hinweis){
                $this->SetValue('Auswertung', 'lüften!');
                $VisID = 21477; 
                #$VisID = $this->ReadPropertyInteger("VisID");
                VISU_PostNotification ($VisID, 'Bitte Lüften', 'Kinderzimmer', 'Info', 0);
            }
            elseif(($Humidity > 60) && ($Diff > 40) && $Hinweis){
                $this->SetValue('Auswertung', 'gelegentlich lüften!'); 
            }
            elseif(($Tin *0.8)<$TPi && $Hinweis){
                $this->SetValue('Auswertung', 'dringend lüften!');
            }
            else{
                if ($Hinweis){
                    $this->SetValue('Auswertung', 'Lüften erlaubt.');
                } else {
                    $this->SetValue('Auswertung', '');
                }
            } 
            if(($Diff < 40)){
                $this->SetValue('Auswertung', '');
            }
        }

    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TSH_Duration($id, $duration);
     *
     * @param int $duration Wartezeit einstellen.
     */
    public function Duration(int $duration)
    {
        IPS_SetProperty($this->InstanceID, 'UpdateTimer', $duration);
        IPS_ApplyChanges($this->InstanceID);
    }
    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TSH_SetMessageThreshold($id, $threshold);
     *
     * @param int MessageThreshold Schwellert einstellen.
     */
    public function MessageThreshold(int $threshold)
    {
        IPS_SetProperty($this->InstanceID, 'MessageThreshold', $threshold);
        IPS_ApplyChanges($this->InstanceID);
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


}
