<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../3rdparty/lib/suncalc.class.php';


class ssaTask extends eqLogic {

	public static function cron() 
    { 
        foreach (eqLogic::byType('ssaTask') as $eqLogic) {
    	
    		$autorefresh = $eqLogic->getConfiguration('autorefresh');
    		if ($eqLogic->getIsEnable() == 1 && $autorefresh != '') {
    			try {
    				$c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
    				if ($c->isDue()) {
    					try {
                                                $eqLogic->pilote();
                                                
    						
    					} catch (Exception $exc) {
    						log::add('ssaTask', 'error', $eqLogic->getHumanName().'['.__FUNCTION__.']' . ' : ' . $exc->getMessage());
    					}
    				}
    			} catch (Exception $exc) {
    				log::add('ssaTask', 'error', $eqLogic->getHumanName().'['.__FUNCTION__.']' .' : Error ' );
    			}
    		}
    	} 
     
    } 

    /*cron */   
    public static function pull() {
        foreach (eqLogic::byType('ssaTask', true) as $ssaTask) {

                log::add('ssaTask','debug',$ssaTask->getHumanName().'['.__FUNCTION__.'] ('.__LINE__.')' .  ' : Info daily');
                $ssaTask->calculate();
        }
    }

  



   
    private function calculate()
    {   $now=new DateTime();
        $suns = new ssaTask\SunCalc($now, config::byKey('latitude', 'ssaTask') , config::byKey('longitude', 'ssaTask'));
        $res=$suns->getSunTimes();
        // $log_etat=sprintf("sunset : [%s] ", json_encode($res));
        //log::add('ssaNight','debug',$this->getHumanName().'['.__FUNCTION__.'] ('.__LINE__.')' .  ' : '.$log_etat);
       
      

        $this->setSunTime("sunset",$res["sunset"]->format('H:i'));
        $this->setSunTime("sunrise",$res["sunrise"]->format('H:i'));
       

    }


