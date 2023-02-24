<?

 #******************************************************************************#
 # Title : MyPC                                                                 #
 #                                                                              #
 # Author: PiTo                                                                 #
 #                                                                              #
 # GITHUB: <https://github.com/SymPiTo/MySymDevices/tree/master/DenonCeol>      #
 #                                                                              #
 # Version: 1.0.1  20220604                                                     #
 #******************************************************************************#
 # _____________________________________________________________________________#
 #    Section: Beschreibung                                                     #
 #    Das Modul dient zur Steuerung des Denon CEOL Players.                     #
 #    Über eine UPNP Schnittstelle.                                             #
 #                                                                              #
 # _____________________________________________________________________________#
require_once(__DIR__ . "/../libs/MyHelper.php");

class MyPC extends IPSModule{
	use DebugHelper;
	use ModuleHelper;
	use NMapHelper;
	use EventHelper;

#______________________________________________________________________________________________________________________________________________
#           Section: Internal Module Functions                                                                                                 
#           Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung                                                             
#______________________________________________________________________________________________________________________________________________

	/*
	#---------------------------------------------------------------------#
	#       Function: Create()                                            #
	#       Create() wird ausgeführt, beim Anlegen der Instanz.           #
	#       Wird ausgeführt beim symcon Neustart                          #
	#---------------------------------------------------------------------#
	*/
	public function Create() {
		parent::Create();
		# Register Form Properties
		$this->RegisterPropertyBoolean('active', 'false');
		$this->RegisterPropertyString('mac', '');
		$this->RegisterPropertyInteger('limitDown', 200);
		$this->RegisterPropertyInteger('limitUp', 600);
		$this->RegisterPropertyInteger('update', 30);
		$this->RegisterPropertyInteger("FSM", 0);
		$this->RegisterPropertyString("IP", "192.168.178.32");
		$this->RegisterPropertyInteger("I_ID", 0);
		$this->RegisterPropertyString("user", "");
		$this->RegisterPropertyString("password", "");
		$this->RegisterPropertyInteger('FSMState', 0);
		
		// Register Variable
		$variablenID = $this->RegisterVariableBoolean('StopOff', 'Stop SwitchOff', '~Switch', 0);
		IPS_SetInfo ($variablenID, "WSS");
		$variablenID = $this->RegisterVariableBoolean('PC_Switch', 'PC Schalter', '~Switch', 1);
		IPS_SetInfo ($variablenID, "WSS");
		$variablenID = $this->RegisterVariableBoolean('Mode', 'PC Off Mode ', 'Mode', 2);
		IPS_SetInfo ($variablenID, "WSS");
		# 0 = PC Stromlos und aus
		# 1 = PC ist aus
		# 2 = PC fährt hoch
		# 3 = PC ist ein
		# 4 = PC fährt runter
		# 5 = Netzwerkkarte nicht erreichbar
		$variablenID = $this->RegisterVariableInteger('PC_State', 'PC Status ', 'PC_Status', 3);
		IPS_SetInfo ($variablenID, "WSS");

		# Register Timer
		$this->RegisterTimer("Update_Timer", 0, 'PC_SetPCStatus($_IPS[\'TARGET\']);');  
 

		# Register Actions
		$this->EnableAction("PC_Switch");
		$this->EnableAction("Mode");
		$this->EnableAction("StopOff");

	}
	
