<?php
/**
 * description de la calsse
 *
 * @author atexo
 * @copyright Atexo 2013
 * @version 0.0
 * @since Atexo.Rdv
 * @package atexo
 * @subpackage atexo
 */
class GestionRdv extends AtexoPage
{
	public $current=1;
	public $noResult=false;
	public $infoRequerant = false;

	public function onInit($param)
    {
        $infoCitoyen = Atexo_User_CurrentUser::readFromSession('infoRequerant');
        if(is_array($infoCitoyen) && count($infoCitoyen)) {
            $this->infoRequerant = true;
        }
    }

    public function onLoad()
    {
    	if(!$this->Page->isPostBack) {
            $this->choixPrestation->setViewState('etabData',array());
    		$this->choixPrestation->initialize();
            $this->Page->Master->setShowPicker(true);
            $infoCitoyen = Atexo_User_CurrentUser::readFromSession('infoRequerant');
            $lang = Atexo_User_CurrentUser::readFromSession("lang");
            if(is_array($infoCitoyen) && count($infoCitoyen)) {
                if(isset($infoCitoyen['idEtablissement'])){
                    $tEtabQ = new TEtablissementQuery();
                    $etab = $tEtabQ->findPk($infoCitoyen['idEtablissement']);
                    if($etab){
                        $this->choixPrestation->typeRdv->SelectedValue = '1';
                        $data = array($infoCitoyen['idEtablissement'] => $etab->getDenominationEtablissementTraduit($lang));
                        $this->choixPrestation->etablissement->DataSource = $data;
                        $this->choixPrestation->etablissement->DataBind();
                        $this->choixPrestation->etablissement->SelectedValue = $infoCitoyen['idEtablissement'];
                        $this->choixPrestation->panelEntite->Visible = true;
                        $this->choixPrestation->loadTypePrestation();

                        if($infoCitoyen['type_prestation'] == 'default'){
                            $typesPrestation = $this->choixPrestation->typePrestation->DataSource;
                            if(count($typesPrestation) > 1){
                                $keys = $typesPrestation->getKeys();
                                $this->choixPrestation->typePrestation->SelectedValue = $keys[1];
                            }else{
                                return false;
                            }
                        }else{
                            $typesPrestation = $this->choixPrestation->typePrestation->DataSource;
                            if($typesPrestation[$infoCitoyen['type_prestation']])
                                $this->choixPrestation->typePrestation->SelectedValue = $infoCitoyen['type_prestation'];
                            else
                                return false;
                        }

                        $this->choixPrestation->loadPrestation();

                        if($infoCitoyen['prestation'] == 'default'){
                            $prestations = $this->choixPrestation->prestation->DataSource;
                            if(count($prestations) > 1){
                                $keys = $prestations->getKeys();
                                $this->choixPrestation->prestation->SelectedValue = $keys[1];
                            }else{
                                return false;
                            }
                        }else{
                            $prestations = $this->choixPrestation->prestation->DataSource;
                            if($prestations[$infoCitoyen['prestation']])
                                $this->choixPrestation->prestation->SelectedValue = $infoCitoyen['prestation'];
                            else
                                return false;
                        }

                        $tPrestationQuery = new TPrestationQuery();
                        $tPrestation = $tPrestationQuery->getPrestationById($this->choixPrestation->prestation->SelectedValue);
                        $this->choixPrestation->loadPrestaForm($tPrestation);

                        $i = 1;
                        foreach ($this->choixPrestation->champsSupPresta->getItems() as $item) {
                            $item->text->Text = $infoCitoyen['champ_supp_rdv_'.$i];
                            $i++;
                        }


                        $this->priseRdv->retour->Visible = false;
                        $this->confirmationRdv->btnRetour->Visible = false;
                        
                        $this->gotoPriseRdv();
                    }else{
                        return false;
                    }
                }


            }else{
                if($_GET["idRdv"]!="") {
                    $tRdvQuery = new TRendezVousQuery();
                    $rvd = $tRdvQuery->getRendezVousById($_GET["idRdv"]);
                    $this->choixPrestation->setRdv($rvd);
                }
                $this->gotoChoixPrestation();
            }
		}
    	$this->msgRdvSimilaire->Visible = false;
    	$this->msgRdvPris->Visible = false;
        $this->confirmationRdv->btnVideo->Visible = false;
    }

