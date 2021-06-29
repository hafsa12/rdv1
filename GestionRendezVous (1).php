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

class GestionRendezVous extends AtexoPage {

	public function onInit()
	{
		$this->Master->setCalledFrom("admin");
		Atexo_Utils_Languages::setLanguageCatalogue($this->Master->getCalledFrom());
	}

	public function onLoad()
	{
		$this->paneldeleteNOk->style = "display:none";
		$this->paneldeleteOk->style = "display:none";
		if(!Atexo_User_CurrentUser::hasHabilitation('GestionDesRendezVous')) {
			$this->response->redirect("?page=administration.AccueilAdministrateurAuthentifie");
		}
		if(!$this->isPostBack) {
			$adminOrg = Atexo_User_CurrentUser::isAdminOrg();
			$adminEtab = Atexo_User_CurrentUser::isAdminEtab() || Atexo_User_CurrentUser::isAdminOrgWithEtab();
			$idOrg = Atexo_User_CurrentUser::getIdOrganisationGere();
			/*if($idOrg>0) {
				$tOrganisationQuery = new TOrganisationQuery();
				$tOrganisation = $tOrganisationQuery->getOrganisationById($idOrg);
				$typePrestation = $tOrganisation->getTypePrestation();
			}
			else {*/
				$typePrestation = Atexo_Config::getParameter('PRESTATION_SAISIE_LIBRE');
			//}
			$this->setViewState("typePrestation", $typePrestation);

			if($adminOrg) {
				$this->loadEntite1();
				$this->loadEntite2();
				$this->loadEntite3();
			} else {
				$this->entite1->visible = false;
				$this->entite2->visible = false;
				$this->entite3->visible = false;
			}

			$this->loadEtablissement();
			//
			if($adminEtab) {
				$idEtablissement = Atexo_User_CurrentUser::getIdEtablissementGere();
				$this->listeEtablissement->setSelectedValue($idEtablissement);
			}
			//
			$this->loadTypePrestation();
			$this->loadPrestation();
			$this->loadRessource();
			$this->loadEtat();
			$this->loadModePriseRdv();
			$this->datedebut->Text=date("d/m/Y");
			$this->datefin->Text=date("d/m/Y");
			$this->suggestNames();
			$this->getListeAnnulationParLangues();
		}
	}

	public function loadEtablissementPrestation($sender, $param) {
		$this->loadEtablissement();
		$this->loadTypePrestation();
	}

	/**
	 * Remplir la liste des Etablissements
	 */
	public function loadEtablissement($idEntite1 = null, $idsEntite2 = null) {
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$etablissementGestion = new Atexo_Etablissement_Gestion();
        $idsCommune = null;
        
        $idOrganisation = Atexo_User_CurrentUser::getIdOrganisationAttache();
		if($idEntite1) {
			$entiteGestion = new Atexo_Entite_Gestion();
			$idsCommune = $entiteGestion->getAllIdChildEntite ($idEntite1);
		}
		if($idsEntite2) {
			$entiteGestion = new Atexo_Entite_Gestion();
			$idsCommune = $entiteGestion->getAllIdChildEntite ($idsEntite2);
		}
		if($this->entite3->getSelectedValue ()) {
			$idsCommune = $this->entite3->getSelectedValue ();
		}

		$this->listeEtablissement->DataSource = $etablissementGestion->getEtablissementByIdProvinceIdOrganisation($lang, $idOrganisation, $idsCommune, Prado::localize('ETABLISSEMENT'), true);
		$this->listeEtablissement->DataBind();
	}

	/**
	 * Remplir la liste des Types-prestations
	 */
	public function loadTypePrestation() {
		$typePrestation = $this->getViewState("typePrestation");
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$typePrestationGestion = new Atexo_TypePrestation_Gestion();

		if($typePrestation==Atexo_Config::getParameter("PRESTATION_SAISIE_LIBRE")) {
			$dataSource = $typePrestationGestion->getTypePrestationByIdEtab($lang,$this->listeEtablissement->SelectedValue,Prado::localize('NIVEAU1'));
		}
		else {
			$dataSource = $typePrestationGestion->getRefTypePrestationByIdEtab($lang,$this->listeEtablissement->SelectedValue,Prado::localize('NIVEAU1'));
		}
		$this->listeTypePrestation->DataSource = $dataSource;
		$this->listeTypePrestation->DataBind();
		$this->listePrestation->DataSource = array(Prado::localize('NIVEAU2'));
		$this->listePrestation->DataBind();
		$this->listeRessource->DataSource = array(Prado::localize('NIVEAU3'));
		$this->listeRessource->DataBind();
		$this->loadRessource();
	}

	/**
	 * Remplir la liste des Prestations
	 */
	public function loadPrestation() {
		$typePrestation = $this->getViewState("typePrestation");
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$prestationGestion = new Atexo_Prestation_Gestion();

		if($this->listeTypePrestation->SelectedValue>0) {
			if($typePrestation==Atexo_Config::getParameter("PRESTATION_SAISIE_LIBRE")) {
				$data = $prestationGestion->getPrestationByIdTypePrestation($lang, $this->listeTypePrestation->SelectedValue, $_SESSION["typePrestation"], Prado::localize('NIVEAU2'));
			}
			else {
				if ($this->listeEtablissement->SelectedValue > 0) {
					$idetablissement = array($this->listeEtablissement->SelectedValue);
				} else {
					$idetablissement = explode(",", Atexo_User_CurrentUser::getIdEtablissementGere());
				}
				if (count($idetablissement) > 0) {
					$data = $prestationGestion->getPrestationByIdTypeRefPrestation($lang, $this->listeTypePrestation->SelectedValue,$idetablissement, Prado::localize('NIVEAU2'));
				} else {
					$data = $prestationGestion->getRefPrestationByIdRefTypePrestation($lang, $this->listeTypePrestation->SelectedValue, Prado::localize('NIVEAU2'));
				}
			}
		}
		else {
			$data[] = Prado::localize('NIVEAU2');
		}
		$this->listePrestation->DataSource = $data;
		$this->listePrestation->DataBind();
		$this->listeRessource->DataSource = array(Prado::localize('NIVEAU3'));
		$this->listeRessource->DataBind();
	}

	/**
	 * Remplir la liste des Ressourcess
	 */
	public function loadRessource() {
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$ressourceGestion = new Atexo_Agent_Gestion();

		if($this->listePrestation->SelectedValue>0) {
			$typePrestation = $this->getViewState("typePrestation");
			$data = $ressourceGestion->getRessourceByIdRefPrestation($lang, $this->listePrestation->SelectedValue, Prado::localize('NIVEAU3'), false,$typePrestation, $this->listeEtablissement->SelectedValue);
		}
		elseif($this->listeEtablissement->SelectedValue>0) {
			$data = $ressourceGestion->getRessourceByEtablissement($lang, $this->listeEtablissement->SelectedValue, Prado::localize('NIVEAU3'));
		}

		$this->listeRessource->DataSource = $data;
		$this->listeRessource->DataBind();
	}

	/**
	 * Remplir la liste des etats du rendez-vous
	 */
	public function loadEtat() {
		$data = array();
		$data[0] = Prado::localize('ETAT_RDV');
		$data[1] = Prado::localize('EN_ATTENTE');
		$data[2] = Prado::localize('CONFIRME');
		$data[3] = Prado::localize('ANNULE_CITOYEN');
		$data[4] = Prado::localize('NON_HONORE_CITOYEN');
		$data[5] = Prado::localize('ANNULE_ETAB');
		$data[6] = Prado::localize('NON_HONORE_ETAB');
			
		$this->listeEtat->DataSource = $data;
		$this->listeEtat->DataBind();
	}

