<?php
/* --------------------------------------------------------------------------- 
  TRAITS: MyHelper
...............................................................................
        
  * MyLogger
  * SendDebug

  * DecodeUTF8
 
-------------------------------------------------------------------------------*/
trait DebugHelper {
        /* --------------------------------------------------------------------------- 
         Function: ModErrorLog
        ...............................................................................
         erweiterte Log Funktion die in eine LogDatei schreibt.
        ...............................................................................
        Parameters: 
           ModName        -   Name des Moduls
           text           -   Beschreibung
           array          -   Wert (array oder text)
        ...............................................................................
        Returns:    
                none
        -------------------------------------------------------------------------------*/
        protected function ModErrorLog($ModName, $text, $array){
                $path = "/home/pi/pi-share/"; 
                $file=$ModName.".log";
                $datum = date("d.m.Y");
                $uhrzeit = date("H:i");
                $logtime = $datum." - ".$uhrzeit." Uhr";
                if (!$array){
                    $array = '-';
                }
                if(($FileHandle = fopen($path.$file, "a")) === false) {
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
        }
 
        /* --------------------------------------------------------------------------- 
         Function: SendDebug
        ...............................................................................
         Adds functionality to serialize arrays and objects.
        ...............................................................................
        Parameters: 
           msg        -   Title of the debug message.
           data       -   Data output.
           format     -   Output format.
        ...............................................................................
        Returns:    
                none
        -------------------------------------------------------------------------------*/
        protected function SendDebug($msg, $data, $format = 0){
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


trait UTF8Coder1{

    /**
     * Führt eine UTF8-Dekodierung für einen String oder ein Objekt durch (rekursiv)
     *
     * @access private
     * @param string|object $item Zu dekodierene Daten.
     * @return string|object Dekodierte Daten.
     */
    private function DecodeUTF8($item)
    {
        if (is_string($item)) {
            $item = utf8_decode($item);
        } elseif (is_object($item)) {
            foreach ($item as $property => $value) {
                $item->{$property} = $this->DecodeUTF8($value);
            }
        }
        return $item;
    }

    /**
     * Führt eine UTF8-Enkodierung für einen String oder ein Objekt durch (rekursiv)
     *
     * @access private
     * @param string|object $item Zu Enkodierene Daten.
     * @return string|object Enkodierte Daten.
     */
    private function EncodeUTF8($item)
    {
        if (is_string($item)) {
            $item = utf8_encode($item);
        } elseif (is_object($item)) {
            foreach ($item as $property => $value) {
                $item->{$property} = $this->EncodeUTF8($value);
            }
        }
        return $item;
    }
}

 
    /***************************************************************************
    * Name:  ProfileHelper - TRAIT
    * 
    * Helper class for create variable profiles.
    *
    * Functions:    
    *   RegisterProfile($vartype, $name, $icon, $prefix = '', $suffix = '', $minvalue = 0, $maxvalue = 0, $stepsize = 0, $digits = 0, $associations = null)
    *   RegisterProfileType($name, $vartype)
    *   RegisterProfileBoolean($name, $icon, $prefix, $suffix, $asso)
    *   RegisterProfileInteger($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $asso)
    *   RegisterProfileFloat($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $asso)
    *   RegisterProfileString($name, $icon, $prefix, $suffix)
    * 
    * Parameters:  
    *   $vartype      Type of the variable.
    *   $name         Profil name.
    *   $icon         Icon to display.
    *   $prefix       Variable prefix.
    *   $suffix       Variable suffix.
    *   $minvalue     Minimum value.
    *   $maxvalue     Maximum value.
    *   $stepsize     Increment.
    *   $digits       Decimal places.
    *   $associations Associations of the values.[key, value,icon,color]
    * 
    * Returns:  none
    *************************************************************************** */
trait ProfileHelper {
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
        $output_file = "/var/lib/symcon/modules/MySymApps/NMap/file.xml";
        if (file_exists($output_file)) {
            unlink($output_file);
         }
        $cmd = "sudo nmap -p T:8888 -oX /var/lib/symcon/modules/MySymApps/NMap/file.xml 192.168.178.28";
        exec($cmd, $retval);
        sleep(1000);
        $xml=simplexml_load_file('/var/lib/symcon/modules/MySymApps/NMap/file.xml') or die("Error: Cannot create object");
        $PortState = $xml->host->ports->port->state['state'];
        $PortState = json_decode(json_encode($PortState),true);
        if($PortState[0] === "open"){
            if($type){
                return 1;
            } else{
                return "open";
            }
        }
        if($PortState[0] === "closed"){
            if($type){
                return 0;
            } else{
                return "closed";
            }
        }
    }
}