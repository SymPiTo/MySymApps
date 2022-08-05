<?php
/* --------------------------------------------------------------------------- 
  TRAITS: MyHelper
...............................................................................
        
  * LogErrorToFile
  * MyLogger
  * SendDebug

  * DecodeUTF8
 
-------------------------------------------------------------------------------*/
trait LogErrorToFile
{
    /**
     * Ergänzt SendDebug um die Möglichkeit Objekte und Array auszugeben.
     *
     * @access protected
     * @param string $Message Nachricht für Data.
     * @param WebSocketFrame|mixed $Data Daten für die Ausgabe.
     * @return int $Format Ausgabeformat für Strings.
     */
        protected function ModErrorLog($ModName, $text, $array){
        {
            $path = "/home/pi/pi-share/"; 
            $file=$ModName.".log";
            
            $datum = date("d.m.Y");
            $uhrzeit = date("H:i");
            $logtime = $datum." - ".$uhrzeit." Uhr";
            if (!$array){

                    $array = '-';
            }

            //prüfen, ob file vorhanden.
            //if (file_exists($path.$filename)) {



                    if(($FileHandle = fopen($path.$file, "a")) === false) {
                            //SetValue($ID_OutEnabled, false);
                            Exit;
                    }
                    if (is_array($array)){
                            //$comma_seperated=implode("\r\n",$array);
                            $comma_seperated=print_r($array, true);
                    }
                    else {
                            $comma_seperated=$array;
                    }
                    fwrite($FileHandle, $logtime.": ".$text.": ");
                    fwrite($FileHandle, $comma_seperated."\r\n");
                    fclose($FileHandle);
           //}
        }
    }
}

/**
 * Helper class for the debug output.
 */
trait DebugHelper
{
    /**
     * Adds functionality to serialize arrays and objects.
     *
     * @param string $msg    Title of the debug message.
     * @param mixed  $data   Data output.
     * @param int    $format Output format.
     */
    protected function SendDebug($msg, $data, $format = 0)
    {
        if (is_object($data)) {
            foreach ($data as $key => $value) {
                $this->SendDebug($msg.':'.$key, $value, 1);
            }
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->SendDebug($msg.':'.$key, $value, 0);
            }
        } elseif (is_bool($data)) {
            parent::SendDebug($msg, ($data ? 'TRUE' : 'FALSE'), 0);
        } else {
            parent::SendDebug($msg, $data, $format);
        }
    }
}


trait NMapHelper {
    //-----------------------------------------------------------------------------
    /* Function: checkPortState($ip, $port)
    ...............................................................................
    Beschreibung:
        prüft ob ein bestimmter Port einer TCP-Adresse offen ist
    ...............................................................................
    Parameters: 
        $ip     = string '192.168.178.28'
        $port   = string '8888'
    ...............................................................................
    Returns:    
        true  => Port is open
        false => Port is closed
    ------------------------------------------------------------------------------  */
    protected function checkPortState($ip, $port, $type=true){
      
        $cmd = "sudo nmap -p T:".$port." ".$ip;
        // linux Befehl ausführen mit  ``'
        $output = `$cmd`;
        $posOpen = strpos($output, "open");
        $posClose = strpos($output, "closed");
        //open Textstring gefunden
        if($posOpen != false){
          $result = "open";
        }
        //closed Textstring gefunden
        if($posClose!= false){
          $result = "closed";
        }
        if(($posOpen == false) & ($posClose!= false)){
            //Keine Antwort
            $result = false;
        }
        if(!$type){
            if($result == "open"){
                $result = true;
            } else{
                $result = false;
            }
        }


  
        return $result;
    }
}

/**
 * Helper class for create variable profiles.
 */
