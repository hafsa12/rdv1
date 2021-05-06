<?php
/**
 * description de la classe
 *
 * @author atexo
 * @copyright Atexo 2013
 * @version 0.0
 * @since Atexo.Rdv
 * @package atexo
 * @subpackage atexo
 */
class Atexo_RendezVous_Gestion {
	
	private $prestation=null;
	private $ressources=null;
	private $idRessources=null;

	public function getJourIndispoRdvMois($idOrganisme, $idPrestation, $site=false, $idRessource=null) {

		$jourIndispo = array();

		$tPrestationQuery = new TPrestationQuery();
		$prestation = $tPrestationQuery->getPrestationById($idPrestation);

		if(!isset($prestation)) {
			return $jourIndispo;
		}

		$tJourFerieQuery = new TJourFerieQuery();
		$jourFerie = $tJourFerieQuery->getJourFerie($idOrganisme);

		$jourIndispoRessource = $prestation->getJourIndisponibiliteRessources($idRessource);

		$jourPris = self::getJourPris($idPrestation, $site, $idRessource);

		$jourIndispo = array_unique(array_merge($jourFerie, $jourIndispoRessource, $jourPris));

		if(!in_array(date("Y-m-d"),$jourIndispo)) {
				
			$heures = self::getHeureDisponibleJour($idPrestation, date("Y-m-d"), $site, $idRessource);
				
			if(count($heures)==0) {
				$jourIndispo[]=date("Y-m-d");
			}
		}

		return $jourIndispo;
	}

	public function getJourPris($idPrestation, $site=false, $idRessource=null, $dateRdv=null ) {
		$jourIndispo = array();
		$tRendezVousPeer = new TRendezVousPeer();
		if($idRessource==null) {
			if($this->idRessources==null) {
				$tAgendaPeer = new TAgendaPeer();
				$idRessourceForRdv = $this->idRessources = $tAgendaPeer->getIdRessourcesByPrestation($idPrestation);
			}
			else {
				$idRessourceForRdv = $this->idRessources;
			}
		}
		else {
			$idRessourceForRdv = $idRessource;
		}
		$jourRdvs = $tRendezVousPeer->getJoursRdv(/*$site,*/ $idRessourceForRdv, $dateRdv);
		$jourRdvsPrestation = $tRendezVousPeer->getJoursRdv(/*$site,*/ $idRessourceForRdv, $dateRdv, $idPrestation);
		foreach($jourRdvs as $dateRdv=>$rdvs) {
			$tPeriodeQuery = new TPeriodeQuery();
			if($tPeriodeQuery->isJourPris($idPrestation, $dateRdv, $rdvs, $site, $idRessource, count($jourRdvsPrestation[$dateRdv]), $jourRdvsPrestation[$dateRdv])=="1" ||
				count(self::getHeureDisponibleJour($idPrestation, $dateRdv, $site, $idRessource))==0) {
				$jourIndispo[$dateRdv] = $dateRdv;
			}
		}
		return $jourIndispo;
	}
	
	public function getHeurePris($idPrestation, $dateRdv, $site=false, $idRessource=null) {
		if($idRessource==null) {
			if($this->idRessources==null) {
				$tAgendaPeer = new TAgendaPeer();
				$idRessourceForRdv = $this->idRessources = $tAgendaPeer->getIdRessourcesByPrestation($idPrestation);
			}
			else {
				$idRessourceForRdv = $this->idRessources;
			}
		}
		else {
			$idRessourceForRdv = $idRessource;
		}
		$tRendezVousPeer = new TRendezVousPeer();
		$jourRdvs = $tRendezVousPeer->getJoursRdv(/*$site,*/ $idRessourceForRdv, $dateRdv);

		if(!isset($jourRdvs[$dateRdv])) {
			return "0";
		}
		$tPeriodeQuery = new TPeriodeQuery();
		return $tPeriodeQuery->isJourPris($idPrestation, $dateRdv, $jourRdvs[$dateRdv], $site, $idRessource);
	}

