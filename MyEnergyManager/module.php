<?
/***************************************************************************
 * Title: MyGreensens
 *
 * Author: PiTo
 * 
 * GITHUB: <https://github.com/SymPiTo/MySymApps/tree/master/MySymApps>
 * 
 * Version: 1.0
 *************************************************************************** */

require_once __DIR__ . '/../libs/MyHelper.php';

class MyEnergyManager extends IPSModule {

	use DebugHelper;

	/* TEST
______________________________________________________________________________________________________________________________________________
           Section: Internal Module Functions                                                                                                 
           Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung                                                             
______________________________________________________________________________________________________________________________________________
*/
	/*
	--------------------------------------------------------------------------------------------------------------------------------------
	       Function: Create()                                                                                                             
	       Create() wird ausgeführt, beim Anlegen der Instanz                                                                             
	--------------------------------------------------------------------------------------------------------------------------------------
	*/
	public function Create() {
		parent::Create();
		$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyString("StromSensorList", "[]");
		$this->RegisterPropertyFloat("StromPreis", 0.00);
		$this->RegisterPropertyBoolean("AddGrundPreis", false);
		
		$this->RegisterPropertyFloat("GrundPreis", 0.00);

		$this->RegisterPropertyInteger('Update_Interval', 30);

		$this->RegisterVariableInteger('kwh_jahr', 'Vor-Jahres Verbrauchswerte', 'Energy', '1');
		$this->RegisterVariableInteger('kwh_akt', 'aktueller Verbrauch', 'Energy', '2');
		$this->RegisterVariableFloat('akt_Kosten', 'aktuelle Kosten', '~Euro', '5');
		$this->RegisterVariableFloat('VJ_Kosten', 'Vorjahres Kosten', '~Euro', '4');
		$this->RegisterVariableFloat('VJ_GesKosten', 'Vorjahres GesamtKosten', '~Euro', '3');
		$this->RegisterTimer('UpdateTrigger', 0, "MEM_update(\$_IPS['TARGET']);");
	}
	
	/*
	--------------------------------------------------------------------------------------------------------------------------------------
	       Function: ApplyChanges()                                                                                                       
	       ApplyChanges() wird ausgeführt, beim Anlegen der Instanz                                                                       
	       und beim ändern der Parameter in der Form                                                                                      
	--------------------------------------------------------------------------------------------------------------------------------------
	*/
	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();

		//für jeden Sensor eine Variable anlegen
		$EnergySensors = json_decode($this->ReadPropertyString("StromSensorList"));
		 $this->SendDebug("SensorList", $EnergySensors, 0);
		foreach ($EnergySensors as $key => $sensor) {
			$SIdent = $sensor->ID;
			
			$exist = @IPS_GetLinkIDByName("Sensor_".$SIdent, $this->InstanceID);
			if ($exist === false){
				// Anlegen eines neuen Links mit dem Namen "ObjectID"
				$LinkID = IPS_CreateLink();             // Link anlegen
				IPS_SetName($LinkID, "Sensor_".$SIdent); // Link benennen
				IPS_SetParent($LinkID, $this->InstanceID); // Link einsortieren unter dem Objekt mit der ID "12345"
				IPS_SetLinkTargetID($LinkID, $SIdent);    // Link verknüpfen
			}
			else{
    			$this->SendDebug("Die Link-ID lautet: ", $SIdent);
			}
		}
 
		if($this->ReadPropertyBoolean("Open")){
			$this->SetTimerInterval('UpdateTrigger', 1000  * $this->ReadPropertyInteger('Update_Interval'));
		}
		else{
			$this->SetTimerInterval('UpdateTrigger', 0);
		}
	}
	
/* 
________________________________________________________________________________________________________________________
Section: Public Functions                                                                                               
Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die 'Module Control' eingefügt wurden.   
Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt.      
GS_XYFunktion($Instance_id, ... );                                                                                      
________________________________________________________________________________________________________________________
*/	
	//-----------------------------------------------------------------------------
	/* Function: calcActEnergy                                                      
	...............................................................................
	 Beschreibung : Holt die Sensor Daten per API                                  
	...............................................................................
	 Parameters:                                                                   
	    none                                                                       
	...............................................................................
	 Returns :                                                                      
	------------------------------------------------------------------------------  */

	private Function calcActEnergy() {
		$actPower = 0;
		$EnergySensors = json_decode($this->ReadPropertyString("StromSensorList"));
		foreach ($EnergySensors as $key => $sensor) {
			$SIdent = $sensor->ID;
			$value = GetValue($SIdent);
			$actPower += $value;
			
		}
		$this->SetValue("kwh_akt", $actPower/1000);
	}

	//-----------------------------------------------------------------------------
	/* Function: calcCost                                                      
	...............................................................................
	 Beschreibung : Holt die Sensor Daten per API                                  
	...............................................................................
	 Parameters:                                                                   
	    none                                                                       
	...............................................................................
	 Returns :                                                                      
	------------------------------------------------------------------------------  */

	private Function calcCost() {
		$actPower = $this->getvalue("kwh_akt");
		$cost = ($actPower *1000 * $this->ReadPropertyFloat("StromPreis"))/100 ;
		if ($this->ReadPropertyBoolean("AddGrundPreis")){
			$cost = $cost + $this->ReadPropertyFloat("GrundPreis");
		}

		$this->SetValue("akt_Kosten", $cost);
	}

	//-----------------------------------------------------------------------------
	/* Function: update                                                      
	...............................................................................
	 Beschreibung : Holt die Sensor Daten per API                                  
	...............................................................................
	 Parameters:                                                                   
	    none                                                                       
	...............................................................................
	 Returns :                                                                      
	------------------------------------------------------------------------------  */

	public Function update() {
		$this->calcActEnergy();
		$this->calcCost();
	}


/* 
________________________________________________________________________________________________________________________
Section: Private Functions                                                                                              
Die folgenden Funktionen sind nur zur internen Verwendung verfügbar                                                     
Hilfsfunktionen                                                                                                         
GS_XYFunktion($Instance_id, ... );                                                                                      
________________________________________________________________________________________________________________________
*/	
	//-----------------------------------------------------------------------------
	/* Function: GetPlantData                                                      
	...............................................................................
	 Beschreibung : Holt die Sensor Daten per API                                  
	...............................................................................
	 Parameters:                                                                   
	    none                                                                       
	...............................................................................
	 Returns :                                                                     
	------------------------------------------------------------------------------  */

	protected Function test() {

	}

}