    public function gotoChoixPrestation() {
    	$this->panelChoixPrestation->Visible = true;
	    $this->panelPriseRdv->Visible = false;
	    $this->panelCitoyen->Visible = false;
	    $this->panelConfirmation->Visible = false;
        $this->choixPrestation->setVisibleOuFalse();

    }
    
    public function toPriseRdv($back) {
		$this->choixPrestation->saveValuePrestaForm();
    	$this->priseRdv->initialize($this->choixPrestation->getIdPrestation(),
    								$this->choixPrestation->getIdRessource(),
    								$this->choixPrestation->isRecherche(),
									$this->choixPrestation->getNbJours(),
    								$this->choixPrestation->getNbMois(),
									$this->choixPrestation->getNbRdvGroupe(),
									$this->choixPrestation->getIntervalJour(),
    								$this->choixPrestation->isRechercheEtendu(),
									$this->getPrestaForm());
    	if($back || $this->choixPrestation->isRecherche() 
    		|| $this->choixPrestation->isRechercheEtendu() || $this->noResult) {
		    $this->panelPriseRdv->Visible = true;
		    $this->panelChoixPrestation->Visible = false;
		    $this->panelCitoyen->Visible = false;
		    $this->panelConfirmation->Visible = false;
		    $this->current = 2;

    	}
    }
    
    public function gotoPriseRdv() {
    	$this->toPriseRdv(false);
    }
    
    public function returnToPriseRdv() {
		$listeRdvGrp = $this->getViewState("listeRdvGrp");

		if(count($listeRdvGrp)>0) {
			$this->gotoChoixPrestation();
		}
		else {
			$this->toPriseRdv(true);
		}
    }
    
	public function gotoCitoyen($idPrestation, $idRessource, $dateRdv, $heureDeb, $heureFin) {
		
    	$this->formCitoyen->initialize($this->choixPrestation->getIdEtablissement(),$this->choixPrestation->getIdPrestation(),$idRessource,
    		$this->choixPrestation->getTypePrestation(),$this->choixPrestation->getPrestation(),$dateRdv,$heureDeb,$heureFin,
			$this->getPrestaForm());

		if($_GET["idRdv"]!="") {
			$tRdvQuery = new TRendezVousQuery();
			$rvd = $tRdvQuery->getRendezVousById($_GET["idRdv"]);
			$this->formCitoyen->setRdv($rvd);
		}

    	$this->panelPriseRdv->Visible = false;
	    $this->panelChoixPrestation->Visible = false;
	    $this->panelCitoyen->Visible = true;
	    $this->panelConfirmation->Visible = false;
	    $this->current = 3;
	    $this->setViewState("idPrestation",$idPrestation);
    	$this->setViewState("idRessource",$idRessource);
    	$this->setViewState("dateRdv",$dateRdv);
    	$this->setViewState("heureDeb",$heureDeb);
    	$this->setViewState("heureFin",$heureFin);
    }

	public function gotoCitoyenRdvGroupe($idPrestation, $listeRdvGrp) {

		$this->formCitoyen->initializeGrp($this->choixPrestation->getIdEtablissement(),$this->choixPrestation->getIdPrestation(),
			$this->choixPrestation->getTypePrestation(),$this->choixPrestation->getPrestation(),$listeRdvGrp,
			$this->getPrestaForm());

		$this->panelPriseRdv->Visible = false;
		$this->panelChoixPrestation->Visible = false;
		$this->panelCitoyen->Visible = true;
		$this->panelConfirmation->Visible = false;
		$this->current = 3;
		$this->setViewState("idPrestation",$idPrestation);
		$this->setViewState("listeRdvGrp",$listeRdvGrp);
	}

