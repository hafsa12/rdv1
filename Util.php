<?php
/**
 * Contient des fonctions non dépendante du projet tel que la convertion des dates (iso -> fr) etc ...
 *
 * @author atexo
 * @copyright Atexo 2013
 * @version 1.0
 * @since atexo.rdv
 * @package
 * @subpackage
 */

class Atexo_Utils_Util extends Api_Util
{
	function getJoursParCle($dateDebIso, $dateFinIso, $keyJour) {
		$jours = array();
		$datetemp = explode("-", $dateDebIso);
		$cle = self::getCleDate($dateDebIso);
		$dateDebIso = date("Y-m-d", mktime(0, 0, 0, $datetemp[1], $datetemp[2] + $keyJour-$cle, $datetemp[0]));
		$jours[] = $dateDebIso;
		$datetemp = explode("-", $dateDebIso);
		while($dateDebIso<$dateFinIso) {
			$dateDebIso = date("Y-m-d", mktime(0, 0, 0, $datetemp[1], $datetemp[2]+7, $datetemp[0]));
			$datetemp = explode("-", $dateDebIso);
			$jours[] = $dateDebIso;
		}

		return $jours;
	}

	function getJoursEntreDate($dateDebIso, $dateFinIso) {
		$jours = array();
		$datetemp = explode("-", $dateDebIso);
		$dateDebIso = date("Y-m-d", mktime(0, 0, 0, $datetemp[1], $datetemp[2]+1, (int)$datetemp[0]));
		while($dateDebIso<$dateFinIso) {
			$jours[$dateDebIso] = $dateDebIso;
			$dateDebIso = date("Y-m-d", mktime(0, 0, 0, $datetemp[1], $datetemp[2]+1, (int)$datetemp[0]));
			$datetemp = explode("-", $dateDebIso);
		}

		return $jours;
	}

	function getCleDate($dateIso) {

		$datetemp = explode("-", $dateIso);

		$cle = date("w", mktime(0, 0, 0, $datetemp[1], $datetemp[2], $datetemp[0]));

		return $cle;
	}

	function getJoursEcoules($mois, $dateIso) {
		$jours = array();
		$datetemp = explode("-", $dateIso);

		if($mois!=$datetemp[1]) {
			return array();
		}

		for($i=1;$i<$datetemp[2];$i++) {
			$jours[] = str_pad($i, 2, "0", STR_PAD_LEFT);
		}

		return $jours;
	}

	function getHeuresEcoules($dateIso, $intervalHeure) {

		$dateTime = date("Y-m-d H:i", mktime(date("H")+$intervalHeure, date("i"), 0, date("m"), date("d"), date("Y")));
		list($date,$time) = explode(" ",$dateTime);

		if($date>$dateIso) {
			return "23:59";
		}

		if($date==$dateIso) {
			return $time;
		}

		return "00:00";
	}

	function getNbJourMois($mois, $annee) {

		$nbJour = date( "t", mktime(0, 0, 0, $mois, 1, $annee) );
		return $nbJour;
	}

	function addMinutes($heureMinute, $minute) {
		$datetemp = explode(":", $heureMinute);
		$heureMinuteResult = date("H:i", mktime($datetemp[0], $datetemp[1]+$minute, 0, date("d"), date("m"), date("Y")));
		return $heureMinuteResult;
	}

	function addJours($date, $jour) {
		$datetemp = explode("-", $date);
		$dateResult = date("Y-m-d", mktime(0, 0, 0, $datetemp[1], $datetemp[2]+$jour, $datetemp[0]));
		return $dateResult;
	}
	
	function addMois($date, $mois) {
		$datetemp = explode("-", $date);
		$dateResult = date("Y-m-d", mktime(0, 0, 0, $datetemp[1]+$mois, $datetemp[2], $datetemp[0]));
		return $dateResult;
	}

