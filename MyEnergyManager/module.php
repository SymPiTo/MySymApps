<?
class MyEnergyManager extends IPSModule {
/*
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
		$this->RegisterVariableInteger('kwh_jahr', 'Jahres Verbrauchswerte', '', '1');
		$this->RegisterVariableInteger('kwh_akt', 'aktueller Verbrauch', '', '2');
		$this->RegisterVariableFloat('akt_Kosten', 'aktuelle Kosten', '', '3');
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
	/* Function: GetPlantData                                                      
	...............................................................................
	 Beschreibung : Holt die Sensor Daten per API                                  
	...............................................................................
	 Parameters:                                                                   
	    none                                                                       
	...............................................................................
	 Returns :                                                                      
	------------------------------------------------------------------------------  */

	public Function XXXXXX() {

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
