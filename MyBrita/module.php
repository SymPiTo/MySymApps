<?php
/***************************************************************************
 * Title: _TITEL_
 *
 * Author: _AUTOR_
 * 
 * GITHUB: <https://github.com/SymPiTo/MySymApps/tree/master/_TITEL_>
 * 
 * Version: _VERSION_
 *************************************************************************** */
require_once __DIR__ . '/../libs/traits.php';
 

class MyBrita extends IPSModule {

    use 
    DebugHelper,
    TimerHelper;
 
   
/* 
___________________________________________________________________________ 
    Section: Internal Modul Funtions
    Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung.
___________________________________________________________________________ 
    */
    /* 
    ------------------------------------------------------------ 
        Function: Create  
        Create() Wird ausgeführt, beim anlegen der Instanz.
    -------------------------------------------------------------
    */
    public function Create() {
    //Never delete this line!
        parent::Create();

        //Register Properties from form.json
        $this->RegisterPropertyBoolean("active", false);

        //$this->ReadPropertyFloat("NAME", 0.0);

        $this->RegisterPropertyInteger("liter", 100);
        $this->RegisterPropertyInteger("lifetime", 4);
        
        //$this->ReadPropertyString("NAME", "");

        // Register Profiles
        //$this->RegisterProfiles();

        //Register Variables
        $variablenID = $this->RegisterVariableBoolean ("setNewFilter", "neuer Filter", '~Switch', 0);
        //IPS_SetInfo ($variablenID, "WSS");
        //IPS_SetHidden($variablenID, true); //Objekt verstecken

        $variablenID = $this->RegisterVariableBoolean ("incFilUsage", "neue Füllung", '~Switch', 1);

        $variablenID = $this->RegisterVariableString ("DateNewFilter", "Datum Filter eingesetzt");
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableString ("DateFilterEnd", "Datum Filter wechseln");
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableFloat ("Liter", "Liter befüllt");
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableString ("Meldung", "Wartungsmeldung");
        IPS_SetInfo ($variablenID, "WSS");

        $variablenID = $this->RegisterVariableInteger ("Fuellstand", "Verbrauchsanzeige");
        IPS_SetInfo ($variablenID, "WSS");
/*
        $variablenID = $this->RegisterVariableFloat ($Ident, $Name, $Profil, $Position);
        IPS_SetInfo ($variablenID, "WSS");
        IPS_SetHidden($variablenID, true); //Objekt verstecken

        $variablenID = $this->RegisterVariableInteger ($Name, $Standardwert);
        IPS_SetInfo ($variablenID, "WSS");
        IPS_SetHidden($variablenID, true); //Objekt verstecken

        $variablenID = $this->RegisterVariableString ($Name, $Standardwert);
        IPS_SetInfo ($variablenID, "WSS");
        IPS_SetHidden($variablenID, true); //Objekt verstecken

        //Register Timer
        $this->RegisterTimer('Name', 0, '_PREFIX__Scriptname($_IPS[\'TARGET\']);');
*/
        $this->RegisterCyclicTimer("FilterTimer", 23, 0, 0, 'Brita_checkFilter($_IPS[\'TARGET\']);', false);






        //Webfront Actions setzen
        $this->EnableAction("setNewFilter");
        $this->EnableAction("incFilUsage");
        
    } //Function: Create End
    /* 
    ------------------------------------------------------------ 
        Function: ApplyChanges  
        ApplyChanges() Wird ausgeführt, beim anlegen der Instanz.
        und beim ändern der Parameter in der Form
    -------------------------------------------------------------
    */
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();