	/**
	 * Remplir la liste des mode de prise du rendez-vous
	 */
	public function loadModePriseRdv() {
		$data = array();
		$data[0] = Prado::localize('MODE_PRISE_RENDEZ_VOUS');
		$data[1] = Prado::localize('WEB');
		$data[2] = Prado::localize('PHONE');
		$data[3] = Prado::localize('SUR_PLACE');
		$data[4] = Prado::localize('APPLICATION_MOBILE');
			
		$this->listeModePriseRdv->DataSource = $data;
		$this->listeModePriseRdv->DataBind();
	}

	/**
	 * @param $criteriaVo
	 * Remplir repeater des rendez-vous selon les critères de recherche
	 */
	public function fillRepeaterWithDataForSearchResult($criteriaVo) {

		$tRendezVousPeer = new TRendezVousPeer();
		$nombreElement = $tRendezVousPeer->getRdvByCriteres($criteriaVo, true);
		if ($nombreElement>=1) {
			$this->nombreElement->Text=$nombreElement;
			$this->PagerBottom->setVisible(true);
			$this->PagerTop->setVisible(true);
			$this->panelBottom->setVisible(true);
			$this->panelTop->setVisible(true);
			$this->ajoutPanelBas->style="display:block";
			//$this->panelActions->style="display:block";
			$this->setViewState("nombreElement",$nombreElement);

			$this->nombrePageTop->Text=ceil($nombreElement/$this->listeRdv->PageSize);
			$this->nombrePageBottom->Text=ceil($nombreElement/$this->listeRdv->PageSize);
			$this->listeRdv->setVirtualItemCount($nombreElement);
			$this->listeRdv->setCurrentPageIndex(0);
			$this->setViewState("CriteriaVo",$criteriaVo);

			$this->populateData($criteriaVo);
		} else {
			$this->PagerBottom->setVisible(false);
			$this->PagerTop->setVisible(false);
			$this->panelTop->setVisible(false);
			$this->panelBottom->setVisible(false);
			$this->listeRdv->DataSource=array();
			$this->listeRdv->DataBind();
			$this->nombreElement->Text="0";
		}
	}

	/**
	 * @param $criteriaVo
	 * Peupler les données des rendez-vous
	 */
	public function populateData(Atexo_RendezVous_CriteriaVo $criteriaVo)
	{
		$nombreElement = $this->getViewState("nombreElement");
		$offset = $this->listeRdv->CurrentPageIndex * $this->listeRdv->PageSize;
		$limit = $this->listeRdv->PageSize;
		/*if ($offset + $limit > $nombreElement) {
			$limit = $nombreElement - $offset;
		}*/
		$criteriaVo->setOffset($offset);
		$criteriaVo->setLimit($limit);
		$dataRdv = TRendezVousPeer::getRdvByCriteres($criteriaVo);
		$this->listeRdv->DataSource=$dataRdv;
		$this->listeRdv->DataBind();
	}

	public function Trier($sender,$param)
	{
		$champsOrderBy = $sender->CommandParameter;
		$this->setViewState('sortByElement',$champsOrderBy);
		$criteriaVo=$this->getViewState("CriteriaVo");
		$criteriaVo->setSortByElement($champsOrderBy);
		$arraySensTri=$this->getViewState("sensTriArray",array());
		$arraySensTri[$champsOrderBy]=($criteriaVo->getSensOrderBy()=="ASC")? "DESC" : "ASC";
		$criteriaVo->setSensOrderBy($arraySensTri[$champsOrderBy]);
		$this->setViewState("sensTriArray",$arraySensTri);
		$this->setViewState("CriteriaVo",$criteriaVo);
		$this->listeRdv->setCurrentPageIndex(0);
		$this->numPageBottom->Text = 1;
		$this->numPageTop->Text = 1;
		$this->populateData($criteriaVo);
		$this->rdvPanel->render($param->getNewWriter());
	}

	/**
	 * Rechercher rendez-vous par critères
	 */
	protected function suggestNames() {
		$nomCitoyen=$this->nomCitoyen->SafeText;
		$codeRdv=$this->codeRdv->SafeText;
		$dateDu=$this->datedebut->SafeText;
		$dateAu=$this->datefin->SafeText;
		$dateCreation=$this->dateCreat->SafeText;
		$criteriaVo = new Atexo_RendezVous_CriteriaVo();
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$criteriaVo->setLang($lang);

		if($nomCitoyen!=null) {
			$criteriaVo->setNomCitoyen($nomCitoyen);
		}

		if($codeRdv!=null) {
			$criteriaVo->setCodeRdv($codeRdv);
		}

		if($dateDu!=null) {
			$criteriaVo->setDateDu($dateDu);
		}

		if($dateAu!=null) {
			$criteriaVo->setDateAu($dateAu);
		}

		if($dateCreation!=null) {
			$criteriaVo->setDateCreation($dateCreation);
		}
		
		$idOrg = Atexo_User_CurrentUser::getIdOrganisationGere();
		if($idOrg>0) {
			$criteriaVo->setIdOrganisationAttache($idOrg);	
		}

		if ( $this->entite1->getSelectedValue () > 0 ) {
			$criteriaVo->setIdEntite ( $this->entite1->getSelectedValue () );
		}
		if ( $this->entite2->getSelectedValue () > 0 ) {
			$criteriaVo->setIdEntite ( $this->entite2->getSelectedValue () );
		}
		if ( $this->entite3->getSelectedValue () > 0 ) {
			$criteriaVo->setIdEntite ( $this->entite3->getSelectedValue () );
		}
		if($this->listeEtablissement->getSelectedValue()>0) {
			$criteriaVo->setIdEtablissementAttache($this->listeEtablissement->getSelectedValue());
		}
		elseif(Atexo_User_CurrentUser::isAdminEtab()) {
			$criteriaVo->setIdEtablissementAttache(Atexo_User_CurrentUser::getIdEtablissementGere());
		}
		$typePrestation = $this->getViewState("typePrestation");
		if($this->listeTypePrestation->getSelectedValue()>0) {

			if($typePrestation==Atexo_Config::getParameter("PRESTATION_SAISIE_LIBRE")) {
				$criteriaVo->setIdTypePrestation($this->listeTypePrestation->getSelectedValue());
			}
			else {
				$criteriaVo->setIdRefTypePrestation($this->listeTypePrestation->getSelectedValue());
			}
		}
		if($this->listePrestation->getSelectedValue()>0) {
			if($typePrestation==Atexo_Config::getParameter("PRESTATION_SAISIE_LIBRE")) {
				$criteriaVo->setIdPrestation($this->listePrestation->getSelectedValue());
			}
			else {
				$criteriaVo->setIdRefPrestation($this->listePrestation->getSelectedValue());
			}
		}
		if($this->listeRessource->getSelectedValue() > 0) {
			$criteriaVo->setIdRessource($this->listeRessource->getSelectedValue());
		}

		if($this->listeEtat->getSelectedValue()>0) {
			$criteriaVo->setIdEtat($this->listeEtat->getSelectedValue()-1);
		}

		if($this->listeModePriseRdv->getSelectedValue()>0) {
			$criteriaVo->setIdModePriseRdv($this->listeModePriseRdv->getSelectedValue()-1);
		}

		//$criteriaVo->setIdEtablissementAttache(Atexo_User_CurrentUser::getIdEtablissementGere());
		$this->listeRdv->CurrentPageIndex = 0;
		$this->numPageBottom->Text = 1;
		$this->numPageTop->Text = 1;
		$this->setViewState("CriteriaVo",$criteriaVo);
		$this->fillRepeaterWithDataForSearchResult($criteriaVo);
	}