	private function saveRdvGrp() {
		$rdvs = $this->formCitoyen->getRdvGrp();
		$idRessourceDispo = $this->isRdvGrpValide();
		$champsSupp = json_encode($this->choixPrestation->getValuePrestaForm());
		if (is_array($idRessourceDispo) && count($idRessourceDispo) > 0) {

			$idRessource = $idRessourceDispo[0];
			for($i=0;$i<count($rdvs);$i++) {
				$rdvs[$i]->setIdAgentRessource($idRessource);
				if ($this->choixPrestation->getIdReferent() > 0) {
					$rdvs[$i]->setIdReferent($this->choixPrestation->getIdReferent());
				}
				$rdvs[$i]->setEtatRdv(Atexo_Config::getParameter("ETAT_EN_ATTENTE"));

				$tabInfoCitoyen = Atexo_User_CurrentUser::readFromSession('infoRequerant');
				if (is_array($tabInfoCitoyen) && count($tabInfoCitoyen)) {
					$rdvs[$i]->setTagGateway('1');
					$rdvs[$i]->setIdUtilisateur($tabInfoCitoyen['id_utilisateur']);
				}

				$rdvs[$i]->save();
				/*$codeRdv = str_pad($rdvs[$i]->getIdRendezVous(), 8, "0", STR_PAD_LEFT);
				$rdvs[$i]->setCodeRdv($codeRdv);*/

				$codeRdv = $rdvs[$i]->getDateRdv("dmY-Hi")."-".str_pad($rdvs[$i]->getIdAgentRessource(), 4, "0", STR_PAD_LEFT);
				$rdvs[$i]->setCodeRdv($codeRdv);//str_pad($rdv->getIdRendezVous(), 8, "0", STR_PAD_LEFT));

				$rdvs[$i]->setChampSuppPresta($champsSupp);
				$rdvs[$i]->save();
				$exts = array("pdf","png","jpg","jpeg","bmp");
				foreach ($this->formCitoyen->listePj->getItems() as $item) {
					if($item->pj->HasFile) {
						$ext = strtolower(end(explode(".", $item->pj->FileName)));
						if(in_array($ext,$exts)) {
							$tBlob = new TBlob();
							$tBlob->setNomBlob($item->blobName->Text.".".$ext);
							$tBlob->save();
							$tBlobRdv = new TBlobRdv();
							$tBlobRdv->setTBlob($tBlob);
							$tBlobRdv->setTRendezVous($rdvs[$i]);
							$tBlobRdv->save();
							if($item->pj->HasFile) {
								Atexo_Utils_Util::mvFile($item->pj->LocalName, Atexo_Config::getParameter("PATH_FILE"), $tBlobRdv->getIdBlob());
							}
						}
					}
				}
			}

			if ($rdvs[0]->getTCitoyen()->getMail() != "") {
				$this->envoiMailGrp($rdvs);
			}

			$this->panelPriseRdv->Visible = false;
			$this->panelChoixPrestation->Visible = false;
			$this->panelCitoyen->Visible = false;
			$this->panelConfirmation->Visible = true;
			$this->current = 4;
			$this->confirmationRdv->initializeGrp($this->choixPrestation->getTypePrestation(), $this->choixPrestation->getPrestation(), $rdvs);

		} else {
			$this->gotoChoixPrestation();
			$this->msgRdvPris->Visible = true;
		}
	}

