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

class RapportDelaiObtention extends AtexoPage {

	private $_lang;

	public function onInit()
	{
		$this->Master->setCalledFrom("admin");
		Atexo_Utils_Languages::setLanguageCatalogue($this->Master->getCalledFrom());
	}

	public function onLoad()
	{
		$this->_lang = Atexo_User_CurrentUser::readFromSession("lang");
		if(!Atexo_User_CurrentUser::hasHabilitation('RapportDelaiObtention')) {
			$this->response->redirect("?page=administration.AccueilAdministrateurAuthentifie");
		}

		if(!$this->isPostBack) {
			$this->loadEntite();
			$this->loadEntite2();
			$this->loadEntite3();
			$this->loadEtablissement();
			$adminEtab = Atexo_User_CurrentUser::isAdminEtab() || Atexo_User_CurrentUser::isAdminOrgWithEtab();
			if($adminEtab) {
				$idEtablissement = Atexo_User_CurrentUser::getIdEtablissementGere();
				$this->listeEtablissement->setSelectedValue($idEtablissement);
			}
			$this->loadTypePrestation();
			$util = new Atexo_Utils_Util();
			$this->datedebut->Text = $util->iso2frnDate($util->addMois(date("Y-m-d"),-1));
			$this->datefin->Text = date("d/m/Y");
			$this->suggestNames();
		}
	}

	public function loadEntite() {
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$entiteGestion = new Atexo_Entite_Gestion();
		$this->entite1->DataSource = $entiteGestion->getAllEntite(1,$lang,null,Prado::localize('ENTITE_1'));
		$this->entite1->DataBind();
	}

	public function loadEntite2($sender = null) {
		$entiteGestion = new Atexo_Entite_Gestion();
		$this->entite2->DataSource = $entiteGestion->getAllEntite(2, $this->_lang, $this->entite1->SelectedValue, Prado::localize('ENTITE_2'));
		$this->entite2->DataBind();
		if($sender) {
			$this->loadEntite3();
			$this->loadEtablissement($this->entite1->getSelectedValue());
		}
	}

	public function loadEntite3($sender = null) {
		$entiteGestion = new Atexo_Entite_Gestion();
		$idEntite = null;

		if($this->entite2->SelectedValue) {
			$idEntite = $this->entite2->SelectedValue;
		}elseif($this->entite1->SelectedValue){
			$idEntite = $entiteGestion->getAllIdChildEntite($this->entite1->SelectedValue);
		}

		$this->entite3->DataSource = $entiteGestion->getAllEntite(3, $this->_lang, $idEntite, Prado::localize('ENTITE_3'));
		$this->entite3->DataBind();
		if($sender) {
			$this->loadEtablissement(null, $this->entite2->getSelectedValue());
		}
	}

	public function loadEtablissementByEntite() {
		$this->loadEtablissement();
	}

	public function loadEtablissement($idEntite1 = null, $idsEntite2 = null) {
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		if($idEntite1) {
			$entiteGestion = new Atexo_Entite_Gestion();
			$idsCommune = $entiteGestion->getAllIdChildEntite ($idEntite1);
		}
		if($idsEntite2) {
			$entiteGestion = new Atexo_Entite_Gestion();
			$idsCommune = $entiteGestion->getAllIdChildEntite ($idsEntite2);
		}
		if($this->entite3->getSelectedValue ()) {
			$idsCommune = array($this->entite3->getSelectedValue ());
		}
		$etablissementGestion = new Atexo_Etablissement_Gestion();
		$this->listeEtablissement->DataSource = $etablissementGestion->getEtablissementByIdProvinceIdOrganisation($lang,
		Atexo_User_CurrentUser::getIdOrganisationGere(),null,Prado::localize('ETABLISSEMENT'),true, false, $idsCommune);
		$this->listeEtablissement->DataBind();
		if($_SESSION["typePrestation"]== Atexo_Config::getParameter("PRESTATION_REFERENTIEL")) {
			$this->loadRefPrestation();
		}
	}