	public function pageChanged($sender,$param)
	{
		$this->listeRdv->CurrentPageIndex =$param->NewPageIndex;
		$this->numPageBottom->Text=$param->NewPageIndex+1;
		$this->numPageTop->Text=$param->NewPageIndex+1;
		$criteriaVo=$this->getViewState("CriteriaVo");
		$this->populateData($criteriaVo);
	}

	public function goToPage($sender)
	{
		switch ($sender->ID) {
			case "DefaultButtonTop" :    $numPage=$this->numPageTop->Text;
			break;
			case "DefaultButtonBottom" : $numPage=$this->numPageBottom->Text;
			break;
		}
		if (Atexo_Utils_Util::isEntier($numPage)) {
			if ($numPage>=$this->nombrePageTop->Text) {
				$numPage=$this->nombrePageTop->Text;
			} else if ($numPage<=0) {
				$numPage=1;
			}
			$this->listeRdv->CurrentPageIndex =$numPage-1;
			$this->numPageBottom->Text=$numPage;
			$this->numPageTop->Text=$numPage;
			$criteriaVo=$this->getViewState("CriteriaVo");
			$this->populateData($criteriaVo);
		} else {
			$this->numPageTop->Text=$this->listeRdv->CurrentPageIndex+1;
			$this->numPageBottom->Text=$this->listeRdv->CurrentPageIndex+1;
		}
	}

	public function changePagerLenght($sender)
	{
		switch ($sender->ID) {
			case "nombreResultatAfficherBottom" : $pageSize=$this->nombreResultatAfficherBottom->getSelectedValue();
			$this->nombreResultatAfficherTop->setSelectedValue($pageSize);
			break;
			case "nombreResultatAfficherTop" : $pageSize=$this->nombreResultatAfficherTop->getSelectedValue();
			$this->nombreResultatAfficherBottom->setSelectedValue($pageSize);
			break;
		}
			
		$this->listeRdv->PageSize=$pageSize;
		$nombreElement=$this->getViewState("nombreElement");
		$this->nombrePageTop->Text=ceil($nombreElement/$this->listeRdv->PageSize);
		$this->nombrePageBottom->Text=ceil($nombreElement/$this->listeRdv->PageSize);
		$criteriaVo=$this->getViewState("CriteriaVo");
		$this->listeRdv->setCurrentPageIndex(0);
		$this->populateData($criteriaVo);
	}

	public function isTrierPar($champ) {
		$sortByElement = $this->getViewState('sortByElement');
		if($champ!=$sortByElement) {
			return "";
		}
		$arraySens = $this->getViewState("sensTriArray");
		if($arraySens[$sortByElement]=="ASC") {
			return "tri-on tri-asc";
		}
		return "tri-on tri-desc";
	}

	public function afficheBtnActions() {
		foreach($this->listeRdv->getItems() as $item) {
			if ($item->checkItem->getChecked()) {
				$this->panelActions->style="display:block";
			}
		}
	}

	public function selectionner() {
		foreach($this->listeRdv->getItems() as $item) {
			if($item->checkItem->Visible) {
				if ($item->checkItem->getChecked()) {
					$this->panelActions->style = "display:none";
					$item->checkItem->setChecked(false);
				} else {
					$this->panelActions->style = "display:block";
					$item->checkItem->setChecked(true);
				}
			}
		}
	}

	/**
	 *
	 * Confirmer la bonne tenue d'une selection des rendez-vous
	 */
	public function onConfirmeSelectionClick($sender,$param) {

		foreach($this->listeRdv->getItems() as $item) {
			if ($item->checkItem->getChecked()){
				$tRendezVousQuery = new TRendezVousQuery();
				$idRendezVous = $item->checkItem->Value;
				$tRendezVous = $tRendezVousQuery->getRendezVousById($idRendezVous);
				$date = date("Y-m-d");
				$idAgent = Atexo_User_CurrentUser::getIdAgentConnected();
				$tRendezVous->setEtatRdv(Atexo_Config::getParameter("ETAT_CONFIRME")) ;
				$tRendezVous->setDateConfirmation($date) ;
				$tRendezVous->setIdAgentConfirmation($idAgent) ;
				$tRendezVous->save();
			}
		}

		$this->paneldeleteOk->style="display:block";
		$criteriaVo=$this->getViewState("CriteriaVo");
		$this->fillRepeaterWithDataForSearchResult($criteriaVo);
		$this->rdvPanel->render($param->NewWriter);
	}

	/**
	 *
	 * Confirmer la bonne tenue d'un rendez-vous séléctionné
	 */
	public function onConfirmeItemClick($sender,$param) {
		$tRendezVousQuery = new TRendezVousQuery();
		$idRendezVous = $this->rendezVousToConfirmHidden->Value;
		$tRendezVous = $tRendezVousQuery->getRendezVousById($idRendezVous);
		$date = date("Y-m-d");
		$idAgent = Atexo_User_CurrentUser::getIdAgentConnected();
		$tRendezVous->setEtatRdv(Atexo_Config::getParameter("ETAT_CONFIRME")) ;
		$tRendezVous->setDateConfirmation($date) ;
		$tRendezVous->setIdAgentConfirmation($idAgent) ;
		$tRendezVous->save();
		$this->paneldeleteOk->style="display:block";
		$criteriaVo=$this->getViewState("CriteriaVo");
		$this->fillRepeaterWithDataForSearchResult($criteriaVo);
		$this->rdvPanel->render($param->NewWriter);
	}

	public function onNonHonoreItemClick($sender,$param) {
		$tRendezVousQuery = new TRendezVousQuery();
		$idRendezVous = $this->rendezVousToNonHonoreHidden->Value;
		$tRendezVous = $tRendezVousQuery->getRendezVousById($idRendezVous);
		$date = date("Y-m-d");
		$idAgent = Atexo_User_CurrentUser::getIdAgentConnected();
		$tRendezVous->setEtatRdv(Atexo_Config::getParameter("ETAT_NON_HONORE")) ;
		$tRendezVous->setDateConfirmation($date) ;
		$tRendezVous->setIdAgentConfirmation($idAgent) ;
		$tRendezVous->save();
		$this->paneldeleteOk->style="display:block";
		$criteriaVo=$this->getViewState("CriteriaVo");
		$this->fillRepeaterWithDataForSearchResult($criteriaVo);
		$this->rdvPanel->render($param->NewWriter);
	}

	public function setItemRdv($rdv,$motif,$date,$idAgent, $etat) {
		if(isset($rdv)) {
			$rdv->setEtatRdv($etat) ;
			$rdv->setMotifAnnulation($motif) ;
			$rdv->setDateAnnulation($date) ;
			$rdv->setIdAgentAnnulation($idAgent) ;
			$rdv->save();
			$citoyen=$rdv->getTCitoyen();
			if($citoyen->getMail()) {
				$this->envoiMail($rdv);
			}
		}
	}