	private function saveRdv() {
		$dateRdv = $this->getViewState("dateRdv");
		$rdv = $this->formCitoyen->getRdv();
		$rdv->setChampSuppPresta(json_encode($this->choixPrestation->getValuePrestaForm()));
		$idRessourceDispo = $this->isRdvValide();
		$idPrestation = $this->getViewState("idPrestation");
		if (!$this->choixPrestation->isRechercheEtendu() && trim($rdv->getTCitoyen()->getIdentifiant()) != "") {
			$tPrestationQuery = new TPrestationQuery();
			$tPrestation = $tPrestationQuery->getPrestationById($idPrestation);

			if ($tPrestation->getRdvSimilaire() == "0") {
				$tRendezVousQuery = new TRendezVousQuery();
				$tPeriodeQuery = new TPeriodeQuery();
				$dateDeb = $tPeriodeQuery->getDateRdvMoinsJour($idPrestation, $idRessourceDispo, $dateRdv, $tPrestation->getNbJourRdvSimilaire());
				$datePrise = $tRendezVousQuery->getDateMaxRdvSimilaire($idPrestation, $dateDeb, "2100-01-01", $rdv->getTCitoyen()->getIdentifiant());
				if ($datePrise != "") {
					$this->returnToPriseRdv();
					$this->msgRdvSimilaire->Visible = true;
					$dateDebPrise = $tPeriodeQuery->getDateRdvPlusJour($idPrestation, $idRessourceDispo, $datePrise, $tPrestation->getNbJourRdvSimilaire());
					$this->textRdvSimilaire->Text = Prado::localize("MESSAGE_RDV_SIMILAIRE") . " " . Atexo_Utils_Util::iso2frnDate($dateDebPrise);
					return;
				}
			}
		}

		if (is_array($idRessourceDispo) && count($idRessourceDispo) > 0) {

			if (count($idRessourceDispo) == 1) {
				$idRessource = $idRessourceDispo[0];
			} else {
				$idRessource = TAgentPeer::getRessourceMoinsCharge($idPrestation, $dateRdv, $idRessourceDispo);

				if ($idRessource == null) {
					$idRessource = $idRessourceDispo[0];
				}
			}

			$tRdvQuery = new TRendezVousQuery();
			$exist = $tRdvQuery->isRdvExist($idRessource, $rdv->getDateRdv("Y-m-d H:i:s"), $rdv->getDateFinRdv("Y-m-d H:i:s"));
			if($exist) {
				$this->returnToPriseRdv();
				$this->msgRdvPris->Visible = true;
				return;
			}
			$rdv->setIdAgentRessource($idRessource);
			if ($this->choixPrestation->getIdReferent() > 0) {
				$rdv->setIdReferent($this->choixPrestation->getIdReferent());
			}
			$rdv->setEtatRdv(Atexo_Config::getParameter("ETAT_EN_ATTENTE"));

			$tabInfoCitoyen = Atexo_User_CurrentUser::readFromSession('infoRequerant');
			if (is_array($tabInfoCitoyen) && count($tabInfoCitoyen)) {
				$rdv->setTagGateway('1');
				$rdv->setIdUtilisateur($tabInfoCitoyen['id_utilisateur']);
			}

			$rdv->save();
			$codeRdv = $rdv->getDateRdv("dmY-Hi")."-".str_pad($rdv->getIdAgentRessource(), 4, "0", STR_PAD_LEFT);
			$rdv->setCodeRdv($codeRdv);//str_pad($rdv->getIdRendezVous(), 8, "0", STR_PAD_LEFT));

			$rdv->save();

            $tAgentQ = new TAgentQuery();
            $ressource = $tAgentQ->findPk($idRessource);
            $ressourceEmail = $ressource->getEmailUtilisateur();
            if($ressourceEmail && $ressource->getAlerte() == "1"){
                $mail = self::envoieCalEvent($ressource, $rdv);
            }
            
				$exts = array("pdf","png","jpg","jpeg","bmp");
				foreach ($this->formCitoyen->listePj->getItems() as $item) {
					if($item->pj->HasFile) {
						$ext = strtolower(end(explode(".", $item->pj->FileName)));
						if(in_array($ext,$exts)) {
							$tBlob = new TBlob();
							$tBlob->setNomBlob($item->blobName->Text.".".$ext);
							$tBlob->save();
							$tBlobRdv = new TBlobRdv();
							$tBlobRdv->setTBlob($tBlob);
							$tBlobRdv->setTRendezVous($rdvs[$i]);
							$tBlobRdv->save();
							if($item->pj->HasFile) {
								Atexo_Utils_Util::mvFile($item->pj->LocalName, Atexo_Config::getParameter("PATH_FILE"), $tBlobRdv->getIdBlob());
							}
						}
					}
				}

	

			if ($rdv->getTCitoyen()->getMail() != "") {
				$this->envoiMail($rdv);
			}



			$this->panelPriseRdv->Visible = false;
			$this->panelChoixPrestation->Visible = false;
			$this->panelCitoyen->Visible = false;
			$this->panelConfirmation->Visible = true;
			if($this->unEtab->Value == 'true'){
				$this->confirmationRdv->setVisibleRecapDetails(false);
			}
			$this->current = 4;

			$this->confirmationRdv->initialize(	$this->choixPrestation->getTypePrestation(), 
												$this->choixPrestation->getPrestation(), $rdv);
			if($_GET["idRdv"]!="") {
				$tRdvQuery = new TRendezVousQuery();
				$rvdOld = $tRdvQuery->getRendezVousById($_GET["idRdv"]);
				$rvdOld->setEtatRdv(Atexo_Config::getParameter("ETAT_ANNULE_ETAB"));
				$rvdOld->save();
			}
		} else {
			$this->returnToPriseRdv();
			$this->msgRdvPris->Visible = true;
		}
	}
    