	#--------------------------------------------------------------------------------#
	#       Function: ApplyChanges()                                                 #
	#       Einträge vor ApplyChanges() werden sowohl beim Systemstart               #
	#       als auch beim Ändern der Parameter in der Form ausgeführt.               #
	#       ApplyChanges() wird ausgeführt, beim Anlegen der Instanz                 #
	#       und beim ändern der Parameter in der Form                                #
	#--------------------------------------------------------------------------------#
	public function ApplyChanges(){
		$this->RegisterMessage(0, IPS_KERNELSTARTED);
		$this->RegisterMessage(0, IPS_KERNELSHUTDOWN);
		
		if (IPS_GetKernelRunlevel() <> KR_READY) {
			$this->LogMessage('ApplyChanges: Kernel is not ready! Kernel Runlevel = '.IPS_GetKernelRunlevel(), KL_ERROR);
			//ApplyChanges wird über MessageSink nachgestartet.
			return;
		}
	
		//Never delete this line!
		parent::ApplyChanges();
		
		/*
		 
		# Register Event I<
		$limit = $this->ReadPropertyInteger("limit");		//Variable der Strommessung
		$ParentID = $this->InstanceID;
		$Typ = 0;	//Event auf Variable
		$Ident = "Eve_I";
		$Name = "Event_I_min";
		$PID = $this->ReadPropertyInteger("I_ID");  //ID der zu überwachende Variable
		$cmd = "PC_SetDelayTimer($this->InstanceID);";
		$EventID = $this->RegisterVarEvent($Name, $Ident, $Typ, $ParentID, 0, 3, $PID, $cmd );
		IPS_SetEventTriggerValue($EventID, $limit);	 
		IPS_SetEventActive ($EventID, false);
		*/
		 
		# Register Event I bei Änderung
		$ParentID = $this->InstanceID;
		$Typ = 0;	//Event auf Variable
		$Ident = "Eve_Ion";
		$Name = "Event_I_on";
		$PID = $this->ReadPropertyInteger("I_ID");  //ID der zu überwachende Variable
		#$cmd = "PC_SetPCStatus(".$_IPS['TARGET'].");";
		$cmd = "PC_SetPCStatus($this->InstanceID);";
		$EventID = $this->RegisterVarEvent($Name, $Ident, $Typ, $ParentID, 0, 1, $PID, $cmd );

		$active = $this->ReadPropertyBoolean("active");
		$UpdateInterval = $this->ReadPropertyInteger("update") * 1000;
		if($active){
			$this->SetTimerInterval("Update_Timer", $UpdateInterval);
		}
		else{
			$this->SetTimerInterval("Update_Timer", 0);
		}
		 
	}
	
	#--------------------------------------------------------------------------------------------#
	#       Function: MessageSink()                                                              #
	#       MessageSink() IPS Standard Funktion                                                  #
	#       auf System-oder eigen definierten Meldungen reagieren.                               #
	#--------------------------------------------------------------------------------------------#
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data){

		Switch($Message) {
		Case IPS_KERNELSTARTED:
			$this->LogMessage('MessageSink: Kernel hochgefahren', KL_MESSAGE);
			$this->ApplyChanges();
			break;
		Case IPS_KERNELSHUTDOWN:
			$this->LogMessage('MessageSink: Kernel runtergefahren', KL_MESSAGE);
			//Timer ausschalten ausschalten.
			$this->SetTimerInterval("Update_Timer", 0);
			break;
		}
	}

	#------------------------------------------------------------# 
    #    Function: RequestAction                                 #
    #        RequestAction() wird von schaltbaren Variablen      #
    #        aufgerufen.                                         #
    #------------------------------------------------------------#
    public function RequestAction($Ident, $Value) {     
        switch($Ident) {
            case "PC_Switch":
                if ($Value == true){ 
					$this->SendDebug("Action_Command","PC_Switch On");
					$this->SwitchPC(true);
					$this->SetValue("PC_Switch", true);
                }
                else {
					$this->SendDebug("Action_Command","PC_Switch On");
					$this->SwitchPC(false);
					$this->SetValue("PC_Switch", false);
                }
                break;
			case "Mode":
				if ($Value == true){ 
					$this->SetValue("Mode", true);
				}
				else {
					$this->SetValue("Mode", false);
				}
				break;
            case "StopOff":
                if ($Value == true){ 
					$this->SetValue("StopOff", true);
					$this->StopSwitchOff();
					IPS_Sleep(1000);
					$this->SetValue("StopOff", false);
                }
                else {
					$this->SetValue("StopOff", false);
                }
                break;
            default:
                throw new Exception("Invalid Ident");
            }
     
    } //Function: RequestAction End