	public function loadTypePrestation() {
		$lang = Atexo_User_CurrentUser::readFromSession("lang");

		$idEtablissement = $this->listeEtablissement->getSelectedValue();
		
		if($_SESSION["typePrestation"]== Atexo_Config::getParameter("PRESTATION_REFERENTIEL")) {
			$refTypePrestationGestion = new Atexo_RefTypePrestation_Gestion();
			$this->listeTypePrestation->DataSource = $refTypePrestationGestion->getRefTypePrestationByIdOrg($lang,Atexo_User_CurrentUser::getIdOrganisationGere(),Prado::localize('NIVEAU1'), $idEtablissement);
			$this->listeTypePrestation->DataBind();
			$this->loadRefPrestation();
		} else {
			$typePrestationGestion = new Atexo_TypePrestation_Gestion();
			$this->listeTypePrestation->DataSource = $typePrestationGestion->getTypePrestationByIdEtab($lang,$idEtablissement,Prado::localize('NIVEAU1'));
			$this->listeTypePrestation->DataBind();

			$this->listePrestation->DataSource = array();
			$this->listePrestation->DataBind();
		}
	}

	public function loadPrestation() {
		if($_SESSION["typePrestation"]== Atexo_Config::getParameter("PRESTATION_REFERENTIEL")) {
			$this->loadRefPrestation();
			return;
		}
		$typePrestation = $this->getViewState("typePrestation");
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$prestationGestion = new Atexo_Prestation_Gestion();
		$this->listePrestation->DataSource = $prestationGestion->getPrestationByIdTypePrestation($lang,$this->listeTypePrestation->SelectedValue,$_SESSION["typePrestation"],Prado::localize('NIVEAU2'));
		$this->listePrestation->DataBind();
		$this->listePrestation->Visible = true;
		$this->listeRefPrestation->Visible = false;
	}
	
	public function loadRefPrestation() {
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$idEtablissement = $this->listeEtablissement->getSelectedValue();
		$refPrestationGestion = new Atexo_RefPrestation_Gestion();
		$this->listeRefPrestation->DataSource = $refPrestationGestion->getAllRefPrestation($lang,$this->listeTypePrestation->SelectedValue,Prado::localize('NIVEAU2'),$idEtablissement);
		$this->listeRefPrestation->DataBind();
		$this->listePrestation->Visible = false;
		$this->listeRefPrestation->Visible = true;
	}

	/**
	 * @param $criteriaVo
	 * Remplir repeater des rendez-vous selon les critères de recherche
	 */
	public function fillRepeaterWithDataForSearchResult($criteriaVo) {

		$tDelaiObtentionPeer = new TDelaiObtentionPeer();
		$nombreElement = $tDelaiObtentionPeer->getDelaiObtentionByCriteres($criteriaVo, true);

		$moyenne = TDelaiObtentionPeer::getDelaiObtentionByCriteres($criteriaVo,false,true);
		$util = new Atexo_Utils_Util();
		$this->min->Text = $util->getMontantArronditEspace($moyenne["MIN"]).TDelaiObtentionPeer::getTextMoyenne($moyenne["MIN"]);
		$this->max->Text = $util->getMontantArronditEspace($moyenne["MAX"]).TDelaiObtentionPeer::getTextMoyenne($moyenne["MAX"]);
		$this->moyenne->Text = $util->getMontantArronditEspace($moyenne["MOYENNE"]).TDelaiObtentionPeer::getTextMoyenne($moyenne["MOYENNE"]);

		if ($nombreElement>=1) {
			$this->nombreElement->Text=$nombreElement;
			$this->PagerBottom->setVisible(true);
			$this->PagerTop->setVisible(true);
			$this->panelBottom->setVisible(true);
			$this->panelTop->setVisible(true);
			$this->setViewState("nombreElement",$nombreElement);

			$this->nombrePageTop->Text=ceil($nombreElement/$this->listeRdv->PageSize);
			$this->nombrePageBottom->Text=ceil($nombreElement/$this->listeRdv->PageSize);
			$this->listeRdv->setVirtualItemCount($nombreElement);
			$this->listeRdv->setCurrentPageIndex(0);
			$this->Page->setViewState("CriteriaVo",$criteriaVo);

			$this->populateData($criteriaVo);
			$this->panelMoyenne->Visible = true;
		} else {
			$this->PagerBottom->setVisible(false);
			$this->PagerTop->setVisible(false);
			$this->panelTop->setVisible(false);
			$this->panelBottom->setVisible(false);
			$this->listeRdv->DataSource=array();
			$this->listeRdv->DataBind();
			$this->nombreElement->Text="0";
			$this->panelMoyenne->Visible = false;
		}
	}