    public function saveCitoyen() {
    	
		$listeRdvGrp = $this->getViewState("listeRdvGrp");

		if(count($listeRdvGrp)>0) {
			$this->saveRdvGrp();
		}
    	else {
			$this->saveRdv();
		}
    }
    
	private function isRdvGrpValide() {

		$listeRdvGrp = $this->getViewState("listeRdvGrp");
		$tRdvGestion = new Atexo_RendezVous_Gestion();
		foreach($listeRdvGrp as $oneRdv) {
			if($oneRdv["dateIso"]<date("Y-m-d")) {
				return false;
			}

			$idPrestation = $this->getViewState("idPrestation");
			$idRessourceChoisi = $oneRdv["idRessource"];

			$jourPris = $tRdvGestion->getJourPris($idPrestation, Atexo_User_CurrentUser::isConnected(), $idRessourceChoisi, $oneRdv["dateIso"]);
			if(count($jourPris)>0) {
				return false;
			}
		}
		return array($idRessourceChoisi);
	}

	private function isRdvValide() {

		$dateRdv = $this->getViewState("dateRdv");

		if($dateRdv<date("Y-m-d")) {
			return false;
		}

		$heureDeb = $this->getViewState("heureDeb");
		$heureFin = $this->getViewState("heureFin");

		$idPrestation = $this->getViewState("idPrestation");
		$idRessourceChoisi = $this->choixPrestation->getIdRessource();
		$tRdvGestion = new Atexo_RendezVous_Gestion();
		if(!$this->choixPrestation->isRechercheEtendu()) {

			$jourPris = $tRdvGestion->getJourPris($idPrestation, Atexo_User_CurrentUser::isConnected(), $idRessourceChoisi, $dateRdv);
			if(count($jourPris)>0) {
				return false;
			}
			return $tRdvGestion->getRessourceDisponible($idPrestation, $dateRdv, $heureDeb, $heureFin, $idRessourceChoisi);
		}
		else {
			if($tRdvGestion->isRdvDisponible($dateRdv, $heureDeb, $idPrestation, $idRessourceChoisi)) {
				return array($idRessourceChoisi);
			}
			return false;
		}
	}

	public function getPrestaForm() {
		return $this->choixPrestation->getValuePrestaForm();
	}
	
	private function envoiMail($rdv)
	{
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$mail = new Atexo_Utils_Mail();
		
		$objet = prado::localize('RECAPITULATIF_DE_VOTRE_DEMANDE_DE_RENDEZ_VOUS');
		
		$corpsMessage = "<b>".prado::localize('RECAPITULATIF_DE_VOTRE_DEMANDE_DE_RENDEZ_VOUS')."</b><br><br>";
		$etab = $rdv->getTEtablissement();
		$corpsMessage .= "<ul><li>".prado::localize('NOM')." : ".$rdv->getTCitoyen()->getNom()." ".$rdv->getTCitoyen()->getPrenom()."</li>";
		//$corpsMessage .= "<li>".prado::localize('IDENTIFIANT')." : ".$rdv->getTCitoyen()->getIdentifiant()."</li>";
		if($rdv->getTPrestation()->getVisioconference() == 0){
			$corpsMessage .= "<li>".prado::localize('ETABLISSEMENT')." : ".$etab->getDenominationEtablissementTraduit($lang)."</li>";
			$corpsMessage .= "<li>".prado::localize('ADRESSE')." : ".$etab->getAdresseEtablissementTraduit($lang)."</li>";
			$corpsMessage .= "<li>".prado::localize('TELEPHONE_POUR_PRISE_RDV')." : ".$etab->getTelephoneRdv()."</li>";
		}
		$corpsMessage .= "<li>".prado::localize('NIVEAU1')." : ".$this->choixPrestation->getTypePrestation()."</li>";
		$corpsMessage .= "<li>".prado::localize('NIVEAU2')." : ".$this->choixPrestation->getPrestation()."</li>";
		
		if($rdv->getIdAgentRessource()!=null && $rdv->getIdAgentRessource()!="" && $rdv->getTPrestation()->getRessourceVisible()=="1") {
			$tAgentQuery = new TAgentQuery();
			$tAgent = $tAgentQuery->getAgentById($rdv->getIdAgentRessource());
			if($_SESSION['typeRessource']) {
				$nomRessource = $tAgent->getCodeUtilisateurTraduit($lang);
			}
			else {
				$nomRessource = $tAgent->getNomPrenomUtilisateurTraduit($lang);
			}
			$corpsMessage .= "<li>".prado::localize('NIVEAU3')." : ".$nomRessource."</li>";
		}
		
		$corpsMessage .= "<li>".prado::localize('HORAIRES')." : ".Prado::localize('LE')." ".$rdv->getDateRdv("d/m/Y")." ".Prado::localize('A')." ".$rdv->getDateRdv("H:i")."</li>";
		$corpsMessage .= "<li>".prado::localize('LE_CODE_DE_CONFIRMATION')." : ".$rdv->getCodeRdv()."</li>";
		if($etab->getLatitudeEtablissement() && $etab->getLongitudeEtablissement()) {
			$corpsMessage .= "<li><a href='https://maps.google.com/maps?hl=".$lang."&amp;q=loc:".
							 $etab->getLatitudeEtablissement().",".$etab->getLongitudeEtablissement().
							 "&amp;z=15&amp;output=embed&amp;iwloc=near'>".prado::localize('PLAN_D_ACCES')."</a><br><br></li>";
		}
		$corpsMessage .= "</ul><br><br>";
		$corpsMessage .= "<li>".prado::localize('MSG_VOIR_DETAIL_OU_ANNULER_RDV').' <a href="'.$this->Page->getPfUrl().
						 '?page=citoyen.GererRendezVous'.'">'.prado::localize('CLIQUER_ICI').'</a>'."<br><br></li>";
		
		try {
			$mail->envoyerMail(Atexo_Config::getParameter('PF_MAIL_FROM'),$rdv->getTCitoyen()->getMail(),$objet,$corpsMessage);
		}catch (Exception $e){
			//$logger = Atexo_LoggerManager::getLogger("rdvLogErreur");
		    //$logger->error($e->getMessage());
			Atexo_Utils_GestionException::catchException($e);
		}
	}