	function getNbJourDebutMois($dateIso) {

		$cle = self::getCleDate($dateIso);

		return ($cle==0) ? 6 : $cle-1;
	}

	function getNbJourFinMois($dateIso) {

		$cle = self::getCleDate($dateIso);

		return ($cle==0) ? 0 : 7-$cle;
	}

	function getMoisWithInterval($mois, $annee, $interval) {

		$moisAnnee = date( "m-Y", mktime(0, 0, 0, $mois+$interval, 1, $annee) );
		return explode("-",$moisAnnee);
	}

	public function getNbHeureBetweenHeures($listHeures, $deb, $fin) {
		$nbHeure=0;
		if(is_array($listHeures)) {
			foreach($listHeures as $heure) {
				if($heure>=$deb && $heure<=$fin) {
					$nbHeure++;
				}
			}
		}
		return $nbHeure;
	}

	public function getDatesSemaine($dateFrn) {
		$dateIso = self::frnDate2iso($dateFrn);
		$cle=self::getCleDate($dateIso);
		$dateDeb = self::addJours($dateIso, 1-$cle);
		$dateFin = self::addJours($dateIso, 7-$cle);
		return array("deb"=>self::iso2frnDate($dateDeb),"fin"=>self::iso2frnDate($dateFin));
	}

	public function getDatesMoisSemaine($dateDebFrn) {
		$dateIso = self::frnDate2iso($dateDebFrn);
		$dateDeb = self::addJours($dateIso, -self::getNbJourDebutMois($dateIso));
		$dateFin = self::getFinMois($dateIso);
		$dateFin = self::addJours($dateFin, self::getNbJourFinMois($dateFin));
		return array("deb"=>self::iso2frnDate($dateDeb),"fin"=>self::iso2frnDate($dateFin));
	}

	public function getFinMois($date) {
		$datetemp = explode("-", $date);
		return strftime("%Y-%m-%d",mktime(0,0,0,$datetemp[1]+1,0,$datetemp[0]));
	}
	
	public function iso2Utc($date) {
		$datetemp = explode("-", $date);
		return $datetemp[0].",".($datetemp[1]-1).",".$datetemp[2];
	}
	
	public function nbJours($debut, $fin) {
        $nbSecondes= 60*60*24;
 
        $debut_ts = strtotime($debut);
        $fin_ts = strtotime($fin);
        $diff = $fin_ts - $debut_ts;
        return round($diff / $nbSecondes);
    }
    
    public function getNMoisNJour($nbJours) {
    	return array(floor($nbJours/30),$nbJours%30);
    }
    
	public function formatTel($tel) {
    	return str_replace("+","0",str_replace(array(" ","-","."),"",$tel));
    }
    
    public function getNomDate($dateIso) {
    	//$dates = explode("-",$dateIso);
		$cle = self::getCleDate($dateIso);
		$cle = ($cle==0) ? 7 : $cle;
		$nomJour = Prado::localize("DAY".$cle);
		//$nomMois = Prado::localize("MONTH".((int)$dates[1]));
		return $nomJour." ".self::iso2frnDate($dateIso);
    }

	public function isDate($date, $format = 'Y-m-d H:i:s')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}

    public static function getLibelleEnumOuiNon($enum)
    {
        if($enum == Atexo_Config::getParameter("ENUM_OUI")) {
            return Prado::localize('OUI');
        } elseif ($enum == Atexo_Config::getParameter("ENUM_NON")) {
            return Prado::localize('NON');
        }
        return $enum;
    }

    public function toUtf8($text)
    {
        //echo "#".$text." ".mb_detect_encoding($text)."#";
        if (self::isUTF8($text)) {
            return $text;
        }
        else {
            return utf8_encode($text);
        }

    }

    public static function getCurrentUrl() {
		return "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	}

	public static function getUrlFromIndex() {
		$url = self::getCurrentUrl();
		return substr($url,0,strpos($url,"?"));
	}
}