<?php
/***************************************************************************
 * Title: NMAP
 *
 * Author: torsten.beck@onlinehome.e
 * 
 * GITHUB: <https://github.com/SymPiTo/MySymApps/tree/master/NMAP_>
 * 
 * Version: 1.0
 *************************************************************************** */
//require_once __DIR__ . '/../libs/MyTrait1';
require_once __DIR__.'/../libs/MyHelper.php';  // diverse Klassen

class NMap extends IPSModule {

       //Traits verbinden
       use DebugHelper,
       ModuleHelper;

# ___________________________________________________________________________ 
#    Section: Internal Modul Functions
#    Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung.
# ___________________________________________________________________________ 

  
    #-----------------------------------------------------------# 
    #    Function: Create                                       #
    #    Create() Wird ausgeführt, beim Anlegen der Instanz.    #
    #-----------------------------------------------------------#    
    

    public function Create() {
    //Never delete this line!
        parent::Create();

        //Register Properties from form.json
        $this->RegisterPropertyBoolean("Active", false);

        //$this->ReadPropertyFloat("NAME", 0.0);

        //$this->ReadPropertyInteger("NAME", 0);

        //$this->ReadPropertyString("NAME", "");

        // Register Profiles
        //$this->RegisterProfiles();

        //Register Variables
        $variablenID = $this->RegisterVariableBoolean ($Ident, $Name, $Profil, $Position);
        IPS_SetInfo ($variablenID, "WSS");
        IPS_SetHidden($variablenID, true); //Objekt verstecken

        $variablenID = $this->RegisterVariableFloat ($Ident, $Name, $Profil, $Position);
        IPS_SetInfo ($variablenID, "WSS");
        IPS_SetHidden($variablenID, true); //Objekt verstecken

        $variablenID = $this->RegisterPropertyInteger ($Name, $Standardwert);
        IPS_SetInfo ($variablenID, "WSS");
        IPS_SetHidden($variablenID, true); //Objekt verstecken

        $variablenID = $this->RegisterPropertyString ($Name, $Standardwert);
        IPS_SetInfo ($variablenID, "WSS");
        IPS_SetHidden($variablenID, true); //Objekt verstecken

        //Register Timer
        $this->RegisterTimer('Name', 0, '_PREFIX__Scriptname($_IPS[\'TARGET\']);');








    } //Function: Create End

    #---------------------------------------------------------------#
    #     Function: ApplyChanges                                    #
    #     ApplyChanges() Wird ausgeführt, beim anlegen der Instanz. #
    #     und beim ändern der Parameter in der Form                 #
    #---------------------------------------------------------------#
    public function ApplyChanges(){
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        //Never delete this line!
        parent::ApplyChanges();






        if($this->ReadPropertyBoolean("Active")){
            //Splitter oder IO verbinden

            //Filter setzen – ForwardData wird nur aufgerufen wenn Filter passt (string $ErforderlicheRegexRegel )$this->SetForwardDataFilter(".*");  
            //Filter setzen – ReceiveData wird nur aufgerufen wenn Filter passt (string $ErforderlicheRegexRegel )$this->SetReceiveDataFilter(".*");  
        }
        else {
            //Timer ausschalten
            $this->SetTimerInterval("Name", 0);
        }                   
    } //Function: ApplyChanges  End

 
    #------------------------------------------------------------# 
    #  Function: MessageSink                                     #
    #  MessageSink() wird nur bei registrierten                  #
    #  NachrichtenIDs/SenderIDs-Kombinationen aufgerufen.        #
    #------------------------------------------------------------#    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        switch ($Message) {
            case IPS_KERNELSTARTED: // Nach dem IPS-Start
                $this->KernelReady(); // Sagt alles.
                break;
        }
    }

    #-------------------------------------------------------------#
    #    Function: Destroy                                        #
    #        Destroy() wird beim löschen der Instanz              #
    #        und update der Module aufgerufen                     #
    #-------------------------------------------------------------#
    public function Destroy() {
        //Never delete this line!
        parent::Destroy();
    } //Function: Destroy End

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
       
    }  //xxxx End

#________________________________________________________________________________________
# Section: Private Functions
#    Die folgenden Funktionen stehen nur innerhalb des Moduls zur verfügung
#    Hilfsfunktionen: 
#_______________________________________________________________________________________


    /** Wird ausgeführt wenn der Kernel hochgefahren wurde. */
    protected function KernelReady(){
        $this->ApplyChanges();
    }



} //end Class