	private function envoiMailGrp($rdvs)
	{
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$mail = new Atexo_Utils_Mail();

		$objet = prado::localize('RECAPITULATIF_DE_VOTRE_DEMANDE_DE_RENDEZ_VOUS');

		$corpsMessage = "<b>".prado::localize('RECAPITULATIF_DE_VOTRE_DEMANDE_DE_RENDEZ_VOUS')."</b><br><br>";
		$etab = $rdvs[0]->getTEtablissement();
		$corpsMessage .= "<ul><li>".prado::localize('NOM')." : ".$rdvs[0]->getTCitoyen()->getNom()." ".$rdvs[0]->getTCitoyen()->getPrenom()."</li>";
		//$corpsMessage .= "<li>".prado::localize('IDENTIFIANT')." : ".$rdv->getTCitoyen()->getIdentifiant()."</li>";
		if($rdvs[0]->getTPrestation()->getVisioconference() == 0){
			$corpsMessage .= "<li>".prado::localize('ETABLISSEMENT')." : ".$etab->getDenominationEtablissementTraduit($lang)."</li>";
			$corpsMessage .= "<li>".prado::localize('ADRESSE')." : ".$etab->getAdresseEtablissementTraduit($lang)."</li>";
			$corpsMessage .= "<li>".prado::localize('TELEPHONE_POUR_PRISE_RDV')." : ".$etab->getTelephoneRdv()."</li>";
		}
		$corpsMessage .= "<li>".prado::localize('NIVEAU1')." : ".$this->choixPrestation->getTypePrestation()."</li>";
		$corpsMessage .= "<li>".prado::localize('NIVEAU2')." : ".$this->choixPrestation->getPrestation()."</li>";

		if($rdvs[0]->getIdAgentRessource()!=null && $rdvs[0]->getIdAgentRessource()!="" && $rdvs[0]->getTPrestation()->getRessourceVisible()=="1") {
			$tAgentQuery = new TAgentQuery();
			$tAgent = $tAgentQuery->getAgentById($rdvs[0]->getIdAgentRessource());
			$corpsMessage .= "<li>".prado::localize('NIVEAU3')." : ".$tAgent->getNomPrenomUtilisateurTraduit($lang)."</li>";
		}

		foreach($rdvs as $rdv) {
			$corpsMessage .= "<li>" . prado::localize('HORAIRES') . " : " . Prado::localize('LE') . " " . $rdv->getDateRdv("d/m/Y") . " "
								. Prado::localize('A') . " " . $rdv->getDateRdv("H:i"). " - ".prado::localize('LE_CODE_DE_CONFIRMATION') . " : " . $rdv->getCodeRdv() . "</li>";
		}
		if($etab->getLatitudeEtablissement() && $etab->getLongitudeEtablissement()) {
			$corpsMessage .= "<li><a href='https://maps.google.com/maps?hl=".$lang."&amp;q=loc:".
				$etab->getLatitudeEtablissement().",".$etab->getLongitudeEtablissement().
				"&amp;z=15&amp;output=embed&amp;iwloc=near'>".prado::localize('PLAN_D_ACCES')."</a><br><br></li>";
		}
		$corpsMessage .= "</ul><br><br>";
		$corpsMessage .= "<li>".prado::localize('MSG_VOIR_DETAIL_OU_ANNULER_RDV').' <a href="'.$this->Page->getPfUrl().
			'?page=citoyen.GererRendezVous'.'">'.prado::localize('CLIQUER_ICI').'</a>'."<br><br></li>";

		try {
			$mail->envoyerMail(Atexo_Config::getParameter('PF_MAIL_FROM'),$rdvs[0]->getTCitoyen()->getMail(),$objet,$corpsMessage);
		}catch (Exception $e){
			//$logger = Atexo_LoggerManager::getLogger("rdvLogErreur");
			//$logger->error($e->getMessage());
			Atexo_Utils_GestionException::catchException($e);
		}
	}