	/**
	 *
	 * Annuler une selection des rendez-vous
	 */
	public function onAnnuleSelectionClick($sender,$param) {
		$diffDate = false;
		$listeRdv = array();
		$dateRdv = null;
		$typeDate = null;

		$envoieSms = $this->envoiSMSGrp->Checked;

		if ($this->annuleGrpCitoyen->checked) {
			$etat = Atexo_Config::getParameter("ETAT_ANNULE");
		}
		if ($this->annuleGrpEtablissement->checked) {
			$etat = Atexo_Config::getParameter("ETAT_ANNULE_ETAB");
		}
		if ($this->nonHonoreGrpCitoyen->checked) {
			$envoieSms = false;
			$etat = Atexo_Config::getParameter("ETAT_NON_HONORE");
		}
		if ($this->nonHonoreGrpEtablissement->checked) {
			$envoieSms = false;
			$etat = Atexo_Config::getParameter("ETAT_NON_HONORE_ETAB");
		}

		foreach($this->listeRdv->getItems() as $item) {
			if ($item->checkItem->getChecked()){

				$tRendezVousQuery = new TRendezVousQuery();
				$idRendezVous = $item->checkItem->Value;
				$tRendezVous = $tRendezVousQuery->getRendezVousById($idRendezVous);

				if(!$dateRdv) {
					$dateRdv = $tRendezVous->getDateRdv("Y-m-d");
				}
				if($typeDate==null) {
					if($dateRdv>date("Y-m-d")) {
						$typeDate = "ETAT_ANNULE";
					}
					else {
						$typeDate = "ETAT_NON_HONORE";
					}
				}

				if($typeDate == "ETAT_ANNULE" && ($tRendezVous->getDateRdv("Y-m-d")<=date("Y-m-d")
					|| ($etat!=Atexo_Config::getParameter("ETAT_ANNULE_ETAB") && $etat!=Atexo_Config::getParameter("ETAT_ANNULE")))) {
					$diffDate = true;
					break;
				}
				if($typeDate == "ETAT_NON_HONORE" && ($tRendezVous->getDateRdv("Y-m-d")>date("Y-m-d")
					|| ($etat!=Atexo_Config::getParameter("ETAT_NON_HONORE_ETAB") && $etat!=Atexo_Config::getParameter("ETAT_NON_HONORE")))) {
					$diffDate = true;
					break;
				}
				$listeRdv[] = $tRendezVous;
			}
		}
		if($diffDate) {
			$this->paneldeleteNOk->style = "display:block";
			$criteriaVo = $this->getViewState("CriteriaVo");
			$this->fillRepeaterWithDataForSearchResult($criteriaVo);
			$this->rdvPanel->render($param->NewWriter);
			return;
		}

		foreach($this->listeAnnulationLangues->getItems() as $item) {
			$motifAnnule[$item->langLibelleAnnulation->Value]=$item->motifAnnulation->SafeText;
		}

		foreach($listeRdv as $tRendezVous) {
			$dateAnnule = date("Y-m-d");
			$idAgentAnnule = Atexo_User_CurrentUser::getIdAgentConnected();
			$this->setItemRdv($tRendezVous,serialize($motifAnnule),$dateAnnule,$idAgentAnnule,$etat);
			if($tRendezVous->getTCitoyen()->getTelephone()!="" && $envoieSms) {
				$this->envoyerSms($tRendezVous);
			}
		}
		$this->paneldeleteOk->style="display:block";
		$criteriaVo=$this->getViewState("CriteriaVo");
		$this->fillRepeaterWithDataForSearchResult($criteriaVo);
		$this->rdvPanel->render($param->NewWriter);
	}

	/**
	 *
	 * Annuler une rendez-vous séléctionné
	 */
	public function onAnnuleItemClick($sender,$param) {
		$tRendezVousQuery = new TRendezVousQuery();
		$idRendezVous = $this->rendezVousToCancelHidden->Value;
		$tRendezVous = $tRendezVousQuery->getRendezVousById($idRendezVous);

		foreach($this->listeAnnulationLangues->getItems() as $item) {
			$motifAnnule[$item->langLibelleAnnulation->Value]=$item->motifAnnulation->SafeText;
		}
		$envoieSms = $this->envoiSMS->Checked;
		if(!$tRendezVous->getTCitoyen() || $tRendezVous->getTCitoyen()->getTelephone()=="") {
			$envoieSms=false;
		}
		$dateAnnule = date("Y-m-d");
		$idAgentAnnule = Atexo_User_CurrentUser::getIdAgentConnected();
        if ($this->annuleCitoyen->checked) {
            $etat = Atexo_Config::getParameter("ETAT_ANNULE");
        }
        if ($this->annuleEtablissement->checked) {
            $etat = Atexo_Config::getParameter("ETAT_ANNULE_ETAB");
        }
        if ($this->nonHonoreCitoyen->checked) {
			$envoieSms=false;
            $etat = Atexo_Config::getParameter("ETAT_NON_HONORE");
        }
        if ($this->nonHonoreEtablissement->checked) {
			$envoieSms=false;
            $etat = Atexo_Config::getParameter("ETAT_NON_HONORE_ETAB");
        }
		$this->setItemRdv($tRendezVous,serialize($motifAnnule),$dateAnnule,$idAgentAnnule,$etat);
		if($envoieSms) {
			$this->envoyerSms($tRendezVous);
		}
		$this->paneldeleteOk->style="display:block";
		$criteriaVo=$this->getViewState("CriteriaVo");
		$this->fillRepeaterWithDataForSearchResult($criteriaVo);
		$this->rdvPanel->render($param->NewWriter);
	}

	/**
	 * @param $rdv
	 * Envoyer un SMS contenant les informations du rendez-vous
	 */
		private function envoyerSms($rdv)
	{
		$envoiSms = new Atexo_SMS();

		$envoiSms->setDestination($rdv->getTCitoyen()->getTelephone());
		//$envoiSms->setDcs("19");
		$message = str_replace('[DATE]',$rdv->getDateRdv("d/m/y H:i"),Prado::localize('SMS_ANNULATION_DE_VOTRE_DEMANDE_DE_RENDEZ_VOUS'));
		$message = '</br>'.str_replace('[MOTIF]', $rdv->getMotifAnnulation(),$message);

		$envoiSms->setMessage($message);
		//$envoiSms->setLogin(Atexo_Config::getParameter('LOGIN_SERVICE_SMS'));
		//$envoiSms->setPass(Atexo_Config::getParameter('PASS_SERVICE_SMS'));
		$envoiSms->setShortcode(Prado::localize('CODE_ENVOI_SMS'));
		$envoiSms->setUrl(Atexo_Config::getParameter('URL_SERVICE_SMS'));

		try {
			$envoiSms->envoyerSms();
		}catch (Exception $e){
			$logger = Atexo_LoggerManager::getLogger("rdvLogErreur");
			$logger->error($e->getMessage());
			Atexo_Utils_GestionException::catchException($e);
		}
	}