#________________________________________________________________________________________________________________________
# Section: Public Functions                                                                                               
# Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die 'Module Control' eingefügt wurden.   
# Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt.      
# GS_XYFunktion($Instance_id, ... );                                                                                      
#________________________________________________________________________________________________________________________

	#-----------------------------------------------------------------------------
	# Function: StopSwitchOff                                                      
	#...............................................................................
	# Beschreibung : Unterbricht das Herunterfahren des Rechners                                 
	#...............................................................................
	# Parameters:                                                                   
	#    none                                                                       
	#...............................................................................
	# Returns :                                                                      
	#------------------------------------------------------------------------------  

	public Function StopSwitchOff() {
		$PCip = $this->ReadPropertyString("IP");
		$user = $this->ReadPropertyString("user");
		$passwort = $this->ReadPropertyString("password");
		`net rpc shutdown -C $PCip -U $user%$passwort`;
	}

	#-----------------------------------------------------------------------------
	# Function: SwitchPC                                       
	#...............................................................................
	# Beschreibung : PC ein und ausschalten                               
	#...............................................................................
	# Parameters:                                                                   
	#    $state:  true/false                                                                       
	#...............................................................................
	# Returns :                                                                      
	#------------------------------------------------------------------------------  

	public Function SwitchPC($state) {
		$this->SendDebug("SwitchPC","Status: ". $state);
		$user = $this->ReadPropertyString("user"); 
		if ($state){
			$this->setValue("PC_State", 2);
			$this->SendDebug("SwitchPC","Status: ". $state. " - ". "starting PC.");
			//Steckdose einschalten, falls nicht ein
			$ParentID = $this->ReadPropertyInteger('FSM');
			$StatID = @IPS_GetObjectIDByName("STATE", $ParentID);
			if ($StatID === false){
				
				$this->SendDebug("Meldung","Objekt nicht gefunden!",0);
			}
			else {
				//Steckdose ist ein bzw.ausgeschaltet
				$state = GetValueBoolean($StatID);
			}

			if (!$state){
				    // Steckdose einschalten
					$this->SendDebug("SwitchPC","Status: ". $state. " - ". "Steckdose einschalten.");
					HM_WriteValueBoolean($ParentID, "STATE", true);
					//warten bis Netzwerkkarte vom Rechner erreichbar ist.
					$PCon = $this->waitHostUp(100, 10);
					$this->SendDebug("SwitchPC","PC erreichbar: ". $PCon );
			}
			else {
				//Steckdose ist schon ein.
				IPS_LogMessage("MyPC".$user, "- Steckdose ist ein - warte auf Verbindung zur Netzwerkkarte.");
				$PCon = $this->waitHostUp(60, 0);
				$this->SendDebug("SwitchPC","PC erreichbar: ". $PCon );
				 
			}
			if($PCon) {
				// PC einschalten
				$this->SendDebug("SwitchPC".$user,"Rechner ist erreichbar und wird nun hochgefahren." ); 
				 
				$this->setValue("PC_State", 2);
				$this->SendDebug("SwitchPC","PC hochfahren WOL");
				$result = $this->wol($this->ReadPropertyString("mac"));
				If ($result){
					$this->SendDebug("SwitchPC".$user,"PC erfolgreich hochgefahren." ); 
					 
					 
				}
				else{
					 
					$this->SendDebug("SwitchPC".$user,"PC lässt sich nicht hochfahren.");
				}
			}
			else {
				//Rechner ist nicht erreichbar
				$this->SendDebug("SwitchPC".$user,"PC ist nicht erreichbar.");
				$this->setValue("PC_State", 5);
			}
		}
		else {
			//Rechner runterfahren
			$this->SendDebug("SwitchPC".$user,"PC wird runtergefahren.");
			$this->shutdown();
			$respond = $this->waitHostDown(100, 2);
			if($respond){
				$this->setValue("PC_State", 1);
				$this->SetValue("PC_Switch", false);
			}

		}
	}

 

	#------------------------------------------------------------------------------
	# Function: SetPCStatus                                                      
	#...............................................................................
	# Beschreibung : Warteschleife bis Rechner erreichbar oder timeout                           
	#...............................................................................
	# Parameters:                                                                   
	#    $ip	- IP Adresse des Host
	#	 $timeout - max Zeit (Anzahl der Schleifen)                                                                     
	#...............................................................................
	# Returns :    bool true/false                                                                
	#------------------------------------------------------------------------------  */
	public Function SetPCStatus() {
		
		$PlugID = $this->ReadPropertyInteger("FSM"); 
		$StatID = @IPS_GetObjectIDByName("STATE", $PlugID);
		$plugState = GetValue($StatID);
		$user = $this->ReadPropertyString("user"); 
		$state = $this->GetValue("PC_State");
		$PID = $this->ReadPropertyInteger("I_ID");
		$I = Getvalue($PID);
		$Mode = $this->getvalue("Mode");
		$I_high = $this->ReadPropertyInteger("limitUp");
		$I_low = $this->ReadPropertyInteger("limitDown");
		$this->SendDebug("SetPCStatus ".$user,"Zykl. PC Statusabfrage.");
		//IPS_LogMessage("MyPC".$user, "- Stromänderung erkannt ".$I);
		//Steckdose ist aus dann ist auch Rechner aus
		if(!$plugState){
			$this->SendDebug("SetPCStatus".$user," Rechner ist aus und Steckdose ist aus.");
			$this->setValue("PC_State", 0); //Rechner und Power aus
			$this->SetValue("PC_Switch", false);
		}
		else{
			$this->SendDebug("SetPCStatus ".$user, "Strom: ".$I. "- Status: ".$state);
			if(($I>$I_high) & ($state != 4) & ($state != 1)) {
				//IPS_LogMessage("MyPC".$user, "- Stromänderung >600 erkannt- Rechner ist ON ");
				$this->SendDebug("SetPCStatus".$user,"Strom>".$I_high." und NOT Shutting down");
				$this->setValue("PC_State", 3); //Rechner ist hochgefahren
				$this->SetValue("PC_Switch", true);
				$this->SendDebug("SetPCStatus".$user,"setzte Rechner auf ON");
			}
			elseif(($I>$I_low) & ($state != 4) & ($state != 1) & ($state != 0)){
				
				$this->SendDebug("SetPCStatus".$user,"Strom>".$I_low." und Rechner fährt hoch");
				$this->setValue("PC_State", 2); //Rechner wird hochgefahren
			}
			//Strom is low and PC ist aus und im Auto mode
			if(($I<$I_low) & ($state == 1) & ($Mode)) {
				IPS_Sleep(1000);
				
				$this->SendDebug("SetPCStatus".$user,"Strom<".$I_low." erkannt- Rechner aus Power off.");
				//Steckdose ausschalten
				$SwitchOnArray = IPS_GetVariable($this->ReadPropertyInteger("FSMState"));
				$SwitchOnTime = $SwitchOnArray["VariableChanged"];
				//falls Steckdose > 3min = 180sec an ist dann erst ausschalten
				if(time()> ($SwitchOnTime) + 180){
					HM_WriteValueBoolean($PlugID, "STATE", false);
					$this->setValue("PC_State", 0); //Rechner und Power aus
					$this->SetValue("PC_Switch", false);
				}
			}
			elseif(($I<$I_low) & ($state == 4)){
				IPS_Sleep(3000);
				$this->SendDebug("SetPCStatus".$user,"Strom<".$I_low." und ist runtergefahren und ist aus.");
				$this->setValue("PC_State", 1); //Rechner ist aus und Power bleibt an
				$this->SetValue("PC_Switch", false);
			}


			//Strom is low and PC steht länger als x sec in starting 
			if(($I<$I_low) & ($state == 2)) {
				//waiting for 30 sec
				IPS_Sleep(30000);
				If($I<$I_low) {
					$this->SendDebug("SetPCStatus".$user,"Strom<".$I_low." Rechner bleibt im starting Modus stecken.");
					$this->setValue("PC_State", 1); //Rechner ist aus und Power ist noch an
				}
			}	

			//Strom ist high und Status immer noch OFF 
			if(($I>$I_high) & ($state == 1)) {
				//prüfen ob Rechner erreichbar
				$PCip = $this->ReadPropertyString("IP");
				$net = $this->checkHost($PCip);
				if ($net){
					//Rechner-Netzwerkkarte ist erreichbar
					$this->setValue("PC_State", 3); //Rechner und Power ist ein.
					$this->SetValue("PC_Switch", true);
				}
				else{
					
				}
				
			}

			if($I < $I_low){
				$this->SendDebug("SetPCStatus".$user,"Strom ist <".$I_low." -PC ist OFF.");
				$this->setValue("PC_State", 1); //Rechner ist aus.
			}
		}
		return $I;
	}



