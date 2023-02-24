<?php
/* --------------------------------------------------------------------------- 
  TRAITS: DataHelper
...............................................................................
        
 
 
-------------------------------------------------------------------------------*/
trait csv {
    protected function csv_to_array($filename='/home/pi/pi-share/MyEnergy.csv', $delimiter=';') {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;
        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

	protected Function findCellValue($filename, $searchCol, $SearchValue, $CellCol) {
        $data = $this->csv_to_array($filename);
        $key = array_search($SearchValue, array_column($data, $searchCol));
        return $data[$key][$CellCol];
	}
}