	/**
	 * @param $rdv
	 * Envoyer un mail contenant les informations du rendez-vous
	 */
	private function envoiMail($rdv)
	{
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$mail = new Atexo_Utils_Mail();
		$pfUrl = $this->getPfUrl();

		$objet = prado::localize('ANNULATION_DE_VOTRE_DEMANDE_DE_RENDEZ_VOUS');

		$corpsMessage ="<div";
		if($lang=="ar") {
			$corpsMessage .= " style='direction: rtl;'";
		}
		$corpsMessage .=">";
		$corpsMessage .= "<b>".prado::localize('MSG_ANNULATION_DE_VOTRE_DEMANDE_DE_RENDEZ_VOUS')."</b><br><br>";
		$etab = $rdv->getTEtablissement();
		$prestation = $rdv->getTPrestation();
		$typePrestation = $prestation->getTTypePrestation();
		$corpsMessage .= "<ul><li>".prado::localize('NOM')." : ".$rdv->getTCitoyen()->getNom()." ".$rdv->getTCitoyen()->getPrenom()."</li>";
		//$corpsMessage .= "<li>".prado::localize('IDENTIFIANT')." : ".$rdv->getTCitoyen()->getIdentifiant()."</li>";
		if($rdv->getTPrestation()->getVisioconference() == 0){
			$corpsMessage .= "<li>".prado::localize('ETABLISSEMENT')." : ".$etab->getDenominationEtablissementTraduit($lang)."</li>";
			$corpsMessage .= "<li>".prado::localize('ADRESSE')." : ".$etab->getAdresseEtablissementTraduit($lang)."</li>";
		//	$corpsMessage .= "<li>".prado::localize('TELEPHONE_POUR_PRISE_RDV')." : ".$etab->getTelephoneRdv()."</li>";
		}
		$corpsMessage .= "<li>".prado::localize('NIVEAU1')." : ".$typePrestation->getLibelleTypePrestationTraduit($lang)."</li>";
		$corpsMessage .= "<li>".prado::localize('NIVEAU2')." : ".$prestation->getLibellePrestationTraduit($lang)."</li>";

		/*if($rdv->getIdAgentRessource()!=null && $rdv->getIdAgentRessource()!="") {
			$tAgentQuery = new TAgentQuery();
			$tAgent = $tAgentQuery->getAgentById($rdv->getIdAgentRessource());
			$corpsMessage .= "<li>".prado::localize('NIVEAU3')." : ".$tAgent->getNomPrenomUtilisateurTraduit($lang)."</li>";
		}*/

		$corpsMessage .= "<li>".prado::localize('HORAIRES')." : ".Prado::localize('LE')." ".$rdv->getDateRdv("d/m/Y")." ".Prado::localize('A')." ".$rdv->getDateRdv("H:i")."</li>";
		$corpsMessage .= "<li>".prado::localize('LE_CODE_DE_CONFIRMATION')." : ".$rdv->getCodeRdv()."</li>";
		
		$corpsMessage .= "<li>".prado::localize('MOTIF_ANNULATION')." : ".unserialize($rdv->getMotifAnnulation())[$lang]."</li>";
		$corpsMessage .= "</ul><br><br>";

		$corpsMessage .= prado::localize('MSG_CONTACTER_NOUS')."<br>";
		$corpsMessage .= " - ".prado::localize('MSG_CONTACTER_NOUS_WEB')." <a href='". $pfUrl ."'>". $pfUrl ."</a><br>";
		if($rdv->getTPrestation()->getVisioconference() == 0){
	//	$corpsMessage .= " - ".prado::localize('MSG_CONTACTER_NOUS_TEL')." ".$etab->getTelephoneRdv()."<br><br></div>";
		}
		try {
			$mail->envoyerMail(Atexo_Config::getParameter('PF_MAIL_FROM'),$rdv->getTCitoyen()->getMail(),$objet,$corpsMessage);
		}catch (Exception $e){
			$logger = Atexo_LoggerManager::getLogger("rdvLogErreur");
		    $logger->error($e->getMessage());
			Atexo_Utils_GestionException::catchException($e);
		}
	}