	/**
	 * @param $criteriaVo
	 * Peupler les données des rendez-vous
	 */
	public function populateData(Atexo_DelaiObtention_CriteriaVo $criteriaVo)
	{
		$nombreElement = $this->getViewState("nombreElement");
		$offset = $this->listeRdv->CurrentPageIndex * $this->listeRdv->PageSize;
		$limit = $this->listeRdv->PageSize;
		if ($offset + $limit > $nombreElement) {
			$limit = $nombreElement - $offset;
		}
		$criteriaVo->setOffset($offset);
		$criteriaVo->setLimit($limit);
		$dataRdv = TDelaiObtentionPeer::getDelaiObtentionByCriteres($criteriaVo);

		$this->listeRdv->DataSource=$dataRdv;
		$this->listeRdv->DataBind();
	}

	private function getCriteres() {
		$dateDu=$this->datedebut->SafeText;
		$dateAu=$this->datefin->SafeText;
		$criteriaVo = new Atexo_DelaiObtention_CriteriaVo();
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$criteriaVo->setLang($lang);

		if($dateDu!=null) {
			$criteriaVo->setDateDu($dateDu);
		}

		if($dateAu!=null) {
			$criteriaVo->setDateAu($dateAu);
		}
		if($this->entite3->getSelectedValue()>0) {
			$criteriaVo->setIdEntite($this->entite3->getSelectedValue());
		}
		elseif($this->entite2->getSelectedValue()>0) {
			$criteriaVo->setIdEntite($this->entite2->getSelectedValue());
		}
		elseif($this->entite1->getSelectedValue()>0) {
			$criteriaVo->setIdEntite($this->entite1->getSelectedValue());
		}
		if($this->listeTypePrestation->getSelectedValue()>0) {
			if($_SESSION["typePrestation"]== Atexo_Config::getParameter("PRESTATION_REFERENTIEL")) {
				$criteriaVo->setIdRefTypePrestation($this->listeTypePrestation->getSelectedValue());
			}
			else {
				$criteriaVo->setIdTypePrestation($this->listeTypePrestation->getSelectedValue());
			}
		}
		if($this->listePrestation->getSelectedValue()>0) {
			$criteriaVo->setIdPrestation($this->listePrestation->getSelectedValue());
		}
		if($this->listeRefPrestation->getSelectedValue()>0) {
			$criteriaVo->setIdRefPrestation($this->listeRefPrestation->getSelectedValue());
		}

		if($this->listeEtablissement->getSelectedValue()>0) {
			$criteriaVo->setIdEtablissementAttache($this->listeEtablissement->getSelectedValue());
		}
		else {
			$criteriaVo->setIdEtablissementAttache(Atexo_User_CurrentUser::getIdEtablissementGere());
		}
		$idOrg = Atexo_User_CurrentUser::getIdOrganisationGere();
		if($idOrg>0) {
			$criteriaVo->setIdOrganisationAttache($idOrg);
		}
		$criteriaVo->setPrestationReferentiel($_SESSION["typePrestation"]);
		$this->Page->setViewState("CriteriaVo",$criteriaVo);
		return $criteriaVo;
	}

	protected function suggestNames() {
		$criteriaVo = $this->getCriteres();
		$this->fillRepeaterWithDataForSearchResult($criteriaVo);
	}

	public function Trier($sender,$param)
	{
		$champsOrderBy = $sender->CommandParameter;
		$this->setViewState('sortByElement',$champsOrderBy);
		$criteriaVo=$this->Page->getViewState("CriteriaVo");
		$criteriaVo->setSortByElement($champsOrderBy);
		$arraySensTri=$this->Page->getViewState("sensTriArray",array());
		$arraySensTri[$champsOrderBy]=($criteriaVo->getSensOrderBy()=="ASC")? "DESC" : "ASC";
		$criteriaVo->setSensOrderBy($arraySensTri[$champsOrderBy]);
		$this->Page->setViewState("sensTriArray",$arraySensTri);
		$this->Page->setViewState("CriteriaVo",$criteriaVo);
		$this->listeRdv->setCurrentPageIndex(0);
		$this->populateData($criteriaVo);
		$this->rdvPanel->render($param->getNewWriter());
	}

