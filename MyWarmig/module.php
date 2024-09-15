<?php
 #******************************************************************************#
 # Title : MyWarning                                                            #
 #                                                                              #
 # Author: PiTo                                                                 #
 #                                                                              #
 # GITHUB: <https://github.com/SymPiTo/MySymDevices/tree/master/MyComfee>       #
 #                                                                              #
 # Version: 1.0.0  20240907                                                     #
 #******************************************************************************#
 # _____________________________________________________________________________#
 #    Section: Beschreibung                                                     #
 #    Das Modul dient zur Erzeugung einer Benachrictigung                       #
 #    für mehrere auslösende Variablen                                          #
 # _____________________________________________________________________________#
 
 require_once(__DIR__ . "/../libs/MyHelper.php");

class MyWarning extends IPSModule{
	#Traits aufrufen
	use ProfileHelper;
	use DebugHelper;

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

        #Properties registrieren
		$this->RegisterPropertyString("StateMsg", "leer");
        $this->RegisterPropertyString("Sensors", "[]");
        $this->RegisterPropertyBoolean("ModActive", false);
        $this->RegisterPropertyBoolean("Mobile", false);
        $this->RegisterPropertyInteger("MobileID", 0);
        $this->RegisterPropertyBoolean("Telegram", false);
        $this->RegisterPropertyInteger("TelegramModulID", 0);
        $this->RegisterPropertyInteger("SenderID", 0);
		$this->RegisterPropertyInteger("VisID", 0);
		$this->RegisterPropertyBoolean("Tablet", true);
		$this->RegisterPropertyInteger("TabletID", 0);

		$this->RegisterAttributeString("SensorList", ""); 	
		$this->WriteAttributeString("SensorList", $this->ReadPropertyString("Sensors"));
		$this->RegisterAttributeString("TriggerList", ""); 

		$this->RegisterVariableString("Bat","Sensor","");
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

		

		#Registriere Neue bzw. deregistriere Variablen
		
		$OldSensorList = $this->MessageList();
		
		$NewSensorList = json_decode($this->ReadPropertyString("Sensors"), true);
		$NewSensorList = array_column($NewSensorList, 'ID');

		$newSensors = array_diff($NewSensorList, $OldSensorList);
		if(!empty($newSensors)){
			foreach ($newSensors as $value) {
				$this->RegisterMessage($value, VM_UPDATE);
				$this->SendDebug("Added", $value, 0);
			}
		}
	
		$delSensors = array_diff($OldSensorList, $NewSensorList);
		if(!empty($delSensors)){
			foreach ($delSensors as $value) {
				$this->UnRegisterMessage($value, VM_UPDATE);
				$this->SendDebug("Deleted", $value, 0);
			}
		}
		
        $this->WriteAttributeString("SensorList", $this->ReadPropertyString("Sensors"));
		

		$this->SendDebug("aktMessages: ", $this->MessageList(), 0);
		$this->SendDebug("Added", "-------------", 0);

        /*check if Modul Telegram Messenger -  installiert ist.
        if (IPS_ModuleExists("{eaf404e1-7a2a-40a5-bb4a-e34ca5ac72e5}")){
             
        }
        else{
            $this->SetStatus(201);
        }
        */
	
    }

	#------------------------------------------------------------------#
	#       Function: Destroy()                                        #
	#       Destroy() IPS Standard Funktion                            #
	#                                                                  #
	#------------------------------------------------------------------#
	
	public function Destroy(){

		//Never delete this line!
		parent::Destroy();

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
			break;
        Case VM_UPDATE:
			$this->SendDebug("VM_UPDATE","angesprochen:".$SenderID,0);
			$MA = $this->ReadPropertyBoolean("ModActive");
			$T = $this->ReadPropertyBoolean("Tablet");
			$varNam = IPS_GetObject($SenderID);
			$StateMsg = $this->ReadPropertyString("StateMsg"); 
			$VisID = $this->ReadPropertyInteger("VisID");
			$triggerValue = GetValue($SenderID);
			
			$TriggerArray = json_decode($this->ReadAttributeString("TriggerList"), true);
			#VISU_PostNotificationEx ($this->ReadPropertyInteger("VisID"), 'Warnung', $varNam['ObjectInfo'].$StateMsg, 'Alert', 'alarm' , $this->ReadPropertyInteger("TabletID")) ;
			if ($triggerValue == TRUE){
				//$this->SendDebug("VM_UPDATE","Trigger Value TRUE",0);
				# prüfe ob triggered Sensor in Liste steht.
				
	 
				if ($MA && T && !$TriggerArray[$SenderID]){
					$SensorName = $varNam['ObjectInfo'];
					//$this->SendDebug("SensorName:", $SensorName,0);
					VISU_PostNotification ($VisID, $StateMsg, $SensorName, 'Info', 0) ;
					#VISU_PostNotificationEx($VisID, $varNam['ObjectInfo'], $StateMsg, 'Info', 6);
					$TriggerArray[$SenderID] = true; 
				} else {
				
	
				}
			} else {

				
			}
			$this->WriteAttributeString("TriggerList",json_decode($TriggerArray));
			break;
		}
	} 


	public function MessageList() {
		$msg = $this->GetMessageList();
		$keysArray = [];
		foreach ($msg as $key => $subArray) {
			$keysArray[] = $key;		
		}
		return $keysArray;

	}
	
}