	/**
	 * 
	 * Exporter les données sous format excel
	 */
	public function exporterExcel() {

		require_once("Spreadsheet/Excel/Writer.php");
		//Initialisation du fichier excel
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->setVersion(8);
 	 
		//Style des cellules d'entete
		$hdr_format = &$workbook->addFormat();
		$hdr_format->setColor('black');
		$hdr_format->setBold(1);
		$hdr_format->setPattern(1);
		$hdr_format->setFgColor(34);
		$hdr_format->setAlign('center');
		$hdr_format->setSize(10);
		$hdr_format->setBorder(1);
		$hdr_format->setOutLine();
		//Style des cellules
		$corps_format = &$workbook->addFormat();
		$corps_format->setColor('black');
		$corps_format->setPattern(1);
		$corps_format->setFgColor(1);
		$corps_format->setSize(10);
		$corps_format->setBorder(1);
		$corps_format->setAlign('center');
		//Creation de l'entete du fichier excel
		$etiq = &$workbook->addWorkSheet(Prado::localize('RAPPORT_RENDEZ_VOUS'));
		$etiq->setInputEncoding('utf-8');
		for ($i=0 ;$i<8;$i++){
			$etiq->setColumn(0, $i, 30);
		}
//		$dateCreation = Prado::localize('DATE_CREATION');
//		$heureCreation = Prado::localize('HEURE_CREATION');
	//	$createur = Prado::localize('CREATEUR');
//		$profil = Prado::localize('PROFIL_UTILISATEUR');
       // $codeRdv = Prado::localize('LE_CODE_DE_CONFIRMATION');
		$dateRdv = Prado::localize('DATE_RDV');
		$heureDebut = Prado::localize('TIME_DEB_RDV');
//		$heureFin = Prado::localize('TIME_FIN_RDV');
//		$etab = Prado::localize('ETABLISSEMENT');
		$type = Prado::localize('TYPE_PRESTATION');
		$prestation = Prado::localize('PRESTATION');
		$ressource = Prado::localize('RESSOURCES');
//		$etat = Prado::localize('ETAT_RDV');
//		$annuleLe = Prado::localize('ANNULE_LE');
//		$annulePar = Prado::localize('ANNULE_PAR');
//		$objetRdv = Prado::localize('PRESTATION_ASSOCIEE');
		$nomClient = Prado::localize('CITOYEN');
  //      $dateNaissance = Prado::localize('DATE_NAISSANCE');
    //    $emailClient = Prado::localize('EMAIL');
      //  $phoneClient = Prado::localize('TELEPHONE');
       // $identifiant = Prado::localize('TEXT_IDENTIFIANT');
        $administration = Prado::localize('RAISON_SOCIAL');
      //  $champSupp1 = Prado::localize('CHAMP_SUPP1');
       // $champSupp2 = Prado::localize('CHAMP_SUPP2');
        //$champSupp3 = Prado::localize('CHAMP_SUPP3');
	//	$etiq->writeString(0, 0,$dateCreation, $hdr_format);
	//	$etiq->writeString(0, 1,$heureCreation, $hdr_format);
//		$etiq->writeString(0, 2,$createur, $hdr_format);
	//	$etiq->writeString(0, 3,$profil, $hdr_format);
  //      $etiq->writeString(0, 4,$codeRdv, $hdr_format);
		$etiq->writeString(0, 0,$dateRdv, $hdr_format);
		$etiq->writeString(0, 1,$heureDebut, $hdr_format);
//		$etiq->writeString(0, 7,$heureFin, $hdr_format);
//		$etiq->writeString(0, 8,$etab, $hdr_format);
		$etiq->writeString(0, 4,$type, $hdr_format);
		$etiq->writeString(0, 5,$prestation, $hdr_format);
//		$etiq->writeString(0, 11,$objetRdv, $hdr_format);
		$etiq->writeString(0, 6,$ressource, $hdr_format);
//		$etiq->writeString(0, 13,$etat, $hdr_format);
//		$etiq->writeString(0, 14,$annuleLe, $hdr_format);
//		$etiq->writeString(0, 15,$annulePar, $hdr_format);

		$etiq->writeString(0, 2,$nomClient, $hdr_format);
		//$etiq->writeString(0, 16,$dateNaissance, $hdr_format);
//		$etiq->writeString(0, 17,$emailClient, $hdr_format);
//		$etiq->writeString(0, 18,$phoneClient, $hdr_format);
	//	$etiq->writeString(0, 2,$identifiant, $hdr_format);
		$etiq->writeString(0, 3,$administration, $hdr_format);
//		$etiq->writeString(0, 21,$champSupp1, $hdr_format);
//		$etiq->writeString(0, 22,$champSupp2, $hdr_format);
//		$etiq->writeString(0, 23,$champSupp3, $hdr_format);
		$c=1;
		$criteriaVo = $this->getCriteria();
		$dataRdv = TRendezVousPeer::getRdvByCriteres($criteriaVo);
		//print_r($dataRdv);exit;
		foreach($dataRdv as $data) {
			array_shift($data['CHAMP_SUPP_PRESTA']);
			if(count($data['CHAMP_SUPP_PRESTA']) > 0){
				$objet = implode(' / ',$data['CHAMP_SUPP_PRESTA']);
			}
			else{
				$objet = '';
			}
			$dateHeure = explode(" ",$data["DATE_CREATION_FR"]);
	//		$etiq->write($c,0,$dateHeure[0],$corps_format);
	//		$etiq->write($c,1,$dateHeure[1],$corps_format);
		/*	switch ($data["PROFIL_UTILISATEUR"]) {
				case Prado::localize('CITOYEN') : $etiq->write($c,2,$data["NOM"]." ".$data["PRENOM"],$corps_format);
				break;
				case Prado::localize('OPERATEUR') : $etiq->write($c,2,$data["NOM_ACCUEIL"]." ".$data["PRENOM_ACCUEIL"],$corps_format);
				break;
			} */
	//		$etiq->writeString($c,3,$data["PROFIL_UTILISATEUR"],$corps_format);
          //  $etiq->writeString($c,4,$data["CODE_RDV"],$corps_format);
			$etiq->writeString($c,0,$data["DATE_DEB_RDV"],$corps_format);
			$etiq->writeString($c,1,$data["TIME_DEB_RDV"],$corps_format);
	//		$etiq->writeString($c,7,$data["TIME_FIN_RDV"],$corps_format);
	//		$etiq->writeString($c,8,$data["DENOMINATION_ETABLISSEMENT_ATTACHE"],$corps_format);
			$etiq->writeString($c,4,$data["TYPE_PRESTATION"],$corps_format);
			$etiq->writeString($c,5,$data["PRESTATION"],$corps_format);
	//		$etiq->writeString($c,11,$objet,$corps_format);
			$etiq->writeString($c,6,$data["NOM_RESSOURCE"]." ".$data["PRENOM_RESSOURCE"],$corps_format);
	//		$etiq->writeString($c,13,$data["ETAT_RDV"],$corps_format);
	//		$etiq->writeString($c,14,$data["DATE_ANNULATION_FR"],$corps_format);
	//		$etiq->writeString($c,15,$data["ANNULE_PAR"],$corps_format);
			$etiq->writeString($c,2,$data["NOM"]." ".$data["PRENOM"],$corps_format);
			//$etiq->writeString($c,16,$data["DATE_NAISSANCE"],$corps_format);
	//		$etiq->writeString($c,17,$data["MAIL"],$corps_format);
	//		$etiq->writeString($c,18,$data["TELEPHONE"],$corps_format);
	//		$etiq->writeString($c,2,$data["IDENTIFIANT"], $corps_format);
			$etiq->writeString($c,3,$data["RAISON_SOCIAL"], $corps_format);
	//		$etiq->writeString($c,21,$data["TEXT1"], $corps_format);
	//		$etiq->writeString($c,22,$data["TEXT2"], $corps_format);
	//		$etiq->writeString($c,23,$data["TEXT3"], $corps_format);
			$c++;
		}
		$nom_file = "ListeRdv.xls";
		$workbook->send($nom_file);
		$workbook->close();
		exit;
	}

	/**
	 * 
	 * recuperer les critéres de recherche
	 */
	public function getCriteria() {
		$nomCitoyen=$this->nomCitoyen->SafeText;
		$codeRdv=$this->codeRdv->SafeText;
		$dateDu=$this->datedebut->SafeText;
		$dateAu=$this->datefin->SafeText;
		$dateCreation=$this->dateCreat->SafeText;
		$criteriaVo = new Atexo_RendezVous_CriteriaVo();
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$criteriaVo->setLang($lang);

		$idOrg = Atexo_User_CurrentUser::getIdOrganisationGere();
		if($idOrg>0) {
			$criteriaVo->setIdOrganisationAttache($idOrg);	
		}

		if($nomCitoyen!=null) {
			$criteriaVo->setNomCitoyen($nomCitoyen);
		}

		if($codeRdv!=null) {
			$criteriaVo->setCodeRdv($codeRdv);
		}

		if($dateDu!=null) {
			$criteriaVo->setDateDu($dateDu);
		}

		if($dateAu!=null) {
			$criteriaVo->setDateAu($dateAu);
		}

		if($dateCreation!=null) {
			$criteriaVo->setDateCreation($dateCreation);
		}

		if($this->listeEtablissement->getSelectedValue()>0) {
			$criteriaVo->setIdEtablissementAttache($this->listeEtablissement->getSelectedValue());
		}
		$typePrestation = $this->getViewState("typePrestation");
		if($this->listeTypePrestation->getSelectedValue()>0) {

			if($typePrestation==Atexo_Config::getParameter("PRESTATION_SAISIE_LIBRE")) {
				$criteriaVo->setIdTypePrestation($this->listeTypePrestation->getSelectedValue());
			}
			else {
				$criteriaVo->setIdRefTypePrestation($this->listeTypePrestation->getSelectedValue());
			}
		}
		if($this->listePrestation->getSelectedValue()>0) {
			if($typePrestation==Atexo_Config::getParameter("PRESTATION_SAISIE_LIBRE")) {
				$criteriaVo->setIdPrestation($this->listePrestation->getSelectedValue());
			}
			else {
				$criteriaVo->setIdRefPrestation($this->listePrestation->getSelectedValue());
			}
		}
		if($this->listeRessource->getSelectedValue() > 0) {
			$criteriaVo->setIdRessource($this->listeRessource->getSelectedValue());
		}

		if($this->listeEtat->getSelectedValue()>0) {
			$criteriaVo->setIdEtat($this->listeEtat->getSelectedValue()-1);
		}

		if($this->listeModePriseRdv->getSelectedValue()>0) {
			$criteriaVo->setIdModePriseRdv($this->listeModePriseRdv->getSelectedValue()-1);
		}

		return $criteriaVo ;
	}
	protected function isConfirmationRdvVisible($item) {
		return  $item['ID_ETAT_RDV']==Atexo_Config::getParameter('ETAT_EN_ATTENTE')
				&& Atexo_Utils_Util::frnDate2iso($item['DATE_DEB_RDV']) <= Atexo_Utils_Util::frnDate2iso(date('d/m/Y'));
	}