	public function getHeureDisponibleJour($idPrestation, $dateRdv, $site=false, $idRessource=null, $horsAgenda=false, $visible=true) {
		$heureRdv = array();
		if(isset($this->prestation) && $this->prestation->getIdPrestation()==$idPrestation) {
			$prestation = $this->prestation;
		}
		else {
			$tPrestationQuery = new TPrestationQuery();
			$this->prestation = $prestation = $tPrestationQuery->getPrestationById($idPrestation);
		}
		if(!$prestation) {
			return array();
		}
		$tRendezVousQuery = new TRendezVousQuery();
		$rdvs = $tRendezVousQuery->getHeureNonDisponibleJour($idPrestation, $dateRdv, $idRessource);
		$tPeriodeQuery = new TPeriodeQuery();
		$allHeureRdv = $tPeriodeQuery->getHeureJour($idPrestation, $dateRdv, $prestation->getDelaiMin(), $idRessource, $visible);
		if($horsAgenda) {
			self::getHeureHorsAgenda($prestation, $dateRdv, $idRessource, $allHeureRdv);
		}//print_r($rdvs);exit;
		$heurePris = self::getHeurePris($idPrestation, $dateRdv, $site, $idRessource);
		/*if($heurePris=="1" && !$horsAgenda) {
			return array();
		}*/
		/*if($heurePris=="0" && count($rdvs)==0 && !$horsAgenda) {
			return $allHeureRdv;
		}*/
		$heureDebut=array();
		$heureFin=array();
		if($idRessource==null) {
			if(isset($this->ressources) && isset($this->prestation) && $this->prestation->getIdPrestation()==$idPrestation) {
				$ressources = $this->ressources;
			}
			else {
				$tAgentQuery = new TAgentQuery();
				$this->ressources = $ressources = $tAgentQuery->getRessourceByIdPrestation($idPrestation);
			}
			foreach($ressources as $ressource) {
				$tPeriodeIndispoQuery = new TPeriodeIndisponibiliteQuery();
				$heureDebut[$ressource->getIdAgent()] = $tPeriodeIndispoQuery->getHeureIndispoRessourceJourDebut($ressource->getIdAgent(),$dateRdv);
				$tPeriodeIndispoQuery = new TPeriodeIndisponibiliteQuery();
				$heureFin[$ressource->getIdAgent()] = $tPeriodeIndispoQuery->getHeureIndispoRessourceJourFin($ressource->getIdAgent(),$dateRdv);
			}
		}
		else {
			$tPeriodeIndispoQuery = new TPeriodeIndisponibiliteQuery();
			$heureDebut[$idRessource] = $tPeriodeIndispoQuery->getHeureIndispoRessourceJourDebut($idRessource,$dateRdv);
			$tPeriodeIndispoQuery = new TPeriodeIndisponibiliteQuery();
			$heureFin[$idRessource] = $tPeriodeIndispoQuery->getHeureIndispoRessourceJourFin($idRessource,$dateRdv);
		}
		list($heurePrisDeb,$heurePrisFin) = split("-",$heurePris);
		foreach($allHeureRdv as $uneHeureRdv) {
			if($heurePris!="0" && $uneHeureRdv["heureDebut"]>=$heurePrisDeb && $uneHeureRdv["heureFin"]<=$heurePrisFin) {
				continue;
			}
			if($heureDebut[$uneHeureRdv["idRessource"]]!="" || $heureFin[$uneHeureRdv["idRessource"]]!="") {
				if($uneHeureRdv["heureDebut"]>=$heureDebut[$uneHeureRdv["idRessource"]] && $uneHeureRdv["heureDebut"]<$heureDebut[$uneHeureRdv["idRessource"]]) {
					continue;
				}
			}
			$prise = false;
			foreach($rdvs as $rdv) {
				if($rdv["idRessource"]==$uneHeureRdv["idRessource"]) {
					if(
					    ($uneHeureRdv["heureDebut"]<=$rdv["heureDebut"] && $uneHeureRdv["heureFin"]>$rdv["heureDebut"])
					    ||
                        ($uneHeureRdv["heureDebut"]>=$rdv["heureDebut"] && $uneHeureRdv["heureDebut"]<$rdv["heureFin"])
                        ||
					    ($uneHeureRdv["heureDebut"]<$rdv["heureFin"] && $uneHeureRdv["heureFin"]>=$rdv["heureFin"])
                    ) {
						$prise=true;
						break;
					}
				}
			}
			if($prise) {
				continue;
			}
			if(count($heureRdv[$uneHeureRdv["heureDebut"]])==0) {
				$heureRdv[$uneHeureRdv["heureDebut"]]=$uneHeureRdv;
			}
			else {
				$heureRdv[$uneHeureRdv["heureDebut"]]["idRessource"].="-".$uneHeureRdv["idRessource"];
				$heureRdv[$uneHeureRdv["heureDebut"]]["heureFin"].="-".$uneHeureRdv["heureFin"];
			}
		}
		ksort($heureRdv);
		return $heureRdv;
	}
	