#________________________________________________________________________________________________________________________
# Section: Private Functions                                                                                              
# Die folgenden Funktionen sind nur zur internen Verwendung verfügbar                                                     
# Hilfsfunktionen                                                                                                         
# GS_XYFunktion($this->InstanceID, ... );                                                                                      
#________________________________________________________________________________________________________________________

	#------------------------------------------------------------------------------
	# Function: wol                                                      
	#...............................................................................
	# Beschreibung : WakeOnLan - Rechner starten über Netzwerk                                 
	#...............................................................................
	# Parameters:                                                                   
	#    $mac	- MAC Adresse des PC's                                                                       
	#...............................................................................
	# Returns :                                                                     
	#------------------------------------------------------------------------------  */
	protected Function wol($mac) {
		$hwaddr = pack('H*', preg_replace('/[^0-9a-fA-F]/', '', $mac));

		// Create Magic Packet
		$packet = sprintf(
			'%s%s',
			str_repeat(chr(255), 6),
			str_repeat($hwaddr, 20)
		);
	
		$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	
		if ($sock !== false) {
			$options = socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, true);
	
			if ($options !== false) {
				socket_sendto($sock, $packet, strlen($packet), 0, "255.255.255.255", 9);
				socket_close($sock);
			}
		}
		return true;
	}
	
	#------------------------------------------------------------------------------
	# Function: wol                                                      
	#...............................................................................
	# Beschreibung : Rechner per NET command runterfahren                            
	#...............................................................................
	# Parameters:                                                                   
	#    $mac	- MAC Adresse des PC's                                                                       
	#...............................................................................
	# Returns :                                                                     
	#------------------------------------------------------------------------------  */
	protected Function shutdown() {
		$user = $this->ReadPropertyString("user"); 
		$PCip = $this->ReadPropertyString("IP");
		IPS_LogMessage("MyPC".$user, "- Rechner. ".$PCip." wird heruntergefahren.");
		$this->setValue("PC_State", 4);
		$user = $this->ReadPropertyString("user");
		$passwort = $this->ReadPropertyString("password");
		`net rpc shutdown -I $PCip -U $user%$passwort`;
 
	}


	#------------------------------------------------------------------------------
	# Function: waitHostUp                                                      
	#...............................................................................
	# Beschreibung : Warteschleife bis Rechner erreichbar oder timeout                           
	#...............................................................................
	# Parameters:                                                                   
	#    $ip	- IP Adresse des Host
	#	 $timeout - max Zeit (Anzahl der Schleifen)                                                                     
	#...............................................................................
	# Returns :    bool true/false                                                                
	#------------------------------------------------------------------------------  */
	protected Function waitHostUp($counter, $delay) {
		$PCip = $this->ReadPropertyString("IP");
		$i = 0;
		IPS_Sleep($delay * 1000);
		while ($i <= $counter)
		{
			$state = $this->checkHost($PCip);
			if ($state){
				//Rechner-Netzwerkkarte ist erreichbar
				return true;
			}
			else{
				//500ms warten und erneut versuchen
				IPS_Sleep(1000);
				$i++  ;            // Wert wird um 1 erhöht
			}
		}
		IPS_LogMessage("MyPC", "Verbindungsversuche zu Rechner ".$PCip." sind erfolglos.");
		return false;
	}

	#------------------------------------------------------------------------------
	# Function: waitHostDown                                                      
	#...............................................................................
	# Beschreibung : Warteschleife bis Rechner nicht mehr erreichbar oder timeout                           
	#...............................................................................
	# Parameters:                                                                   
	#    $ip	- IP Adresse des Host
	#	 $timeout - max Zeit (Anzahl der Schleifen)                                                                     
	#...............................................................................
	# Returns :    bool true/false                                                                
	#------------------------------------------------------------------------------  */
	protected Function waitHostDown($counter, $delay) {
		$PCip = $this->ReadPropertyString("IP");
		$i = 0;
		while ($i <= $counter)
		{
			$state = $this->checkHost($PCip);
			if (!$state){
				//Rechner-Netzwerkkarte ist nicht erreichbar
				IPS_LogMessage("MyPC", "- Rechner ".$PCip." ist aus.");
				$this->SetValue("PC_State", 1);
				return true;
			}
			else{
				//500ms warten und erneut versuchen
				IPS_Sleep($delay *1000);
				$i++  ;            // Wert wird um 1 erhöht
			}
		}
		
		return true;
	}

}
