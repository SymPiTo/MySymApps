<?
Class puffer{

    public function __construct($Speicher_ID)
    {
        $this->ID_Mem = $Speicher_ID;
    }
    //$VarArray = array("timerOn", "Todzeit", "RT_before", "RLFT_before");
    public function defineVars($VarArray){
        //prüfen ob IPS Variable existiert
        if(getvalue($this->ID_Mem)){
            foreach ($VarArray as $key => $val) {
                ${$key} = $val;
                $${$key} = "";
            }
            $result = compact($VarArray);
            /* 
            Übergabe mit:
                $timerOn = false;
                    $VarArray = array("timerOn", "Todzeit", "RT_before", "RLFT_before");
            Result:
                Array
                (
                    [timerOn] => false
                    [Todzeit] => false
                    [RT_before] => 0
                    [RLFT_before] => 0
                )
            */
            setvalue($this->ID_Mem, serialize($VarArray));   
            return $result;
        }
        else{
            return false;
        }
    }
    public function getMem($var){
        $result = unserialize(getvalue($this->ID_Mem));
        return $result[$var];
    }
    public function setMem($var, $value){
        $MemArray = unserialize(getvalue($this->ID_Mem));
        $MemArray[$var] = $value;
        setvalue($this->ID_Mem, serialize($MemArray));
        return $MemArray[$var];
    }
} 