	public function getHeureHorsAgenda($prestation, $dateRdv, $idRessource, &$allHeureRdv) {
		$tPeriodeQuery = new TPeriodeQuery();
		$periodicites = $tPeriodeQuery->getPeriodicite($prestation->getIdPrestation(), $dateRdv, $idRessource);
		$heureDebHorsAgenda = ($dateRdv==date("Y-m-d")) ? str_pad((date("H")+1),2,"0", STR_PAD_LEFT).":00" : Atexo_Config::getParameter('HEURE_OUVERTURE_ETENDU');
		$heureFinHorsAgenda = Atexo_Config::getParameter('HEURE_FERMETURE_ETENDU');
		$i = count($allHeureRdv);
		if(count($periodicites)==0) {
			if($idRessource!=null) {
				$periodicites = array($idRessource=>$prestation->getPeriodicite());
			}
			else {
				$agendas = $prestation->getTAgendas();
				foreach($agendas as $agenda) {
					$periodicites[$agenda->getIdAgent()]=$prestation->getPeriodicite();
				}
			}
		}
		foreach($periodicites as $idRes=>$periodicite) {
			while($heureDebHorsAgenda<$heureFinHorsAgenda) {
				$fin = Atexo_Utils_Util::addMinutes($heureDebHorsAgenda,$periodicite);
				$exist = false;
				foreach($allHeureRdv as $rdv) {
					if($rdv["heureDebut"]==$allHeureRdv[$i]["heureDebut"] 
						&& $allHeureRdv[$i]["heureFin"]==$rdv["heureFin"] 
						&& $allHeureRdv[$i]["idRessource"]==$rdv["idRessource"]) {
						$exist=true;
						break;
					}
				}
				if(!$exist) {
					$allHeureRdv[$i]["periodicite"]=$periodicite;
					$allHeureRdv[$i]["heureDebut"]=$heureDebHorsAgenda;
					$allHeureRdv[$i]["heureFin"]=$fin;
					$allHeureRdv[$i]["idRessource"]=$idRes;
					$allHeureRdv[$i]["horsAgenda"]="1";
					$i++;
				}
				$heureDebHorsAgenda = $fin;
			}
		}
	}

	public function getRessourceDisponible($idPrestation, $dateRdv, $heureDeb, $heureFin, $idRessource=null, $visible=true) {

		$idRessourceDispo = array();

		$rdvs = self::getHeureDisponibleJour($idPrestation, $dateRdv, true, $idRessource, false, $visible);
		foreach($rdvs as $rdv) {
			if($idRessource==null || in_array($idRessource,explode("-",$rdv["idRessource"]))) {
				if(self::isHeureValide($heureDeb, $heureFin, $rdv)) {
					$idRessourceDispo=array_merge(explode("-",$rdv["idRessource"]),$idRessourceDispo);
				}
			}
		}
		return array_unique($idRessourceDispo);
	}
	
	public function isHeureValide($heureDeb, $heureFin, $rdv) {
		
		if($heureDeb<=$rdv["heureDebut"] && $heureFin>$rdv["heureDebut"]) {
			return true;
		}
		
		$heuresFinRdv = explode("-",$rdv["heureFin"]);
		
		foreach($heuresFinRdv as $heureFinRdv) {
			if($heureDeb<$heureFinRdv && $heureFin>=$heureFinRdv) {
				return true;
			}
		}
		
		return false;
	}
	
	public function retrieveRdvByCodeEmailTel($code, $email="", $tel="")
	{
		$c = new Criteria();
		$c->add(TRendezVousPeer::CODE_RDV,$code, CRITERIA::EQUAL);
		if($email!="" || $tel!="") {
			
			$c->addJoin(TRendezVousPeer::ID_CITOYEN,TCitoyenPeer::ID_CITOYEN);
			
			if($email!="") {
				$c->add(TCitoyenPeer::MAIL,$email, CRITERIA::EQUAL);
			}
			if($tel!="") {
				$c->add(TCitoyenPeer::TELEPHONE,$tel, CRITERIA::EQUAL);
			}
		}
		$connexionCom = Propel::getConnection(Atexo_Config::getParameter('DB_NAME').Atexo_Config::getParameter('CONST_READ_ONLY'));
		$tRdvObject = TRendezVousPeer::doSelectOne($c, $connexionCom);
		return $tRdvObject ? $tRdvObject : null;
	}
	
	/**
	 * @param $date : date de création
	 * récuperer les rdv créés
	 */
	public function getRdvCreeLe($date)
	{
		$c = new Criteria();
		$c->add(TRendezVousPeer::DATE_CREATION,$date." 00:00:00", CRITERIA::GREATER_EQUAL);
		$c->addAnd(TRendezVousPeer::DATE_CREATION,$date." 23:59:59", CRITERIA::LESS_EQUAL);
		$connexionCom = Propel::getConnection(Atexo_Config::getParameter('DB_NAME').Atexo_Config::getParameter('CONST_READ_ONLY'));
		return TRendezVousPeer::doSelect($c, $connexionCom);
	}
	
	/**
	 * @param $date : date de rdv
	 * vérifier si la date est disponible
	 */
	public function isRdvDisponible($date, $heure, $idPrestation, $idRessource)
	{
		$tRdvQuery = new TRendezVousQuery();
		$allHeure = $tRdvQuery->getHeureNonDisponibleJour($idPrestation, $date, $idRessource);

		foreach ($allHeure as $heureRdv) {
			if($heureRdv["heureDebut"]<=$heure && $heureRdv["heureFin"]>$heure) {
				return false;
			}
		}
		return true;
	}

}