    public function getState($_options)
    {   $_id= $_options['id'];
        $ssaEqlogicObj = ssaTask::byId($_id);
        
        $etat=$ssaEqlogicObj->getEtat();
        
        $log_etat=sprintf("etat %s",$etat);
        log::add('ssaTask','debug',__LINE__." ".$ssaEqlogicObj->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
        
        
        if( $ssaEqlogicObj->getEtat()==1)
            return true;
        else
            return false;

    }

    public function setOnOff($_options)
    {   $_id= $_options['id'];
        $_ordre = $_options['ordre'];
        $ssaEqlogicObj = ssaTask::byId($_id);
        
        if ($_ordre == $ssaEqlogicObj->getEtat())
        {   $log_etat=sprintf("etat déjà %s",$_ordre);
            log::add('ssaTask','debug',__LINE__." ".$ssaEqlogicObj->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
       

        }    
        else if ( $ssaEqlogicObj->getIsEnable() == 1 )
        {   $ssaCmdEtat= $ssaEqlogicObj->getCmd(null, 'etat');
            $ssaCmdEtat->setConfiguration('etat_id', $_ordre);
            $ssaCmdEtat->setCollectDate('');
            $ssaCmdEtat->event(($_ordre==1)?true:false);
            $ssaCmdEtat->save();

            $ssaCmd= $ssaEqlogicObj->getCmd(null, 'lastTask');
            $ssaCmd->setCollectDate('');
            $ssaCmd->event(($_ordre==1)?'On':'Off');
            $ssaCmd->save(); 

            $log_etat=sprintf("etat set  %s",$_ordre);
            log::add('ssaTask','debug',__LINE__." ".$ssaEqlogicObj->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
            $ssaEqlogicObj->refreshScreen();
        }

    }

	/*     * **********************Reactoringr*************************** */
    private function setSunTime($sun, $ltime)
    {   $tasks=$this->getConfiguration('task');
        $log_etat=sprintf("%s %s",$sun,$ltime);
        //log::add('ssaTask','debug',__LINE__." ".$this->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
        foreach ($tasks as $k => $task)
        {   $log_etat=sprintf("task  %s",$tasks[$k]["name"] );
            //log::add('ssaTask','debug',__LINE__." ".$this->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
            if ( $tasks[$k][$sun] )
            {  $log_etat=sprintf("set time");
              //  log::add('ssaTask','debug',__LINE__." ".$this->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
                $tasks[$k]["heure"]=$ltime;

            }

        }
        $this->setConfiguration($sun, $ltime);
        $this->setConfiguration('task',$tasks);
        $this->save();
    }


    public static function test($_options)
    {   
       
        $_id= $_options['id'];
        $ssaEqlogicObj = ssaTask::byId($_id);
        $ssaEqlogicObj->setConfiguration('sunset', '');
        $ssaEqlogicObj->setConfiguration('sunrise', '');
        
        $ssaEqlogicObj->save();


    }

    private function getEtat()
    {   $ssaEtatCmd=$this->getCmd(null, 'etat');
        $etat = $ssaEtatCmd->getConfiguration('etat_id') ;
        return $etat;
    }

    private function buttonOffActif()
    {  if ($this->getEtat()==0)
            return true;
       else
            return false;
    }

    private function buttonOnActif()
    {  if ($this->getEtat()==1)
            return true;
       else
            return false;
    }

    private function executeCmd($now,$name,$cmd)
    {	
        $listCommand=explode ("&&",$cmd);
                
        for ($i=0;$i < sizeof($listCommand); $i++)
        {   if (jeedom::isDateOk()) 
            {
                $ssaCmd= $this->getCmd(null, 'lastTask');
                $ssaCmd->setConfiguration('task',$name);
                $ssaCmd->setConfiguration('cmd',$listCommand[$i]);
                $ssaCmd->setCollectDate('');
                $ssaCmd->event($name,$now);
                $ssaCmd->save(); 
                
            }
        	$localCMD=cmd::byString($listCommand[$i])->execCmd();
        
        }



    }

	private function pilote()
    {   
        $log_etat=sprintf("debut");
        log::add('ssaTask','debug', __LINE__." ". $this->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
        
        if ($this->getEtat()==0)
        {   $log_etat=sprintf("Etat Off");
            log::add('ssaTask','debug',__LINE__." ". $this->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
        
        }    
        else if ($this->getIsEnable() == 1)
        {   $currentTasks=$this->getCurrentTask();
        	$nextTask=$this->getNextTask();
        	
            $date = strtotime("now");
            foreach ($currentTasks as $task)
            {   
                $date = strtotime("+2 second", $date);

            	$log_etat=sprintf("listOfTask : %s ]", json_encode($task));
       			log::add('ssaTask','debug',__LINE__." ". $this->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
                
       			$this->executeCmd( date('Y-m-d H:i:s', $date), $task["name"],$task["cmd"]);

            }
            foreach ($nextTask as $task)
            {
            	$log_etat=sprintf("NextTask : %s ]",  json_encode($task));
       			log::add('ssaTask','debug',__LINE__." ". $this->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
       			
       			$ssaCmd= $this->getCmd(null, 'nextTask');
                $ssaCmd->setConfiguration('task',$task["name"]);
                $ssaCmd->setConfiguration('cmd',$task["cmd"]);
                $ssaCmd->setCollectDate('');
                $ssaCmd->event($task["name"]);
                $ssaCmd->save(); 

            }
            $this->refreshScreen();

        }
    }


    private function isNotWorkable($date)
    {
 
        if ($date === null)
        {
            $date = time();
        }
 
        $date = strtotime(date('m/d/Y',$date));
        $year = date('Y',$date);
 
        $easterDate  = easter_date($year);
        $easterDay   = date('j', $easterDate);
        $easterMonth = date('n', $easterDate);
        $easterYear   = date('Y', $easterDate);
 
        $holidays = array(
        // Dates fixes
        mktime(0, 0, 0, 1,  1,  $year),  // 1er janvier
        mktime(0, 0, 0, 5,  1,  $year),  // Fête du travail
        mktime(0, 0, 0, 5,  8,  $year),  // Victoire des alliés
        mktime(0, 0, 0, 7,  14, $year),  // Fête nationale
        mktime(0, 0, 0, 8,  15, $year),  // Assomption
        mktime(0, 0, 0, 11, 1,  $year),  // Toussaint
        mktime(0, 0, 0, 11, 11, $year),  // Armistice
        mktime(0, 0, 0, 12, 25, $year),  // Noel
 
        // Dates variables
        mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear),
        mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear),
        mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear),
        );
 
      return in_array($date, $holidays);
    }

    private function getCurrentTask()
    {   $listOfNextTask = array();
    	
        
        if ( $this->getIsEnable() == 1)
        {	//typeOfDay

            $today= time();
            if ($this->isNotWorkable($today))
                $typeOfDay='f';
            else
            {   $day=array("d","l","ma","me","j","v","s");
                $typeOfDay=$day[strftime("%w")];
            }

            $time=date('Hi', time() );
            $now=DateTime::createFromFormat('Hi', $time);

           
            $tasks=$this->getConfiguration('task');
            foreach ($tasks as $task)
            {   $calendrier=$task["calendrier"];
            
                $debut = DateTime::createFromFormat('H:i', $task["heure"]);
                
                if ($now == $debut  &&  in_array($typeOfDay,$calendrier ))
                {   array_push($listOfNextTask, $task);
                    
                    
                }


            }


	    }
	    return $listOfNextTask;
    }


 	private function getNextTask()
    {   $nextTask = array();

    	
        
        if ( $this->getIsEnable() == 1)
        {	//typeOfDay

            $today= time();
            if ($this->isNotWorkable($today))
                $typeOfDay='f';
            else
            {   $day=array("d","l","ma","me","j","v","s");
                $typeOfDay=$day[strftime("%w")];
            }

            $time=date('Hi', time() );
            $now=DateTime::createFromFormat('Hi', $time);

           
            $tasks=$this->getConfiguration('task');


        
            foreach ($tasks as $task)
            {   
            	$calendrier=$task["calendrier"];
                $debut = DateTime::createFromFormat('H:i', $task["heure"]);
                $lastime = (isset($nextTask["heure"])) ? DateTime::createFromFormat('H:i', $nextTask["heure"]):$now;
                
                

                if ($now < $debut  && ($debut < $lastime  || $lastime==$now) && in_array($typeOfDay,$calendrier ))
                {   
                	$nextTask= $task;
                    
                }


            }


	    }
	    
	    return array($nextTask);
    }




	private  function createInfoCmd($name,$unit,$onlyEvent,$subType, $configuration,$default)
    {   //numeric   
        $ssaTaskCmd = $this->getCmd(null, $name);
		if (!is_object($ssaTaskCmd)) {
            $ssaTaskCmd = new ssaTaskCmd();
		}
		$ssaTaskCmd->setName(__($name, __FILE__));
		$ssaTaskCmd->setLogicalId($name);
		$ssaTaskCmd->setEqLogic_id($this->getId());
		$ssaTaskCmd->setUnite($unit);
        $ssaTaskCmd->setType('info');
        $ssaTaskCmd->setValue($default);
		$ssaTaskCmd->setEventOnly($onlyEvent);
		$ssaTaskCmd->setSubType($subType);
        $ssaTaskCmd->setIsHistorized(1);
        foreach($configuration as $cle=>$valeur)
        {
                $ssaTaskCmd->setConfiguration($cle,$valeur);
		}
        $ssaTaskCmd->save(); 
        return $ssaTaskCmd->getId();
        
    }

	private  function createActionCmd($name)
    {
    	$ssaTaskCmd = $this->getCmd(null, $name);
		if (!is_object($ssaTaskCmd)) {
            $ssaTaskCmd = new ssaTaskCmd();
		}
		$ssaTaskCmd->setName(__($name, __FILE__));
		$ssaTaskCmd->setLogicalId($name);
		$ssaTaskCmd->setEqLogic_id($this->getId());
	
        $ssaTaskCmd->setType("action");
        
	
		$ssaTaskCmd->setSubType("other");
       
		$ssaTaskCmd->save(); 
        return true;
        
    }


    public function postInsert() {
    	$this->createInfoCmd("etat", '', 1, "binary",array('etat_id'=>0),0);
    	$this->createInfoCmd("lastTask", '', 1, "string",array(),'');
    	$this->createInfoCmd("nextTask", '', 1, "string",array(),'');
    	$this->createActionCmd("on");
    	$this->createActionCmd("off");

         #e66b6b
        $value=array('date'=>true,'history'=>true,'color'=> '#e66b6b');
        $this->setConfiguration('affichage', $value);
        $this->setConfiguration('sunset', '19:00');
        $this->setConfiguration('sunrise', '6:00');
        
        $this->save();
    	//$this->createActionCmd("test");
    }


 	public function preSave() 
    {   log::add('ssaTask', 'debug',$this->getHumanName().'['.__FUNCTION__.']'.' L'.__LINE__.' '. 'presaving');
        
        if ($this->getConfiguration('autorefresh') == '') 
        {
                $this->setConfiguration('autorefresh', '* * * * *');
	    }

	}

	public function postSave() {
		log::add('ssaTask', 'debug',$this->getHumanName().'['.__FUNCTION__.']'.' L'.__LINE__.' '. 'saving');
        

		
	}



	public function postUpdate() {
		log::add('ssaTask', 'debug',$this->getHumanName().'['.__FUNCTION__.']'.' L'.__LINE__.' '. 'updating');

	}



	public function preUpdate() {
	   
	}





    public function refreshScreen()
    {   
        $_version = jeedom::versionAlias("dashboard"); 
        $mc = cache::byKey('ssaTaskWidget' . $_version . $this->getId());
        $mc->remove();
        $this->toHtml('dashboard');
        
        
        $_version = jeedom::versionAlias("mobile"); 
        $mc = cache::byKey('ssaTaskWidget' . $_version . $this->getId());
        $mc->remove();
        $this->toHtml('mobile');
        
    
        $this->refreshWidget();
    }
    
    private function displayZone($zone)
    {   $conf=$this->getConfiguration('affichage') ;
        $log_etat=sprintf("conf : %s ]",  json_encode($conf));
        log::add('ssaTask','debug', $this->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
        if ($conf[$zone]==1)
            return "";
        else 
            return "ssaTaskDisplay";
    }

    private function displayColor()
    {   $conf=$this->getConfiguration('affichage') ;
        return ($conf['color']);

    }

    

    public function toHtml($_version = 'dashboard')
    {   $mois= array('janvier','février','mars','avril','mai','juin','juillet','aout','septembre','octobre','novembre','decembre');
        if ($this->getIsEnable() != 1) {
            return '';
        }
        
        
        
        $_version = jeedom::versionAlias($_version);
        
        $log_etat=sprintf("entrer %s",$_version);
        log::add('ssaTask','debug',__LINE__." ". $this->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
       
       
        $mc = cache::byKey('ssaTaskWidget' . $_version . $this->getId());
       
        if ($mc->getValue() != '') {
            return $mc->getValue();
        }
        
        
        
        $ssaOffCmd= $this->getCmd(null, 'off');
        $ssaOnCmd= $this->getCmd(null, 'on');
        $ssaLastCmd= $this->getCmd(null, 'lastTask');
        $ssaNextCmd=$this->getCmd(null, 'nextTask');
        $lastTask = $ssaLastCmd->getConfiguration('task') ;
        $nextTask = $ssaNextCmd->getConfiguration('task') ;
        
        $replace = array(
            '#id#' => $this->getId(),
            '#uid#' => '_eq' . $this->getId() . eqLogic::UIDDELIMITER . mt_rand() . eqLogic::UIDDELIMITER,
            '#name#'=>$this->getName(),
            '#background_color#' => $this->getBackgroundColor($_version),
            '#eqLink#' => $this->getLinkToConfiguration(),
            '#collectDate#' => $ssaLastCmd->getCollectDate(),
            '#history#' => $ssaLastCmd->getId(),
            '#lastCMD#' => $lastTask,
            '#day#' => date("j"),
            '#month#' => $mois[date("n")-1],
            '#cmd_on#' => $ssaOnCmd->getId(),
            '#cmd_off#' => $ssaOffCmd->getId(),
            '#Off_actif#'=> ($this->buttonOffActif()==true)?'ssaTaskActive':'',
            '#On_actif#'=> ($this->buttonOnActif()==true)?'ssaTaskActive':'',
            '#DISPLAY_DATE#'=> $this->displayZone("date"),
            '#DISPLAY_HISTO#'=> $this->displayZone("history"),
            '#nextCMD#' => empty($nextTask)==1?'----':$nextTask,
            '#DISPLAY_COLOR#'=>$this->displayColor()

                       
        );
       
        $log_etat=sprintf("color [%s]",$this->displayColor());
        log::add('ssaTask','debug',__LINE__." ". $this->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
       
       
        $html = template_replace($replace, getTemplate('core', $_version, 'simple', 'ssaTask'));
        cache::set('ssaTaskWidget' . $_version . $this->getId(), $html, 60);
        return $html;
    }
    


}


class ssaTaskCmd extends cmd {
	public function dontRemoveCmd() {
      return true;
    }



    public function execute($_options = array()) {

    	$eqLogic = $this->getEqLogic();
        log::add('ssaTask','debug',  $eqLogic->getHumanName().'['.__FUNCTION__.']' . ' : appel '.$this->getLogicalId());

        if ($this->getLogicalId() == 'test') 
        {  $eqLogic->test(array('id' => intval($eqLogic->getId()) ));

        }

        if ($this->getLogicalId() == 'on') 
        {  $eqLogic->setOnOff(array('id' => intval($eqLogic->getId()), 'ordre' => 1 ));

        }

        if ($this->getLogicalId() == 'off') 
        {  $eqLogic->setOnOff(array('id' => intval($eqLogic->getId()), 'ordre' => 0 ));

        }
        
        if ($this->getLogicalId() == 'etat') 
        {  $eqLogic->getState(array('id' => intval($eqLogic->getId()) ));

        }
        
		return $this->getLogicalId();
    }
}

/**********************/


?>