	/**
	 * @param $data
	 * récuperer repeater libelle de la prestation
	 */
	public function getListeAnnulationParLangues($data=null) {
		if(count($data) > 0) {
            $this->setListeAnnulationParLangues($data);
            $this->setListeAnnulationGrpParLangues($data);
		} else {
			//récupérer les langues
			$langues[]= explode(",", Atexo_Config::getParameter("LANGUES_ACTIVES"));

			$data = array();
			$index=0;
			foreach($langues[0] as $lan){
				$data[$index]['annulationLang'] = Prado::localize('MOTIF');
				$data[$index]['lang'] = '('.Prado::localize('LANG_'.strtoupper($lan)).')';
				$data[$index]['motifAnnulation'] = '';
				$data[$index]['langLibelleAnnulation'] = $lan;
				$index++;
			}
			$this->setListeAnnulationParLangues($data);
            $this->setListeAnnulationGrpParLangues($data);
		}
	}

    /**
     * @param $data
     * remplir repeater libelle de la prestation
     */
    public function setListeAnnulationParLangues($data) {
        $this->listeAnnulationLangues->dataSource = $data;
        $this->listeAnnulationLangues->dataBind();
        $index = 0;
        foreach ($this->listeAnnulationLangues->getItems() as $item) {
            $item->annulationLang->Text = $data[$index]['annulationLang'];
            $item->lang->Text = $data[$index]['lang'];
            $item->langLibelleAnnulation->Value = $data[$index]['langLibelleAnnulation'];
            $index++;
        }
    }

    /**
     * @param $data
     * remplir repeater motif annulation
     */
    public function setListeAnnulationGrpParLangues($data) {
        $this->listeAnnulationGrpLangues->dataSource = $data;
        $this->listeAnnulationGrpLangues->dataBind();
        $index = 0;
        foreach ($this->listeAnnulationGrpLangues->getItems() as $item) {
            $item->annulationLang->Text = $data[$index]['annulationLang'];
            $item->lang->Text = $data[$index]['lang'];
            $item->langLibelleAnnulation->Value = $data[$index]['langLibelleAnnulation'];
            $index++;
        }
    }

	/**
	 * Remplir la liste des regions
	 */
	public function loadEntite1() {
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$entiteGestion = new Atexo_Entite_Gestion();
		$this->entite1->DataSource = $entiteGestion->getAllEntite(1, $lang, null, Prado::localize('ENTITE_1'));
		$this->entite1->DataBind();
	}

	/**
	 * Remplir la liste des provinces
	 */
	public function loadEntite2($sender = null) {
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$entiteGestion = new Atexo_Entite_Gestion();
		$this->entite2->DataSource = $entiteGestion->getAllEntite(2, $lang, $this->entite1->SelectedValue, Prado::localize('ENTITE_2'));
		$this->entite2->DataBind();
		if($sender) {
			$this->loadEntite3();
			$this->loadEtablissement($this->entite1->getSelectedValue());
		}
	}

	/**
	 * Remplir la liste des communes
	 */
	public function loadEntite3($sender = null) {
		$entiteGestion = new Atexo_Entite_Gestion();
		$idEntite = null;
		$lang = Atexo_User_CurrentUser::readFromSession("lang");

		if($this->entite2->SelectedValue) {
			$idEntite = $this->entite2->SelectedValue;
		}elseif($this->entite1->SelectedValue){
			$idEntite = $entiteGestion->getAllIdChildEntite($this->entite1->SelectedValue);
		}

		$this->entite3->DataSource = $entiteGestion->getAllEntite(3, $lang, $idEntite, Prado::localize('ENTITE_3'));
		$this->entite3->DataBind();
		if($sender) {
			$this->loadEtablissement($this->entite1->getSelectedValue(), $this->entite2->getSelectedValue());
		}
	}


	public function getRessourcesLibres($sender, $param){
		$data = $param->CallbackParameter;
		$this->panelAffectationFail->Visible = false;
		$gestionRdv = new Atexo_RendezVous_Gestion();
		$date = Atexo_Utils_Util::frnDate2iso($data->date);
		
		$tAgentQuery = new TAgentQuery();
		$ressource = $tAgentQuery->getAgentById($data->ressource);

		$criteria = new Atexo_Agent_CriteriaVo();
		$criteria->setIdPrestation($data->prestation);
		$criteria->setLang(Atexo_User_CurrentUser::readFromSession("lang"));
		
		if($ressource->getCodeUtilisateur()>0) {
			$criteria->setCodeRessource($ressource->getCodeUtilisateurTraduit(Atexo_User_CurrentUser::readFromSession("lang")));
		}

		$ressourcesByIdPrestation = TAgentPeer::getRessourceByCriteres($criteria);

		$ressources = $gestionRdv->getRessourceDisponible($data->prestation, $date, $data->debut, $data->fin, null, false);

		$this->setViewState('idPrestation', $data->prestation);
		$this->setViewState('dateRdv', $date);
		$this->setViewState('heureDeb', $data->debut);
		$this->setViewState('heureFin', $data->fin);

		$ressourcesDispo = array(0 => Prado::localize('SELECTIONNEZ'));

		foreach ($ressourcesByIdPrestation as $ressource) {
			if(in_array($ressource['ID_AGENT'], $ressources)){
				$ressourcesDispo[$ressource['ID_AGENT']] = $ressource['NOM_UTILISATEUR'].' '.$ressource['PRENOM_UTILISATEUR'];
			}
		}

		$this->listeRessourceLibre->DataSource = $ressourcesDispo;
		$this->listeRessourceLibre->DataBind();
		$this->script->Text = "<script>J('.chosen-select').trigger('chosen:updated');</script>";
	}

	public function reaffecterRdv(){
		$idRdv = $this->idRdv->Value;

		$idPrestation = $this->getViewState('idPrestation');
		$dateRdv = $this->getViewState('dateRdv');
		$heureDeb = $this->getViewState('heureDeb');
		$heureFin = $this->getViewState('heureFin');
		$idRessourceChoisi = $this->listeRessourceLibre->SelectedValue;
		if(self::isRdvValide($idPrestation, $idRessourceChoisi, $dateRdv, $heureDeb, $heureFin)){
			$this->panelAffectationFail->Visible = false;
			$rdv = TRendezVousPeer::retrieveByPk($idRdv);
			$rdv->setIdChefRessource(Atexo_User_CurrentUser::getIdAgent());
			$rdv->setIdAgentRessource($idRessourceChoisi);
			$rdv->save();
			$this->suggestNames();
		}
		else{
			$this->panelAffectationFail->Visible = true;
		}
		
		
	}