        if($this->ReadPropertyBoolean("active")){
            //Splitter oder IO verbinden

            //Filter setzen – ForwardData wird nur aufgerufen wenn Filter passt (string $ErforderlicheRegexRegel )$this->SetForwardDataFilter(".*");  
            //Filter setzen – ReceiveData wird nur aufgerufen wenn Filter passt (string $ErforderlicheRegexRegel )$this->SetReceiveDataFilter(".*");  
        }
        else {
            //Timer ausschalten
            //$this->SetTimerInterval("Name", 0);
        }                   
    } //Function: ApplyChanges  End
    /* 
    ------------------------------------------------------------ 
        Function: Destroy  
            Destroy() wird beim löschen der Instanz 
            und update der Module aufgerufen
    -------------------------------------------------------------
    */
    public function Destroy() {
        //Never delete this line!
        parent::Destroy();
    } //Function: Destroy End
    /* 
    ------------------------------------------------------------ 
        Function: RequestAction  
            RequestAction() wird von schaltbaren Variablen 
            aufgerufen.
    -------------------------------------------------------------
    */ 
    public function RequestAction($Ident, $Value) {     
        switch($Ident) {
            case "setNewFilter":
                if ($Value == true){ 
                    $this->setNewDate();
                    $this->SetValue($Ident, $Value);
                }
                else {

                }
                break;
                case "incFilUsage":
                    $this->SetValue($Ident, $Value);
                    if ($Value == true){ 
                        $this->incFill();
                        $this->SetValue($Ident, false);
                    }
                    else {
    
                    }
                    break;
            default:
                throw new Exception("Invalid Ident");
            }
    } //Function: RequestAction End
    /* 




_____________________________________________________________________________________________________________________
    Section: Public Funtions
    Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt:
    
    FSSC_XYFunktion($Instance_id, ... );
________________________________________________________________________________________________________________________ 
*/
    //-----------------------------------------------------------------------------
    /* Function: checkFilter
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function checkFilter(){
       //Berechnen ob die maximale Filterzeit erreicht ist
        $startDatum = getvalue(29377);
        $timestamp = strtotime($startDatum);
        $startDatum = date('Y-m-d', strtotime($startDatum));
        $heute = date('Y-m-d');

        $origin = new DateTime($startDatum);
        $target = new DateTime($heute);
        $interval = $origin->diff($target);
        $Differenz = $interval->format('%a');
        $maxTage = $this->ReadPropertyInteger("lifetime") * 7;
        if ($Differenz<8) {
            $this->SetValue("Fuellstand", 100);
        }
        elseif($Differenz<15){
            $this->SetValue("Fuellstand", 75);
        }
        elseif($Differenz<22){
            $this->SetValue("Fuellstand", 50);
        }
        elseif($Differenz<29){
            $this->SetValue("Fuellstand", 25);
        }
        else{
            $this->SetValue("Fuellstand", 0);
        }
        if($Differenz > $maxTage){
            // Filterzeit ist abgelaufen
            $this->GetValue("Meldung", "Bitte Filter wechseln");
            $this->SetValue("Fuellstand", 0);
        }
        else{
            //püfen ob mehr als 100 Liter gefiltert wurden
            $Fill = $this->GetValue("Liter");
            if($Fill > 100){
                //Filter ist aufgebraucht.
                $this->SetValue("Meldung", "Bitte Filter wechseln");
            }
        }

 
    }  //checkFilter End

    //-----------------------------------------------------------------------------
    /* Function: setNewDate
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function setNewDate(){
       $this->SetValue("DateNewFilter", date("j-n-Y"));
       $date = new DateTime("+30 days");
       $endDatum = $date->format("d-m-Y"); 
       $this->SetValue("DateFilterEnd", $endDatum);
       // Timer einschalten
       IPS_SetEventActive($this->GetIDForIdent("FilterTimer"), true);
       $this->SetValue("Meldung", "");
       $this->SetValue("Liter", 0);
       $this->SetValue("setNewFilter", false);
    }  //setNewDate End

    //-----------------------------------------------------------------------------
    /* Function: incFill
    ...............................................................................
    Beschreibung
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function incFill(){
        $Menge = $this->GetValue("Liter");
        $this->SetValue("Liter", $Menge + 1.5) ;
        $this->checkFilter();
     }  //incFill End
/* 
_______________________________________________________________________
    Section: Private Funtions
    Die folgenden Funktionen sind nur zur internen Verwendung verfügbar
    Hilfsfunktionen
______________________________________________________________________
*/ 
 
    /** Wird ausgeführt wenn der Kernel hochgefahren wurde. */
    protected function KernelReady(){
        $this->ApplyChanges();
    }
    /* ----------------------------------------------------------------------------
     Function: GetIPSVersion()
    ...............................................................................
        gibt die eingestezte IPS Version zurück
    ...............................................................................
    Parameters: 
        none
    ..............................................................................
    Returns:   
        $ipsversion
    ------------------------------------------------------------------------------- */
    protected function GetIPSVersion(){
        $ipsversion = floatval(IPS_GetKernelVersion());
        if ($ipsversion < 4.1) {    // 4.0
            $ipsversion = 0;
        } elseif ($ipsversion >= 4.1 && $ipsversion < 4.2){ // 4.1
            $ipsversion = 1;
        } elseif ($ipsversion >= 4.2 && $ipsversion < 4.3){ // 4.2
            $ipsversion = 2;
        } elseif ($ipsversion >= 4.3 && $ipsversion < 4.4){ // 4.3
            $ipsversion = 3;
        } elseif ($ipsversion >= 4.4 && $ipsversion < 5){ // 4.4
            $ipsversion = 4;
        } else {  // 5
            $ipsversion = 5;
        }
        return $ipsversion;
    } //Function: GetIPSVersion End
    

} //end Class