	public function pageChanged($sender,$param)
	{
		$this->listeRdv->CurrentPageIndex =$param->NewPageIndex;
		$this->numPageBottom->Text=$param->NewPageIndex+1;
		$this->numPageTop->Text=$param->NewPageIndex+1;
		$criteriaVo=$this->Page->getViewState("CriteriaVo");
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
			$criteriaVo=$this->Page->getViewState("CriteriaVo");
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
		$criteriaVo=$this->Page->getViewState("CriteriaVo");
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

	public function genererExcel() {

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

		$corps_first = &$workbook->addFormat();
		$corps_first->setColor('black');
		$corps_first->setPattern(1);
		$corps_first->setFgColor(1);
		$corps_first->setSize(10);
		$corps_first->setBorder(1);
		//Creation de l'entete du fichier excel
		$etiq = &$workbook->addWorkSheet(Prado::localize('DELAI_OBTENTION'));
		$etiq->setInputEncoding('utf-8');
		$etiq->setColumn(0, 0, 30);
		$etiq->setColumn(0, 1, 30);
		$etiq->setColumn(0, 2, 30);
		$etiq->setColumn(0, 3, 30);
		$etiq->setColumn(0, 4, 30);
		$etiq->setColumn(0, 5, 30);

		$nomEtab = Prado::localize('ETABLISSEMENT');
		$entite1 = Prado::localize('ENTITE_1');
		$entite2 = Prado::localize('ENTITE_2');
		$typePrestation = Prado::localize('TYPE_PRESTATION');
		$prestation = Prado::localize('PRESTATION');
		$delai = Prado::localize('DELAI_OBTENTION')." ".Prado::localize('EN_JOUR');

		$etiq->writeString(0, 0,$entite1, $hdr_format);
		$etiq->writeString(0, 1,$entite2, $hdr_format);
		$etiq->writeString(0, 2,$nomEtab, $hdr_format);
		$etiq->writeString(0, 3,$typePrestation, $hdr_format);
		$etiq->writeString(0, 4,$prestation, $hdr_format);
		$etiq->writeString(0, 5,$delai, $hdr_format);

		$c=1;
		$criteriaVo=$this->Page->getViewState("CriteriaVo");
		$criteriaVo->setLimit(0);
		$criteriaVo->setOffset(0);
		$data = TDelaiObtentionPeer::getDelaiObtentionByCriteres($criteriaVo);
		$util = new Atexo_Utils_Util();
		foreach($data as $etab) {

			$lang = Atexo_User_CurrentUser::readFromSession("lang");
			$tEntiteQuery = new TEntiteQuery();
			$tEntite3 = $tEntiteQuery->getEntiteById($etab["ID_ENTITE"]);

			if(isset($tEntite3)) {
				$tEntiteQuery = new TEntiteQuery();
				$tEntite2 = $tEntiteQuery->getEntiteById($tEntite3->getIdEntiteParent());
				if(isset($tEntite2)) {
					$tEntiteQuery = new TEntiteQuery();
					$tEntite1 = $tEntiteQuery->getEntiteById($tEntite2->getIdEntiteParent());
					if(isset($tEntite1)) {
						$etiq->write($c,0,$tEntite1->getLibelleTraduit($lang),$corps_first);
					}
					else {
						$etiq->write($c,0,$tEntite2->getLibelleTraduit($lang),$corps_first);
					}
					$etiq->write($c,1,$tEntite2->getLibelleTraduit($lang),$corps_format);
				}
				else {
					$etiq->write($c,1,$tEntite3->getLibelleTraduit($lang),$corps_format);
				}
			}

			$etiq->write($c,2,$etab["NOM_ETAB"],$corps_format);
			$etiq->write($c,3,$etab["LIBELLE_TYPE_PRESTATION"],$corps_format);
			$etiq->write($c,4,$etab["LIB_PRESTATION"],$corps_format);
			$etiq->write($c,5,$util->getMontantArrondit($etab["VALUE_MOYENNE"]),$corps_format);
			$c++;
		}

		$nom_file = "DelaiObtentionRdv.xls";
		$workbook->send($nom_file);
		$workbook->close();
	}

	public function genererExcelConsolide() {

		require_once("Spreadsheet/Excel/Writer.php");

		//Initialisation du fichier excel
		$workbook = new Spreadsheet_Excel_Writer();

		//Style des cellules d'entete
		$hdr_format = &$workbook->addFormat();
		$hdr_format->setColor('black');
		$hdr_format->setBold(1);
		$hdr_format->setPattern(1);
		$hdr_format->setVAlign('vcenter');
		// our green (overwriting color on index 12)
		$workbook->setCustomColor(12, 200, 200, 200);
		$hdr_format->setFgColor(12);

		$hdr_format->setAlign('center');
		$hdr_format->setSize(10);
		$hdr_format->setBorder(1);
		$hdr_format->setOutLine();
		$hdr_format->setTextWrap();

		//Style des cellules
		$corps_format = &$workbook->addFormat();
		$corps_format->setColor('black');
		$corps_format->setPattern(1);
		$corps_format->setFgColor(1);
		$corps_format->setSize(10);
		$corps_format->setBorder(1);
		$corps_format->setAlign('center');
		$corps_format->setTextWrap();

		$nomEtab = utf8_decode(Prado::localize('ETABLISSEMENTS'));
		$entite1 = utf8_decode(Prado::localize('ENTITE_1'));
		$typePrestation = utf8_decode(Prado::localize('TYPE_PRESTATION'));
		$prestation = utf8_decode(Prado::localize('PRESTATION'));

		$criteriaVo = $this->getCriteres();
		$util = new Atexo_Utils_Util();

		//Creation de l'entete du fichier excel
		$etiqPr = &$workbook->addWorkSheet('PRESTATION');

		$etiqPr->setColumn(0, 0, 30);
		$etiqPr->setColumn(0, 1, 30);
		$etiqPr->setColumn(0, 2, 30);

		$etiqPr->writeString(0, 2,utf8_decode(Prado::localize('TITRE_EXCEL_DELAI_PRESTA_ETAB')), $hdr_format);
		$etiqPr->writeString(0, 3,"", $hdr_format);
		$etiqPr->writeString(0, 4,"", $hdr_format);
		$etiqPr->writeString(0, 5,"", $hdr_format);
		$etiqPr->writeString(0, 6,"", $hdr_format);
		$etiqPr->writeString(0, 7,"", $hdr_format);
		$etiqPr->writeString(0, 8,"", $hdr_format);
		$etiqPr->writeString(0, 9,"", $hdr_format);
		$etiqPr->mergeCells (0, 2, 0, 9);

		$etiqPr->writeString(3, 0,$entite1, $hdr_format);
		$etiqPr->writeString(3, 1,$typePrestation, $hdr_format);
		$etiqPr->writeString(3, 2,$prestation, $hdr_format);

		$col = 2;

		$debInit = $deb = substr($util->frnDate2iso($criteriaVo->getDateDu()),0,7);
		$fin = substr($util->frnDate2iso($criteriaVo->getDateAu()),0,7);
		while($deb<=$fin) {
			$date = explode("-",$deb);
			$libelleDateDeb = Prado::localize("MONTH".((int)$date[1])).' '.$date[0];
			$col++;
			$etiqPr->setColumn(3, $col, 10);
			$etiqPr->writeString(3,$col, utf8_decode($libelleDateDeb), $hdr_format);
			$deb = $this->getMoisProchain($deb);
		}

		$c=4;
		$data = TDelaiObtentionPeer::getTabDelaiObtentionByPrestationAndCriteres($criteriaVo);
		foreach($data as $dataEntite1) {
			$cET = $c;
			$etiqPr->write($c,0,utf8_decode($dataEntite1["NOM_ENTITE1"]),$hdr_format);
			foreach($dataEntite1["TYPE_PRESTATION"] as $dataTypePrestation) {
				$cTP = $c;
				$etiqPr->write($c, 1, utf8_decode($dataTypePrestation["LIBELLE_TYPE_PRESTATION"]), $hdr_format);
				foreach($dataTypePrestation["PRESTATION"] as $dataEtablissement) {
					if($c>$cTP) {
						$etiqPr->write($c, 0, "", $hdr_format);
						$etiqPr->write($c, 1, "", $hdr_format);
					}
					$etiqPr->write($c, 2, utf8_decode($dataEtablissement["LIBELLE_PRESTATION"]), $corps_format);
					$deb = $debInit;
					$col=3;
					while($deb<=$fin) {
						if($dataEtablissement['MOIS'][$deb]) {
							$etiqPr->write($c, $col, $dataEtablissement['MOIS'][$deb], $corps_format);
						}
						else {
							$etiqPr->write($c, $col, "-", $corps_format);
						}
						$col++;
						$deb = $this->getMoisProchain($deb);
					}
					$c++;
				}
				$etiqPr->mergeCells ($cTP, 1, $c-1, 1);
			}
			$etiqPr->mergeCells ($cET, 0, $c-1, 0);
		}

		$etiqPr = &$workbook->addWorkSheet('ETABLISSEMENTS');

		$etiqPr->setColumn(0, 0, 30);
		$etiqPr->setColumn(0, 1, 30);
		$etiqPr->setColumn(0, 2, 30);

		$etiqPr->writeString(0, 2,utf8_decode(Prado::localize('TITRE_EXCEL_DELAI_PRESTA')), $hdr_format);
		$etiqPr->writeString(0, 3,"", $hdr_format);
		$etiqPr->writeString(0, 4,"", $hdr_format);
		$etiqPr->writeString(0, 5,"", $hdr_format);
		$etiqPr->writeString(0, 6,"", $hdr_format);
		$etiqPr->writeString(0, 7,"", $hdr_format);
		$etiqPr->writeString(0, 8,"", $hdr_format);
		$etiqPr->writeString(0, 9,"", $hdr_format);
		$etiqPr->mergeCells (0, 2, 0, 9);

		$etiqPr->writeString(3, 0,$entite1, $hdr_format);
		$etiqPr->writeString(3, 1,$typePrestation, $hdr_format);
		$etiqPr->writeString(3, 2,$nomEtab, $hdr_format);

		$col = 2;

		$debInit = $deb = substr($util->frnDate2iso($criteriaVo->getDateDu()),0,7);
		$fin = substr($util->frnDate2iso($criteriaVo->getDateAu()),0,7);
		while($deb<=$fin) {
			$date = explode("-",$deb);
			$libelleDateDeb = Prado::localize("MONTH".((int)$date[1])).' '.$date[0];
			$col++;
			$etiqPr->setColumn(3, $col, 10);
			$etiqPr->writeString(3,$col, utf8_decode($libelleDateDeb), $hdr_format);
			$deb = $this->getMoisProchain($deb);
		}

		$c=4;
		$data = TDelaiObtentionPeer::getTabDelaiObtentionByTypePrestaAndEtabAndCriteres($criteriaVo);
		foreach($data as $dataEntite1) {
			$cET = $c;
			$etiqPr->write($c,0,utf8_decode($dataEntite1["NOM_ENTITE1"]),$hdr_format);
			foreach($dataEntite1["TYPE_PRESTATION"] as $dataTypePrestation) {
				$cTP = $c;
				$etiqPr->write($c, 1, utf8_decode($dataTypePrestation["LIBELLE_TYPE_PRESTATION"]), $hdr_format);
				foreach($dataTypePrestation["ETABLISSEMENT"] as $dataEtablissement) {
					if($c>$cTP) {
						$etiqPr->write($c, 0, "", $hdr_format);
						$etiqPr->write($c, 1, "", $hdr_format);
					}
					$etiqPr->write($c, 2, utf8_decode($dataEtablissement["NOM_ETAB"]), $corps_format);
					$deb = $debInit;
					$col=3;
					while($deb<=$fin) {
						if($dataEtablissement['MOIS'][$deb]) {
							$etiqPr->write($c, $col, $dataEtablissement['MOIS'][$deb], $corps_format);
						}
						else {
							$etiqPr->write($c, $col, "-", $corps_format);
						}
						$col++;
						$deb = $this->getMoisProchain($deb);
					}
					$c++;
				}
				$etiqPr->mergeCells ($cTP, 1, $c-1, 1);
			}
			$etiqPr->mergeCells ($cET, 0, $c-1, 0);
		}

		$etiq = &$workbook->addWorkSheet('PRESTA_ETABLISSEMENTS');
		$etiq1Mois = &$workbook->addWorkSheet('PRESTA_ETAB_1MOIS');
		$etiq2Mois = &$workbook->addWorkSheet('PRESTA_ETAB_2MOIS');

		$etiq->setColumn(0, 0, 30);
		$etiq->setColumn(0, 1, 30);
		$etiq->setColumn(0, 2, 30);

		$etiq1Mois->setColumn(0, 0, 30);
		$etiq1Mois->setColumn(0, 1, 30);
		$etiq1Mois->setColumn(0, 2, 30);

		$etiq2Mois->setColumn(0, 0, 30);
		$etiq2Mois->setColumn(0, 1, 30);
		$etiq2Mois->setColumn(0, 2, 30);

		$etiq->writeString(0, 2,utf8_decode(Prado::localize('TITRE_EXCEL_DELAI_ETAB')), $hdr_format);
		$etiq->writeString(0, 3,"", $hdr_format);
		$etiq->writeString(0, 4,"", $hdr_format);
		$etiq->writeString(0, 5,"", $hdr_format);
		$etiq->writeString(0, 6,"", $hdr_format);
		$etiq->writeString(0, 7,"", $hdr_format);
		$etiq->writeString(0, 8,"", $hdr_format);
		$etiq->writeString(0, 9,"", $hdr_format);
		$etiq->mergeCells (0, 2, 0, 9);

		$etiq->writeString(3, 0,$entite1, $hdr_format);
		$etiq->writeString(3, 1,$prestation, $hdr_format);
		$etiq->writeString(3, 2,$nomEtab, $hdr_format);

		$etiq1Mois->writeString(0, 2,utf8_decode(Prado::localize('TITRE_EXCEL_DELAI_ETAB_SUPP_1_MOIS')), $hdr_format);
		$etiq1Mois->writeString(0, 3,"", $hdr_format);
		$etiq1Mois->writeString(0, 4,"", $hdr_format);
		$etiq1Mois->writeString(0, 5,"", $hdr_format);
		$etiq1Mois->writeString(0, 6,"", $hdr_format);
		$etiq1Mois->writeString(0, 7,"", $hdr_format);
		$etiq1Mois->writeString(0, 8,"", $hdr_format);
		$etiq1Mois->writeString(0, 9,"", $hdr_format);
		$etiq1Mois->mergeCells (0, 2, 0, 9);

		$etiq1Mois->writeString(3, 0,$entite1, $hdr_format);
		$etiq1Mois->writeString(3, 1,$prestation, $hdr_format);
		$etiq1Mois->writeString(3, 2,$nomEtab, $hdr_format);

		$etiq2Mois->writeString(0, 2,utf8_decode(Prado::localize('TITRE_EXCEL_DELAI_ETAB_SUPP_2_MOIS')), $hdr_format);
		$etiq2Mois->writeString(0, 3,"", $hdr_format);
		$etiq2Mois->writeString(0, 4,"", $hdr_format);
		$etiq2Mois->writeString(0, 5,"", $hdr_format);
		$etiq2Mois->writeString(0, 6,"", $hdr_format);
		$etiq2Mois->writeString(0, 7,"", $hdr_format);
		$etiq2Mois->writeString(0, 8,"", $hdr_format);
		$etiq2Mois->writeString(0, 9,"", $hdr_format);
		$etiq2Mois->mergeCells (0, 2, 0, 9);

		$etiq2Mois->writeString(3, 0,$entite1, $hdr_format);
		$etiq2Mois->writeString(3, 1,$prestation, $hdr_format);
		$etiq2Mois->writeString(3, 2,$nomEtab, $hdr_format);

		$col = 2;

		$debInit = $deb = substr($util->frnDate2iso($criteriaVo->getDateDu()),0,7);
		$fin = substr($util->frnDate2iso($criteriaVo->getDateAu()),0,7);
		while($deb<=$fin) {
			$date = explode("-",$deb);
			$libelleDateDeb = Prado::localize("MONTH".((int)$date[1])).' '.$date[0];
			$col++;
			$etiq->setColumn(3, $col, 10);
			$etiq->writeString(3,$col, utf8_decode($libelleDateDeb), $hdr_format);

			$etiq1Mois->setColumn(3, $col, 10);
			$etiq1Mois->writeString(3,$col, utf8_decode($libelleDateDeb), $hdr_format);

			$etiq2Mois->setColumn(3, $col, 10);
			$etiq2Mois->writeString(3,$col, utf8_decode($libelleDateDeb), $hdr_format);
			$deb = $this->getMoisProchain($deb);
		}

		$c=$c1=$c2=4;
		$data = TDelaiObtentionPeer::getTabDelaiObtentionByEtabAndCriteres($criteriaVo);
		foreach($data as $dataEntite1) {
			$marge1ET=false;
			$marge2ET=false;
			$cET = $c;
			$cET1 = $c1;
			$cET2 = $c2;
			$etiq->write($c,0,utf8_decode($dataEntite1["NOM_ENTITE1"]),$hdr_format);
			foreach($dataEntite1["PRESTATION"] as $dataTypePrestation) {
				$marge1TP=false;
				$marge2TP=false;
				$cTP = $c;
				$cTP1 = $c1;
				$cTP2 = $c2;
				$etiq->write($c, 1, utf8_decode($dataTypePrestation["LIBELLE_PRESTATION"]), $hdr_format);
				foreach($dataTypePrestation["ETABLISSEMENT"] as $dataEtablissement) {
					$marge1=false;
					$marge2=false;
					if($c>$cTP) {
						$etiq->write($c, 0, "", $hdr_format);
						$etiq->write($c, 1, "", $hdr_format);
					}
					$etiq->write($c, 2, utf8_decode($dataEtablissement["NOM_ETAB"]), $corps_format);
					$deb = $debInit;
					$col=3;
					while($deb<=$fin) {
						if($dataEtablissement['MOIS'][$deb]) {
							if(str_replace(",",".",$dataEtablissement['MOIS'][$deb])>=Atexo_Config::getParameter("DELAI_MARGE_1")) {
								$etiq1Mois->write($c1, $col, $dataEtablissement['MOIS'][$deb], $corps_format);
								if(!$marge1) {
									$etiq1Mois->write($c1, 2, utf8_decode($dataEtablissement["NOM_ETAB"]), $corps_format);
								}
								$marge1=$marge1TP=$marge1ET=true;
								if($c1>$cTP1) {
									$etiq1Mois->write($c1, 0, "", $hdr_format);
									$etiq1Mois->write($c1, 1, "", $hdr_format);
								}
							}
							if(str_replace(",",".",$dataEtablissement['MOIS'][$deb])>=Atexo_Config::getParameter("DELAI_MARGE_2")) {
								$etiq2Mois->write($c2, $col, $dataEtablissement['MOIS'][$deb], $corps_format);
								if(!$marge2) {
									$etiq2Mois->write($c2, 2, utf8_decode($dataEtablissement["NOM_ETAB"]), $corps_format);
								}
								$marge2=$marge2TP=$marge2ET=true;
								if($c2>$cTP2) {
									$etiq2Mois->write($c2, 0, "", $hdr_format);
									$etiq2Mois->write($c2, 1, "", $hdr_format);
								}
							}
							$etiq->write($c, $col, $dataEtablissement['MOIS'][$deb], $corps_format);
						}
						else {
							$etiq->write($c, $col, "-", $corps_format);
						}
						$col++;
						$deb = $this->getMoisProchain($deb);
					}
					$c++;
					if($marge1) {
						$c1++;
					}
					if($marge2) {
						$c2++;
					}
				}
				$etiq->mergeCells ($cTP, 1, $c-1, 1);
				if($marge1TP) {
					$etiq1Mois->write($cTP1, 1, utf8_decode($dataTypePrestation["LIBELLE_PRESTATION"]), $hdr_format);
					$etiq1Mois->mergeCells ($cTP1, 1, $c1-1, 1);
				}
				if($marge2TP) {
					$etiq2Mois->write($cTP2, 1, utf8_decode($dataTypePrestation["LIBELLE_PRESTATION"]), $hdr_format);
					$etiq2Mois->mergeCells ($cTP2, 1, $c2-1, 1);
				}
			}
			$etiq->mergeCells ($cET, 0, $c-1, 0);
			if($marge1ET) {
				$etiq1Mois->write($cET1, 0, utf8_decode($dataEntite1["NOM_ENTITE1"]), $hdr_format);
				$etiq1Mois->mergeCells ($cET1, 0, $c1-1, 0);
			}
			if($marge2ET) {
				$etiq2Mois->write($cET2, 0, utf8_decode($dataEntite1["NOM_ENTITE1"]), $hdr_format);
				$etiq2Mois->mergeCells ($cET2, 0, $c2-1, 0);
			}
		}

		$nom_file = "EvolutionDelaiObtentionRdv.xls";
		$workbook->send($nom_file);
		$workbook->close();
	}

	private function getMoisProchain($anneeMois) {
		$date = explode("-", $anneeMois);
		if($date[1]==12) {
			return ($date[0]+1)."-01";
		}
		return ($date[0])."-".str_pad(($date[1]+1),2,"0", STR_PAD_LEFT);
	}
}