	private function isRdvValide($idPrestation, $idRessourceChoisi, $dateRdv, $heureDeb, $heureFin) {

        if($dateRdv<date("Y-m-d")) {
            return false;
        }

        $tRdvGestion = new Atexo_RendezVous_Gestion();
        if($tRdvGestion->isRdvDisponible($dateRdv, $heureDeb, $idPrestation, $idRessourceChoisi)) {
            return array($idRessourceChoisi);
        }
        return false;
    }
 public function imprimerRdv($sender, $param){
		$idRdv = $sender->attributes->rdv;

    	$lang = Atexo_User_CurrentUser::readFromSession("lang");
    	$idOrg = Atexo_User_UserVo::getCurrentOrganism();
    	$tTraductionLibelleQuery = new TTraductionLibelleQuery();
    	$rdv = TRendezVousPeer::retrieveByPk($idRdv);

    	$etab = $rdv->getTEtablissement();
    	$entite = $etab->getTEntite();
		$tOrganisation = $etab->getTOrganisation();
		$prestation = $rdv->getTPrestation();
		$typePrestation = $prestation->getTTypePrestation();

		$idRessource = $rdv->getIdAgentRessource();
		$ressource = TAgentPeer::retrieveByPk($idRessource);
		$codeRessource = '';
		if($ressource->getActif() != 0){
			$codeRessource = $ressource->getCodeUtilisateurTraduit($lang);
			if($codeRessource == ''){
				$codeRessource = $ressource->getNomPrenomUtilisateurTraduit($lang);
			}
		}



		$champSupp1 = $prestation->getIdChampsSupp1();
		$champSupp2 = $prestation->getIdChampsSupp2();

		$libelleChampSupp1 = $tTraductionLibelleQuery->getLibelle($champSupp1, $lang);
		$libelleChampSupp2 = $tTraductionLibelleQuery->getLibelle($champSupp2, $lang);

		$champSuppValue = $rdv->getChampSuppPresta();

		$champsSupp = json_decode($champSuppValue)->$lang;
		
		$libelleChampSupp1 = '';
		$champSupp1 = '';
		$libelleChampSupp2 = '';
		$champSupp2 = '';
		$libelleChampSupp3 = '';
		$champSupp3 = '';

		$champSupp1Exist = false;
		$champSupp2Exist = false;
		$champSupp3Exist = false;

		if(isset($champsSupp[0])){
			$arr = explode(' : ', $champsSupp[0]);
			$libelleChampSupp1 = $arr[0].' : ';
			$champSupp1 = $arr[1];
			$champSupp1Exist = true;
		}
		
		if(isset($champsSupp[1])){
			$arr = explode(' : ', $champsSupp[1]);
			$libelleChampSupp2 = $arr[0].' : ';
			$champSupp2 = $arr[1];
			$champSupp2Exist = true;
		}

		if(isset($champsSupp[2])){
			$arr = explode(' : ', $champsSupp[2]);
			$libelleChampSupp3 = $arr[0].' : ';
			$champSupp3 = $arr[1];
			$champSupp3Exist = true;
		}

		
if($prestation->getIdPrestation()===1636) $path = "ressources/word/recap_rdv_52_ar"  . ".docx";
 else       $path = "ressources/word/recap_rdv_". $idOrg . "_" . $lang . ".docx";
        $vendorpath = 'protected/library/vendors/vendor';
        require_once $vendorpath.'/autoload.php';
        if(!file_exists($path)){
				if($prestation->getIdPrestation()===1636) $path = "ressources/word/recap_rdv_52" . $lang . ".docx";
        	else $path = "ressources/word/recap_rdv_" . $lang . ".docx";
        } 

		$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($path);
        $templateProcessor->setValue('etablissement', $etab->getDenominationEtablissementTraduit($lang));
       	$templateProcessor->setValue('adresse', $etab->getAdresseEtablissementTraduit($lang));
       	$templateProcessor->setValue('type_prestation', $typePrestation->getLibelleTypePrestationTraduit($lang));
		
		try {
			
				$templateProcessor->setValue('denom', $rdv->getTCitoyen()->getText1());
				$templateProcessor->setValue('type', $rdv->getTCitoyen()->getText2());
				$templateProcessor->setValue('nbr', $rdv->getTCitoyen()->getText3());
			
			 $templateProcessor->setValue('rc', $champSupp1);
				$templateProcessor->setValue('nom_citoyen', $rdv->getTCitoyen()->getNom());
			$templateProcessor->setValue('prenom_citoyen', $rdv->getTCitoyen()->getPrenom());
			$templateProcessor->setValue('identifiant', $rdv->getTCitoyen()->getIdentifiant());
			$templateProcessor->setValue('raison_social', $rdv->getTCitoyen()->getRaisonSocial());
		}
		catch(Exception $e) {
			
		}
		
       	if($champSupp1Exist){
       		$templateProcessor->setValue('prestation', $prestation->getLibellePrestationTraduit($lang).'</w:t><w:br/><w:t>');
       	}else{
       		$templateProcessor->setValue('prestation', $prestation->getLibellePrestationTraduit($lang));
       	}

       	$templateProcessor->setValue('libelle_champ_supp_1', $libelleChampSupp1);
       	if($champSupp2Exist){
       		$templateProcessor->setValue('champ_supp_1', $champSupp1.'</w:t><w:br/><w:t>');
       	}else{
       		$templateProcessor->setValue('champ_supp_1', $champSupp1);
       	}

       	$templateProcessor->setValue('libelle_champ_supp_2', $libelleChampSupp2);
       	if($champSupp3Exist){
       		$templateProcessor->setValue('champ_supp_2', $champSupp2.'</w:t><w:br/><w:t>');
       	}else{
       		$templateProcessor->setValue('champ_supp_2', $champSupp2);
       	}
       	
       	$templateProcessor->setValue('libelle_champ_supp_3', $libelleChampSupp3);
       	$templateProcessor->setValue('champ_supp_3', $champSupp3);
       	
		
		$templateProcessor->setValue('code_ressource', $codeRessource);
       	$templateProcessor->setValue('horaire', $rdv->getDateRdv("d/m/Y"));

       	$templateProcessor->setValue('libelle_etablissement', Prado::localize('ETABLISSEMENT').' : ');
       	$templateProcessor->setValue('libelle_nature_rdv', Prado::localize('NIVEAU1').' : ');
       	$templateProcessor->setValue('libelle_prestation', Prado::localize('NIVEAU2').' : ');
       	$templateProcessor->setValue('libelle_entite', Prado::localize('NIVEAU3').' : ');
		

	
       	$filename = 'recap_rdv_' . $rdv->getCodeRdv() . '.docx';

	   header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessing‌​ml.document");// you should look for the real header that you need if it's not Word 2007!!!
	   header('Content-Disposition: attachment; filename=' . $filename);

	   $templateProcessor->saveAs("php://output");
       exit;
        
        
    }



    public function downloadPieceJointe($sender) {
		$idFichier = $sender->CommandParameter;
		Atexo_DownloadFile::downloadFiles($idFichier);
	}

	public function showModalPj($sender, $param){
		$data = $param->CallbackParameter;
		$idRdv = $data->idRdv;

		$tRdv = new TRendezVousQuery();
		$rdv = $tRdv->findPk($idRdv);
		if($rdv){
			$blobs = $rdv->getTBlobRdvs();
			$dataPj = array();
			$i = 0;
			foreach($blobs as $blob){
				$dataPj[$i]["libelle"] = $blob->getTBlob()->getNomBlob();
				$dataPj[$i]["idBlob"] = $blob->getIdBlob();
				$i++;
			}
			$this->listePj->DataSource = $dataPj;
			$this->listePj->DataBind();
		}
		
	}

	public function partagerRecap($sender, $param){
        $data = $param->CallbackParameter;
        $idRdv = $data->idRdv;

        $tRdv = new TRendezVousQuery();
        $rdv = $tRdv->findPk($idRdv);

        if($rdv) {
            $rdv->setPartageRecap(1);
            $rdv->save();

            $this->panelConfirmationPartageRecap->style = "display:block";
        }
    }
}
