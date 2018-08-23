<?php
/**
 * Stoper - measure the time that a script takes to execute
 *
 * Warning:
 *          there is no full backward compatibility with
 *          first version (1.0) of this class - old method
 *          MidTimeStart() was renamed to MidTimeRestart()
 * Changes:
 *          2.0
 *          added Advanced Mode:
 *          - results are stored in array
 *          - you can view results in browser or / and save them to file
 *
 * PHP version: 4.x
 * @name Stoper
 * @version 2.0
 * @author Marek Szewczyk <marek@crayon.pl>
 * @copyright Copyright &copy; 2005 by Marek Szewczyk <www.crayon.pl>
 */
namespace RestBundle\Model;

class Stoper {

    var $fltStartTime;

    var $fltMidTime;

    var $fltStopTime;

    var $arrResults;

    /**
     * computational precision
     * @var int
     */
    var $intComputPrecision;

    /**
     * output precision
     * @var int
     */
    var $intOutputPrecision;

    /**
     * Class constructor
     *
     * @param $intComputPrecision int
     * @param $intOutputPrecision int
     * @return void
     */
    function Stoper($intComputPrecision = 6, $intOutputPrecision = 4){
        $this->fltStartTime = 0.0;
        $this->fltMidTime   = 0.0;
        $this->fltStopTime  = 0.0;
        $this->arrResults   = array();
        settype($intComputPrecision, 'int');
        settype($intOutputPrecision, 'int');
        if ($intComputPrecision < 0) {
            $intComputPrecision = 6;
        }
        if ($intOutputPrecision < 0) {
            $intOutputPrecision = 4;
        }
        $this->intComputPrecision = $intComputPrecision;
        $this->intOutputPrecision = $intOutputPrecision;
    }

    /**
     * @param $intValue int
     * @return void
     */
    function setComputPrecision($intValue){
        if ($intValue < 0) {
            $intValue = 6;
        }
        $this->intComputPrecision = $intValue;
    }

    /**
     * @param $intValue int
     * @return void
     */
    function setOutputPrecision($intValue){
        if ($intValue < 0) {
            $intValue = 4;
        }
        $this->intOutputPrecision = $intValue;
    }

    /**
     * Returns current UNIX timestamp with microseconds,
     *
     * @return float
     */
    function getMicroTime(){
        list($usec, $sec) = explode(' ',microtime());
        return round((float) $usec + (float) $sec, $this->intComputPrecision);
    }



    /*
    * Simple mode methods:
    * $this->Start();
    * $this->MidTimeStart();
    * $this->Stop();
    * $this->Stop();
    * $this->showResult();
    */
    function Start(){
        $this->fltStartTime = $this->getMicroTime();
        $this->fltMidTime   = $this->fltStartTime;
        $this->fltStopTime  = 0.0;
    }

    function MidTimeRestart(){
        $this->fltMidTime = $this->getMicroTime();
    }

    function Stop(){
        $this->fltStopTime = $this->getMicroTime();
    }

    function getMidTime(){
        $time = $this->fltMidTime;
        $this->fltMidTime = $this->getMicroTime();
        return $time;
    }

    function showResult($strDescription = ''){
        if ($this->fltStopTime > 0.0) {
            return $strDescription.round($this->fltStopTime - $this->fltStartTime, $this->intOutputPrecision).' sec';
        } else {
            return $strDescription.round($this->getMicroTime() - $this->getMidTime(), $this->intOutputPrecision).' sec';
        }
    }



    /*
    * Advanced mode methods
    */
    function AdvStart(){
        $this->addAdvResult('start');
    }
    function AdvCheckPoint($strName){
        $this->addAdvResult($strName);
    }
    function AdvStop(){
        $this->addAdvResult('end');
    }
    function addAdvResult($strName){
        array_push($this->arrResults, array('name' => $strName, 'timestamp' => $this->getMicroTime()));
    }
    function showAdvResults(){
        $out = '';
        $startTime = $this->arrResults[0]['timestamp'];
        $resNumber = sizeof($this->arrResults);
        $out .= '<table style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px;">';
        $out .= '<tr><td colspan="2"><b>test started at</b> '.date('H:i:s', $this->arrResults[0]['timestamp']).'</td></tr>';
        for ($i = 0; $i < $resNumber; $i++) {
            if ($this->arrResults[$i]['name'] != 'start' && $this->arrResults[$i]['name'] != 'end') {
                if ($i == 0) {
                    $prevTime = $this->arrResults[$i]['timestamp'];
                } else {
                    $prevTime = $this->arrResults[$i - 1]['timestamp'];
                }
                $resultTime = round($this->arrResults[$i]['timestamp'] - $prevTime, $this->intOutputPrecision);
                $out .= '<tr><td align="right">'.$this->arrResults[$i]['name'].'</td><td>'.$resultTime.' sec</td></tr>';
            }
        }
        $out .= '<tr><td><b>comlete test time</b></td><td><b>'.round($this->arrResults[$resNumber - 1]['timestamp'] - $startTime, $this->intOutputPrecision).' sec</b></td></tr>';
        $out .= '<tr><td colspan="2"><b>test comleted at</b> '.date('H:i:s', $this->arrResults[$resNumber - 1]['timestamp']).'</td></tr>';
        $out .= '</table>';
        return $out;
    }
    function saveAdvResults($fileName = ''){
        $out = '';
        $startTime = $this->arrResults[0]['timestamp'];
        $resNumber = sizeof($this->arrResults);
        $out .= 'test started at '.date('H:i:s', $this->arrResults[0]['timestamp'])."\n";
        $out .= "--------------------------------------------\n";
        for ($i = 0; $i < $resNumber; $i++) {
            if ($this->arrResults[$i]['name'] != 'start' && $this->arrResults[$i]['name'] != 'end') {
                if ($i == 0) {
                    $prevTime = $this->arrResults[$i]['timestamp'];
                } else {
                    $prevTime = $this->arrResults[$i - 1]['timestamp'];
                }
                $resultTime = round($this->arrResults[$i]['timestamp'] - $prevTime, $this->intOutputPrecision);
                $out .= $resultTime."\t".$this->arrResults[$i]['name']."\n";
            }
        }
        $out .= "--------------------------------------------\n";
        $out .= 'comlete test time: '.round($this->arrResults[$resNumber - 1]['timestamp'] - $startTime, $this->intOutputPrecision)."\n";
        $out .= 'test comleted at '.date('H:i:s', $this->arrResults[$resNumber - 1]['timestamp']);
        if ($fileName == '') {
            $fileName = 'StoperRaport_'.date('Ymdhis').'.txt';
        }
        $fileHandle = fopen($fileName, 'a');
        fwrite($fileHandle, $out);
        fclose($fileHandle);
        return $out;
    }
}
?>