    private function envoieCalEvent($ressource, $rdv){
        $to_name = $ressource->getPrenomUtilisateurTraduit('fr')." ".$ressource->getNomUtilisateurTraduit('fr');
        $visio = $rdv->getTPrestation()->getVisioconference();
        $from_name = Prado::localize('PF_MAIL_FROM');
        $subject = Prado::localize('MAIL_SUBJECT')." - ".$rdv->getTCitoyen()->getRaisonSocial();
        $from_address = Atexo_Config::getParameter('PF_MAIL_FROM');

        $description = Prado::localize('MAIL_HEADER')." : ".
			"<br><br>".Prado::localize('NOM').": ".$rdv->getTCitoyen()->getNom()."
			<br>".Prado::localize('PRENOM').": ".$rdv->getTCitoyen()->getPrenom()."
			<br>".Prado::localize('RAISON_SOCIAL').": ".$rdv->getTCitoyen()->getRaisonSocial()."
			<br>".Prado::localize('IDENTIFIANT').": ".$rdv->getTCitoyen()->getIdentifiant();

        if($rdv->getModePriseRdv()==Atexo_Config::getParameter("MODE_AGENT_TELEOPERATEUR_TELEPHONE")) {
            $description .= "<br>".Prado::localize('NIVEAU1').": ".Prado::localize('RDV_TEL');
            $location = "";
        }
        else {
            $description .= "<br>".Prado::localize('NIVEAU1').": ".Prado::localize('RDV_PHYS');
            $location = $rdv->getTCitoyen()->getAdresse();
        }
        $description .= "<br>".Prado::localize('DATE_HEURE_RDV')." : ".$rdv->getDateRdv("d/m/Y H:i").
            "<br>".Prado::localize('NUM_TEL_CLIENT')." : ".$rdv->getTCitoyen()->getTelephone();

        if($rdv->getModePriseRdv()!=Atexo_Config::getParameter("MODE_AGENT_TELEOPERATEUR_TELEPHONE")) {
            $description .="<br>".Prado::localize('ADRESSE')." :".$rdv->getTCitoyen()->getAdresse();
        }
        $description .= "<br><br>".Prado::localize('CORDIALEMENT').".";
        $startTime = $rdv->getDateRdv("m/d/Y H:i:s");
        $endTime = $rdv->getDateFinRdv("m/d/Y H:i:s");
        $tel = $rdv->getTCitoyen()->getTelephone();
        $address = $rdv->getTCitoyen()->getAdresse();
        $cc = array();
        $to = explode(',', $ressource->getEmailUtilisateur());
        $to_address = $to[0];
        if(count($to)>1){
            array_shift($to);
            $cc = $to;
        }
        $bcc = "";
        if($to_address){
            $mail = self::sendIcalEvent($from_name, $from_address, $to_name, $to_address, $cc, $bcc, $startTime, $endTime, $subject, $description, $location, $tel, $address);
        }
        return $mail;
    }