trait ProfileHelper
{
    /**
     * Create the profile for the given type, values and associations.
     *
     * @param string $vartype      Type of the variable.
     * @param string $name         Profil name.
     * @param string $icon         Icon to display.
     * @param string $prefix       Variable prefix.
     * @param string $suffix       Variable suffix.
     * @param int    $minvalue     Minimum value.
     * @param int    $maxvalue     Maximum value.
     * @param int    $stepsize     Increment.
     * @param int    $digits       Decimal places.
     * @param array  $associations Associations of the values.[key, value,icon,color]
     */
    protected function RegisterProfile($vartype, $name, $icon, $prefix = '', $suffix = '', $minvalue = 0, $maxvalue = 0, $stepsize = 0, $digits = 0, $associations = null)
    {
        if (!IPS_VariableProfileExists($name)) {
            switch ($vartype) {
                case vtBoolean:
                    $this->RegisterProfileBoolean($name, $icon, $prefix, $suffix, $associations);
                    break;
                case vtInteger:
                    $this->RegisterProfileInteger($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $associations);
                    break;
                case vtFloat:
                    $this->RegisterProfileFloat($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $associations);
                    break;
                case vtString:
                    $this->RegisterProfileString($name, $icon);
                    break;
            }
        }
        return $name;
    }
    /**
     * Create the profile for the given type with the passed name.
     *
     * @param string $name    Profil name.
     * @param string $vartype Type of the variable.
     */
    protected function RegisterProfileType($name, $vartype)
    {
        if (!IPS_VariableProfileExists($name)) {
            IPS_CreateVariableProfile($name, $vartype);
        } else {
            $profile = IPS_GetVariableProfile($name);
            if ($profile['ProfileType'] != $vartype) {
                throw new Exception('Variable profile type does not match for profile '.$name);
            }
        }
    }
    /**
     * Create a profile for boolean values.
     *
     * @param string $name   Profil name.
     * @param string $icon   Icon to display.
     * @param string $prefix Variable prefix.
     * @param string $suffix Variable suffix.
     * @param array  $asso   Associations of the values.
     */
    protected function RegisterProfileBoolean($name, $icon, $prefix, $suffix, $asso)
    {
        $this->RegisterProfileType($name, vtBoolean);
        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        if (($asso !== null) && (count($asso) !== 0)) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $ass[1], $ass[2], $ass[3]);
            }
        }
    }
    /**
     * Create a profile for integer values.
     *
     * @param string $name     Profil name.
     * @param string $icon     Icon to display.
     * @param string $prefix   Variable prefix.
     * @param string $suffix   Variable suffix.
     * @param int    $minvalue Minimum value.
     * @param int    $maxvalue Maximum value.
     * @param int    $stepsize Increment.
     * @param int    $digits   Decimal places.
     * @param array  $asso     Associations of the values.
     */
    protected function RegisterProfileInteger($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $asso)
    {
        $this->RegisterProfileType($name, vtInteger);
        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileDigits($name, $digits);
        /* Not correct for icon visuality (0-100)
        if (($asso !== null) && (count($asso) !== 0)) {
            $minvalue = 0;
            $maxvalue = 0;
        }
        */
        IPS_SetVariableProfileValues($name, $minvalue, $maxvalue, $stepsize);
        if (($asso !== null) && (count($asso) !== 0)) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $ass[1], $ass[2], $ass[3]);
            }
        }
    }
    /**
     * Create a profile for float values.
     *
     * @param string $name     Profil name.
     * @param string $icon     Icon to display.
     * @param string $prefix   Variable prefix.
     * @param string $suffix   Variable suffix.
     * @param int    $minvalue Minimum value.
     * @param int    $maxvalue Maximum value.
     * @param int    $stepsize Increment.
     * @param int    $digits   Decimal places.
     * @param array  $asso     Associations of the values.
     */
    protected function RegisterProfileFloat($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $asso)
    {
        $this->RegisterProfileType($name, vtFloat);
        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileDigits($name, $digits);
        if (($asso !== null) && (count($asso) !== 0)) {
            $minvalue = 0;
            $maxvalue = 0;
        }
        IPS_SetVariableProfileValues($name, $minvalue, $maxvalue, $stepsize);
        if (($asso !== null) && (count($asso) !== 0)) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $ass[1], $ass[2], $ass[3]);
            }
        }
    }
    /**
     * Create a profile for string values.
     *
     * @param string $name   Profil name.
     * @param string $icon   Icon to display.
     * @param string $prefix Variable prefix.
     * @param string $suffix Variable suffix.
     */
    protected function RegisterProfileString($name, $icon, $prefix, $suffix)
    {
        $this->RegisterProfileType($name, IPSVarType::vtString);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileIcon($name, $icon);
    }
}
/**
 * Helper class to create timer and events.
 */
trait TimerHelper
{
    /**
     * Create a cyclic timer.
     *
     * @param string $ident  Name and ident of the timer.
     * @param int    $hour   Start hour.
     * @param int    $minute Start minute.
     * @param int    $second Start second.
     * @param int    $script Script ID.                 _PREFIX__Scriptname($_IPS[\'TARGET\'])
     * @param bool   $active True to activate the timer, oterwise false.
     */
    protected function RegisterCyclicTimer($ident, $hour, $minute, $second, $script, $active)
    {
        $id = @$this->GetIDForIdent($ident);
        $name = $ident;
        if ($id && IPS_GetEvent($id)['EventType'] != 1) {
            IPS_DeleteEvent($id);
            $id = 0;
        }
        if (!$id) {
            $id = IPS_CreateEvent(1);
            IPS_SetParent($id, $this->InstanceID);
            IPS_SetIdent($id, $ident);
        }
        IPS_SetName($id, $name);
        // IPS_SetInfo($id, "Update Timer");
        // IPS_SetHidden($id, true);
        IPS_SetEventScript($id, $script);
        if (!IPS_EventExists($id)) {
            throw new Exception("Ident with name $ident is used for wrong object type");
        }
        //IPS_SetEventCyclic($id, 0, 0, 0, 0, 0, 0);
        IPS_SetEventCyclicTimeFrom($id, $hour, $minute, $second);
        IPS_SetEventActive($id, $active);
    }
}


trait VersionHelper{		
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
}