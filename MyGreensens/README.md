# Greensens
_Das Modul liest die Daten der Greensens Pflanzen Sensoren aus. 

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Anforderungen](#2-anforderungen)
3. [Installation & Konfiguration](#3-installation--konfiguration)
4. [Variablen](#4-variablen)
5. [Hintergrund Skripte](#5-hintergrund-skripte)
6. [Funktionen](#6-funktionen)
6. [FAQ](#7-faq)

### 1. Funktionsumfang
Dieses Modul liest die Sensordaten aus einer API Schnittstelle von Greensens  
Daten von bis zu 6 Sensoren können ausgelesen werden:
Folgende Sensordaten werden von der API Schnittstelle ausgelesen:
            ['sensorID']
            ['plantNameDE']
            ['status']
            ['temperature']
            ['illumination']
            ['moisture']
            ['state']
            ['link']
 

### 2. Anforderungen

 - IP-Symcon ab Version 5.o
 - Greensens HUB und Sensoren (http://www.greensens.de/)
 - Greensens APP auf Smartphone installieren
 - Konto erstellen (Email & Passwort)
 - Sensoren wie im Handbuch beschrieben einrichten.


### 3. Installation & Konfiguration

### Installation
Über das Modul-Control folgende URL hinzufügen.  
"git://github.com/tkugelberg/SymconSonos.git"  .  

 

### Konfiguration
 
-  Login (Email von Registrierten Greensens - Konto)
-  Passwort (PW von Registrierten Greensens - Konto)
-  Anzahl konfigurierter Sensoren.
-  Intervall Zeit: (Update Zyklus) 



## 4. Variablen
Je Sensor:
Name                    | Typ       | Beschreibung
----------------------- | --------- | ----------------
SensorX:Sensor ID       | Integer   | Nummer (ID) des  Sensors
SensorX:Pflanzen Name   | String    | Name der Pflanze 
SensorX:Sensor Status   | Boolean   | Status des Sensors
SensorX:Temperatur      | Float     | gemessener Temperaturwert
SensorX:Helligkeit      | Float     | gemessene Helligkeit
SensorX:Feuchte         | Float     | gemessene Feuchte
SensorX:Zustand         | Integer   | Zustand der Pflanze  (0-3)
SensorX:ImageUrl        | String    | Link zum Bild der Pflanze


## 6. Funktionen

GS_GetPlantDate(integer $InstanceID)
```
Holt die Sensordaten von der API Schnittstelle.
https://api.greensens.de/

Die Funktion gibt alle Sensordaten als Array zurück. 

### 7. FAQ
#### 7.1. Wie viele Variablen werden für das Modul benötigt?  
8 je Sensor