    private function sendIcalEvent($from_name, $from_address, $to_name, $to_address, $cc, $bcc, $startTime, $endTime, $subject, $description, $location, $tel, $address='')
    {
        $domain = 'rdv.com';

        $format = 'Y-m-d H:i:s';
        $icalformat = 'Ymd\THis';
        $lang = Atexo_User_CurrentUser::readFromSession("lang");
        $headers = array();
        $mime_boundary = "----Meeting Booking----".md5(time());
        $message = "--$mime_boundary\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\n";
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= "<html>\n";
        $message .= "<body>\n";
        if($lang == "ar")
            $message .= "<div style='text-align:right;'>";
        else
            $message .= "<div style='text-align:left;'>";
        $message .= '<p>Bonjour '.$to_name.',</p>';
        $message .= '<p>'.$description.'.</p>';
        $message .= '</div>';
        $message .= "</body>\n";
        $message .= "</html>\n";
        $message .= "--$mime_boundary\r\n";

        $ical = 'BEGIN:VCALENDAR' . "\r\n" .
            'PRODID:-//Microsoft Corporation//Outlook 10.0 MIMEDIR//EN' . "\r\n" .
            'VERSION:2.0' . "\r\n" .
            'METHOD:REQUEST' . "\r\n" .
            'BEGIN:VTIMEZONE' . "\r\n" .
            'TZID:Europe/Paris' . "\r\n" .
            'BEGIN:STANDARD' . "\r\n" .
            'DTSTART:20091101T020000' . "\r\n" .
            'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=1SU;BYMONTH=11' . "\r\n" .
            'TZOFFSETFROM:+0100' . "\r\n" .
            'TZOFFSETTO:+0100' . "\r\n" .
            'TZNAME:WEST' . "\r\n" .
            'END:STANDARD' . "\r\n" .
            'BEGIN:DAYLIGHT' . "\r\n" .
            'DTSTART:20090301T020000' . "\r\n" .
            'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=2SU;BYMONTH=3' . "\r\n" .
            'TZOFFSETFROM:+0100' . "\r\n" .
            'TZOFFSETTO:+0100' . "\r\n" .
            'TZNAME:WEST' . "\r\n" .
            'END:DAYLIGHT' . "\r\n" .
            'END:VTIMEZONE' . "\r\n" .
            'BEGIN:VEVENT' . "\r\n" .
            'ORGANIZER;CN="'.$from_name.'":MAILTO:'.$from_address. "\r\n" .
            'ATTENDEE;CN="'.$to_name.'";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:'.$to_address. "\r\n" .
            'LAST-MODIFIED:' . date("Ymd\TGis") . "\r\n" .
            'UID:'.date("Ymd\TGis", strtotime($startTime)).rand()."@".$domain."\r\n" .
            'DTSTAMP:'.date("Ymd\TGis"). "\r\n" .
            'DTSTART:'.date("Ymd\THis", strtotime($startTime)). "\r\n" .
            'DTEND:'.date("Ymd\THis", strtotime($endTime)). "\r\n" .
            'TRANSP:OPAQUE'. "\r\n" .
            'SEQUENCE:1'. "\r\n" .
            'SUMMARY:' . $subject . "\r\n" .
            'LOCATION:' . $location . "\r\n" .
            'CLASS:PUBLIC'. "\r\n" .
            'PRIORITY:5'. "\r\n" .
            'BEGIN:VALARM' . "\r\n" .
            'TRIGGER:-PT15M' . "\r\n" .
            'ACTION:DISPLAY' . "\r\n" .
            'DESCRIPTION:Reminder' . "\r\n" .
            'END:VALARM' . "\r\n" .
            'END:VEVENT'. "\r\n" .
            'END:VCALENDAR'. "\r\n";

        $message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST\n';
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= $ical;
//Create Mime Boundry


//Create Email Headers
        $headers = "From:  <notification@rdv.gov.ma>\n";
        $headers .= "Reply-To: <notification@rdv.gov.ma>\n";

        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
        $headers .= "Content-class: urn:content-classes:calendarmessage\n";
        $mailsent = mail($to_address, $subject, $message, $headers);

        return ($mailsent)?(true):(false);
    }

}
