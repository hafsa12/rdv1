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

class FormulaireRessource extends AtexoPage {

	private $_lang = "";
	private $_dataNom = null;
	private $_dataPrenom = null;
	private $_dataCode = null;
    private $_dataLogin = null;

	public function onInit()
	{
		$this->Master->setCalledFrom("admin");
		Atexo_Utils_Languages::setLanguageCatalogue($this->Master->getCalledFrom());
	}

	public function onLoad()
	{
		if(!Atexo_User_CurrentUser::hasHabilitation('GestionRessource')) {
			$this->response->redirect("?page=administration.AccueilAdministrateurAuthentifie");
		}
		$this->_lang = Atexo_User_CurrentUser::readFromSession("lang");
		if(!$this->isPostBack) {
			$idOrg = Atexo_User_CurrentUser::getIdOrganisationGere();
			if($idOrg>0) {
				$tOrganisationQuery = new TOrganisationQuery();
				$tOrganisation = $tOrganisationQuery->getOrganisationById($idOrg);
				$typePrestation = $tOrganisation->getTypePrestation();
			}
			else {
				$typePrestation = Atexo_Config::getParameter('PRESTATION_SAISIE_LIBRE');
			}
			$this->setViewState("typePrestation", $typePrestation);
			if(isset($_GET["idRessource"])) {
				$this->indisponibilitePanel->Visible=true;
				$this->remplir($_GET["idRessource"]);
                $this->modifPasswordPanel->setVisible(true);
			}elseif(isset($_GET["idOldRessource"])){
				$this->indisponibilitePanel->Visible=true;
				$this->remplirOld($_GET["idOldRessource"]);
                $this->modifPasswordPanel->setVisible(true);
			}
			else{
                $this->modifPasswordPanel->setVisible(false);
            }
			$this->getListeNomPrenomParLangues($this->_dataNom, $this->_dataPrenom, $this->_dataCode);
			$this->remplirListeOrganisation();
			$adminOrg = Atexo_User_CurrentUser::isAdminOrg();
			$adminEtab = Atexo_User_CurrentUser::isAdminEtab();

			if($adminOrg) {
				$idOrganisation = Atexo_User_CurrentUser::getIdOrganisationGere();
				$this->organisationRessource->SelectedValue=$idOrganisation;
				$this->organisationRessource->Enabled=false;
				$this->loadEtablissementRessource();
			}

			if($adminEtab) {
				$idOrganisation = Atexo_User_CurrentUser::getIdOrganisationAttache();
				$this->organisationRessource->SelectedValue=$idOrganisation;
				$this->organisationRessource->Enabled=false;

				$this->loadEtablissementRessource();
			}
            $this->remplirListeProfilsRessources();
			$this->showPanelProfil();
		}
	}

	public function getListeNomPrenomParLangues($dataNom, $dataPrenom, $dataCode) {
		self::getListeNomParLangues($dataNom);
		self::getListePrenomParLangues($dataPrenom);
		if($_SESSION['typeRessource']) {
			self::getListeCodeParLangues($dataCode);
		}
	}

	public function getListeNomParLangues($data=null) {
		if(count($data) > 0) {
			$this->setListeNomParLangues($data);
		} else {
			//recupérer les langues
			$langues[]= explode(",", Atexo_Config::getParameter("LANGUES_ACTIVES"));

			$data = array();
			$index=0;
			foreach($langues[0] as $lan){
				$data[$index]['nomRessource'] = '';
				$data[$index]['nomLibelleLang'] = Prado::localize('NOM_RESSOURCE');
				$data[$index]['lang'] = '('.Prado::localize('LANG_'.strtoupper($lan)).')';
				$data[$index]['langNom'] = $lan;
				$index++;
			}
			$this->setListeNomParLangues($data);
		}
	}

	public function getListePrenomParLangues($data=null) {
		if(count($data) > 0) {
			$this->setListePrenomParLangues($data);
		} else {
			//recupérer les langues
			$langues[]= explode(",", Atexo_Config::getParameter("LANGUES_ACTIVES"));

			$data = array();
			$index=0;
			foreach($langues[0] as $lan){
				$data[$index]['prenomRessource'] = '';
				$data[$index]['prenomLibelleLang'] = Prado::localize('PRENOM_RESSOURCE');
				$data[$index]['lang'] = '('.Prado::localize('LANG_'.strtoupper($lan)).')';
				$data[$index]['langPrenom'] = $lan;
				$index++;
			}
			$this->setListePrenomParLangues($data);
		}
	}

	public function getListeCodeParLangues($data=null) {
		if(count($data) > 0) {
			$this->setListeCodeParLangues($data);
		} else {
			//recupérer les langues
			$langues[]= explode(",", Atexo_Config::getParameter("LANGUES_ACTIVES"));

			$data = array();
			$index=0;
			foreach($langues[0] as $lan){
				$data[$index]['codeRessource'] = '';
				$data[$index]['codeLibelleLang'] = Prado::localize('CODE_RESSOURCE');
				$data[$index]['lang'] = '('.Prado::localize('LANG_'.strtoupper($lan)).')';
				$data[$index]['langCode'] = $lan;
				$index++;
			}
			$this->setListeCodeParLangues($data);
		}
	}

	public function setListePrenomParLangues($data) {
		$this->listePrenomLangues->dataSource = $data;
		$this->listePrenomLangues->dataBind();
		$index = 0;
		foreach ($this->listePrenomLangues->getItems() as $item) {
			$item->prenomLibelleLang->Text = $data[$index]['prenomLibelleLang'];
			$item->prenomRessource->Text = $data[$index]['prenomRessource'];
			$item->lang->Text = $data[$index]['lang'];
			$item->langPrenom->Value = $data[$index]['langPrenom'];
			$index++;
		}
	}

	public function setListeNomParLangues($data) {
		$this->listeNomLangues->dataSource = $data;
		$this->listeNomLangues->dataBind();
		$index = 0;
		foreach ($this->listeNomLangues->getItems() as $item) {
			$item->nomLibelleLang->Text = $data[$index]['nomLibelleLang'];
			$item->nomRessource->Text = $data[$index]['nomRessource'];
			$item->lang->Text = $data[$index]['lang'];
			$item->langNom->Value = $data[$index]['langNom'];
			$index++;
		}
	}

	public function setListeCodeParLangues($data) {
		$this->listeCodeLangues->dataSource = $data;
		$this->listeCodeLangues->dataBind();
		$index = 0;
		foreach ($this->listeCodeLangues->getItems() as $item) {
			$item->codeLibelleLang->Text = $data[$index]['codeLibelleLang'];
			$item->codeRessource->Text = $data[$index]['codeRessource'];
			$item->lang->Text = $data[$index]['lang'];
			$item->langCode->Value = $data[$index]['langCode'];
			$index++;
		}
	}

	public function remplirListeOrganisation() {
		$organisations = new Atexo_Organisation_Gestion();
		$this->organisationRessource->DataSource = $organisations->getAllOrganisation($this->_lang, Prado::localize('SELECTIONNEZ'));
		$this->organisationRessource->DataBind();
	}

	public function loadEtablissementRessource($sender=null, $param=null) {
		$idOrganisation = $this->organisationRessource->getSelectedValue();
		//Remplir liste Etablissement
		$etablissements = new Atexo_Etablissement_Gestion();
		$this->etablissementRessource->DataSource = $etablissements->getEtablissementByIdProvinceIdOrganisation($this->_lang, $idOrganisation, null, Prado::localize('SELECTIONNEZ'),true);
		$this->etablissementRessource->DataBind();
		if($param) {
			$this->organisationEtabRessource->render($param->NewWriter);
		}
	}

	public function loadTypePrestationRessource($sender=null, $param=null) {
		$idEtab = $this->etablissementRessource->getSelectedValue();
		//Remplir liste Type Prestation
		$typePrests = new Atexo_TypePrestation_Gestion();
		$this->prestationAssociee->typePrestation->DataSource = $typePrests->getTypePrestationByIdEtab($this->_lang,$idEtab, Prado::localize('SELECTIONNEZ'));
		$this->prestationAssociee->typePrestation->DataBind();
		
		if($param) {
			$this->Page->setViewState("prestationAssociees",array());
			$this->remplirListePrestationAssociees($sender,$param);	
		}
	}

	public function loadPrestation() {
		$idTypePrest = $this->prestationAssociee->typePrestation->getSelectedValue();
		$typePrestation = $this->getViewState("typePrestation");
		//Remplir liste Prestations
		$prests = new Atexo_Prestation_Gestion();
		$data = $prests->getPrestationByIdTypePrestation($this->_lang,$idTypePrest,$typePrestation, Prado::localize('SELECTIONNEZ'));
		$prestationAssociees = $this->getViewState("prestationAssociees");
		if(is_array($prestationAssociees)) {
			$idsPrestationAssociees = array_keys($prestationAssociees);
			foreach (array_keys($data) as $key){
				if(in_array($key, $idsPrestationAssociees)) {
					unset($data[$key]);
				}
			}
		}
		$this->prestationAssociee->prestation->DataSource = $data;
		$this->prestationAssociee->prestation->DataBind();
	}

	public function loadPeriodicite() {
		$idPrest = $this->prestationAssociee->prestation->getSelectedValue();
		$tPrestationQuery = new TPrestationQuery();
		$tPrestation = $tPrestationQuery->getPrestationById($idPrest);
		if($tPrestation instanceof TPrestation) {
			$this->prestationAssociee->inputDureeRdv->Text = $tPrestation->getPeriodicite();
		}
	}

	public function loadTypePrestationForHoraire() {
		$idEtab = $this->etablissementRessource->getSelectedValue();
		//Remplir liste Type Prestation
		$typePrests = new Atexo_TypePrestation_Gestion();
		$this->prestationAssociee->typePrestation->DataSource = $typePrests->getTypePrestationByIdEtab($this->_lang,$idEtab, Prado::localize('SELECTIONNEZ'));
		$this->prestationAssociee->typePrestation->DataBind();
	}

	public function loadPrestationForHoraire($idTypePrest) {
		//Remplir liste Prestations
		$typePrestation = $this->getViewState("typePrestation");
		$prests = new Atexo_Prestation_Gestion();
		$this->prestationAssociee->prestation->DataSource = $prests->getPrestationByIdTypePrestation($this->_lang,$idTypePrest,$typePrestation, Prado::localize('SELECTIONNEZ'));
		$this->prestationAssociee->prestation->DataBind();
	}

	public function loadPeriodiciteForHoraire($idPrest) {
		$tPrestationQuery = new TPrestationQuery();
		$tPrestation = $tPrestationQuery->getPrestationById($idPrest);
		if($tPrestation instanceof TPrestation) {
			$this->prestationAssociee->inputDureeRdv->Text = $tPrestation->getPeriodicite();
		}
	}

	public function remplirListePrestationAssociees($sender,$param) {
		$this->listePrestationsAssociees->DataSource = $this->getViewState("prestationAssociees") ;
		$this->listePrestationsAssociees->DataBind();
		$this->PrestationAssocie->setStyle('display:block');
		$this->validateNbrePrestsPanel->render($param->getNewWriter());
		$this->PrestationAssocie->render($param->getNewWriter());
	}

    public function remplirListeProfilsRessources() {
        $profilGestion = new Atexo_Profil_Gestion();
        $profiles = $profilGestion->getAllPossibleProfilRessourceByConnected($this->_lang, Prado::localize('SELECTIONNEZ'),true);

        $this->profilUtilisateur->DataSource = $profiles;
        $this->profilUtilisateur->DataBind();
        $this->profilUtilisateur->setselectedIndex(1);
    }

	public function getLibellesTypePrestationAndPrestation($idPrestation) {

		$tPrestationQuery = new TPrestationQuery();
		$tPrestation = $tPrestationQuery->getPrestationById($idPrestation);
		if($tPrestation instanceof TPrestation) {
			$tTypePrestation = $tPrestation->getTTypePrestation();
			if($tTypePrestation instanceof TTypePrestation) {
				return $tTypePrestation->getLibelleTypePrestationTraduit($this->_lang).' - '.$tPrestation->getLibellePrestationTraduit($this->_lang);
			}
			return $tPrestation->getLibellePrestationTraduit($this->_lang);
		}
	}

	public function viderFormulairePrestation($sender, $param) {
		$this->prestationAssociee->viderFormulaire($sender,$param);
	}

	public function viderFormulairePrestationEdit($sender, $param) {
		$this->typePrestation->DataSource = array();
		$this->typePrestation->DataBind();
		$this->prestation->DataSource = array();
		$this->prestation->DataBind();
		$this->panelEditPrestsAssoc->render($param->getNewWriter());
	}

	public function viderFormulaireHoraire($sender, $param) {
		$this->prestationAssociee->viderFormulaire($sender,$param);
	}

	public function deletePrestation($sender, $param) {
		$idPrestToDelete = $sender->CommandParameter;
		$data = $this->getViewState("prestationAssociees");
		unset($data[$idPrestToDelete]);
		$this->nbrePrestationAttachees->Value = count($data);
		$this->Page->setViewState("prestationAssociees",$data);
		$this->remplirListePrestationAssociees($sender,$param);
	}

	public function deleteHoraire($sender, $param) {
		$tab = explode('-',$sender->CommandParameter);
		$listes = $this->getViewState("prestationAssociees");
		$horairesPrestation = $listes[$tab[0]]['horaires'];
		unset($horairesPrestation[$tab[1]]);
		$listes[$tab[0]]['horaires'] = $horairesPrestation;
		$this->nbrePrestationAttachees->Value = count($listes);
		$this->Page->setViewState("prestationAssociees",$listes);
		$this->remplirListePrestationAssociees($sender,$param);

	}
	public function getHoraireForm($sender, $param) {
		$oneHoraire = null;
		$tab = explode('-',$sender->CommandParameter);
		$listes = $this->getViewState("prestationAssociees");
		if($tab[3]=="editHoraire") {
			//get horaire to edit
			$this->prestationAssociee->setEditHoraire(true);
			$horairesPrestation = $listes[$tab[1]]['horaires'];
			$oneHoraire = $horairesPrestation[$tab[2]];
		} elseif($tab[3]=="addHoraire") {
			$this->prestationAssociee->setEditHoraire(false);
			$this->prestationAssociee->setAddHoraire(true);
		}
		$this->prestationAssociee->setForHoraire(true);
		$this->prestationAssociee->setForPrestation(false);
		$this->prestationAssociee->setIdTypePrestation($tab[0]);
		$this->prestationAssociee->setIdPrestation($tab[1]);
		$this->prestationAssociee->setIdHoraire($tab[2]);
		$this->prestationAssociee->getObjectToEdit($oneHoraire);
		$this->prestationAssociee->panelPrestsAssoc->render($param->getNewWriter());
	}

	public function getPrestationForm($sender, $param) {
		$tab = explode('-',$sender->CommandParameter);
		$typePrestation = $this->getViewState("typePrestation");
		$idEtab = $this->etablissementRessource->getSelectedValue();
		//Remplir liste Type Prestation
		$typePrests = new Atexo_TypePrestation_Gestion();
		$this->typePrestation->DataSource = $typePrests->getTypePrestationByIdEtab($this->_lang,$idEtab, Prado::localize('SELECTIONNEZ'));
		$this->typePrestation->DataBind();
		$this->typePrestation->setSelectedValue($tab[0]);
		$this->idTypePrestationToEdit->Value = $tab[0];
		//Remplir liste Prestations
		$prests = new Atexo_Prestation_Gestion();
		$this->prestation->DataSource = $prests->getPrestationByIdTypePrestation($this->_lang,$tab[0],$typePrestation, Prado::localize('SELECTIONNEZ'));
		$this->prestation->DataBind();
		$this->prestation->setSelectedValue($tab[1]);
		$this->idPrestationToEdit->Value = $tab[1];
		$this->panelEditPrestsAssoc->render($param->getNewWriter());
	}

	public function SavePrestationModif($sender, $param) {
		$listes = $this->getViewState("prestationAssociees");
		$horairesPrestation = $listes[$this->idPrestationToEdit->Value]['horaires'];
		foreach($horairesPrestation as $one) {
			$one->setIdTypePrestation($this->typePrestation->getSelectedValue());
			$one->setIdPrestation($this->prestation->getSelectedValue());
			$horairesPrestationEdited[] = $one;
		}
		unset($listes[$this->idPrestationToEdit->Value]);
		$listes[$this->prestation->getSelectedValue()]['idTypePrestation'] = $this->typePrestation->getSelectedValue();
		$listes[$this->prestation->getSelectedValue()]['idPrestation'] = $this->prestation->getSelectedValue();
		$listes[$this->prestation->getSelectedValue()]['horaires'] = $horairesPrestationEdited;
		$this->Page->setViewState("prestationAssociees",$listes);
		$this->viderFormulairePrestationEdit($sender, $param);
		$this->remplirListePrestationAssociees($sender,$param);
	}

	public function getFormulaireAjoutHoraire($sender, $param) {
		$this->prestationAssociee->titreModal->Text = Prado::localize('HORAIRES_TRAVAIL');
		$tab = explode('-',$sender->CommandParameter);
		$idEtab = $this->etablissementRessource->getSelectedValue();
		$typePrestation = $this->getViewState("typePrestation");
		//Remplir liste Type Prestation
		$typePrests = new Atexo_TypePrestation_Gestion();
		$this->prestationAssociee->typePrestation->DataSource = $typePrests->getTypePrestationByIdEtab($this->_lang,$idEtab, Prado::localize('SELECTIONNEZ'));
		$this->prestationAssociee->typePrestation->DataBind();
		$this->prestationAssociee->typePrestation->setSelectedValue($tab[0]);
		$idTypePrest = $this->prestationAssociee->typePrestation->getSelectedValue();
		//Remplir liste Prestations
		$prests = new Atexo_Prestation_Gestion();
		$this->prestationAssociee->prestation->DataSource = $prests->getPrestationByIdTypePrestation($this->_lang,$idTypePrest,$typePrestation, Prado::localize('SELECTIONNEZ'));
		$this->prestationAssociee->prestation->DataBind();
		$this->prestationAssociee->prestation->setSelectedValue($tab[1]);
		$this->prestationAssociee->panelPrestsAssoc->render($param->getNewWriter());
	}

	public function onEnregistrerClick()
	{
		$tAgentQuery = new TAgentQuery();
		$tAgent = $tAgentQuery->getAgentById($_GET["idRessource"]);
		if($tAgent==null) {
			$tAgent = new TAgent();
		}

		$tTraductionNom = new TTraduction();
		$tTraductionPrenom = new TTraduction();
		$tTraductionCode = new TTraduction();
        $compteVo = new Atexo_User_CompteVo();

        $gestionAgent = new Atexo_Agent_Gestion();
        $agent = $gestionAgent->retrieveAgentByLogin($this->identifiant->getSafeText());
        if($agent) {
            if($agent->getIdAgent() != $tAgent->getIdAgent()) {
                //$this->panelMsg->style="display:block";
            } else {
                $continue = true;
            }
        }
        if(!$agent || $continue) {
            if (($this->passwordUser->Text != '') && ($this->passwordUser->Text != 'xxxxxx')) {
                $tAgent->setTentativesMdp("0");
                $tAgent->setMotDePasse(sha1(Atexo_Utils_Util::atexoHtmlEntities($this->passwordUser->Text)));
            }
            //Creation de mot de passe aléatoire
            if ($tAgent->getMotDePasse() == null) {
                $mdp = $compteVo->genererPassWord();
                $tAgent->setMotDePasse(sha1($mdp));
                $this->passwordUser->Text = $mdp;
            }
            $tAgent->setLogin($this->identifiant->getSafeText());
        }
		if($tAgent!=null) {
			//Debut Modification Agent Nom Prenom selon Langues
			$this->modifierTraductionAgent($tAgent);
			//Fin Modification Agent Nom Prenom selon Langues
		} else {
			//Debut Ajout Agent Nom Prenom selon Langues
			foreach($this->listeNomLangues->getItems() as $item) {
				$tTraductionLibelle = new TTraductionLibelle();
				$tTraductionLibelle->setLang($item->langNom->Value);
				$tTraductionLibelle->setLibelle($item->nom->getSafeText());
				$tTraductionNom->addTTraductionLibelle($tTraductionLibelle);
			}
			foreach($this->listePrenomLangues->getItems() as $item) {
				$tTraductionLibelle = new TTraductionLibelle();
				$tTraductionLibelle->setLang($item->langPrenom->Value);
				$tTraductionLibelle->setLibelle($item->prenom->getSafeText());
				$tTraductionPrenom->addTTraductionLibelle($tTraductionLibelle);
			}
			foreach($this->listeCodeLangues->getItems() as $item) {
				$tTraductionLibelle = new TTraductionLibelle();
				$tTraductionLibelle->setLang($item->langCode->Value);
				$tTraductionLibelle->setLibelle($item->code->getSafeText());
				$tTraductionCode->addTTraductionLibelle($tTraductionLibelle);
			}
			//Fin Ajout Agent Nom Prenom selon Langues
			$tAgent->setTTraductionRelatedByCodeNomUtilisateur($tTraductionNom);
			$tAgent->setTTraductionRelatedByCodePrenomUtilisateur($tTraductionPrenom);
			$tAgent->setTTraductionRelatedByCodeUtilisateur($tTraductionCode);

		}

		if($this->ouiProfil->Checked == true){
            $tAgent->setEmailUtilisateur($this->emailUtilisateur->getSafeText());
            $tAgent->setTelephoneUtilisateur($this->telUtilisateur->getSafeText());
            $tAgent->setIdProfil($this->profilUtilisateur->getSelectedValue());
        }
		else{
            $tAgent->setEmailUtilisateur(null);
            $tAgent->setTelephoneUtilisateur(null);
            $tAgent->setIdProfil(null);
            $tAgent->setLogin(null);
            $tAgent->setMotDePasse(null);

        }

        if($this->oui->Checked){
        	$tAgent->setActif(1);
        }elseif($this->non->Checked){
        	$tAgent->setActif(0);
        }else{
        	$tAgent->setActif(2);
        }
		
		$tAgent->setIdOrganisationAttache($this->organisationRessource->getSelectedValue());
		$tAgent->setIdEtablissementAttache($this->etablissementRessource->getSelectedValue());
		$tAgent->save();
		$prestationAssociees = $this->Page->getViewState("prestationAssociees");

		//Effacer les periodes et agendas de cet agent et insérer à nouveau
		if($tAgent!=null) {
			$agendas = $tAgent->getTAgendas();
			foreach($agendas as $oneAgenda) {
				$periodesAgenda = $oneAgenda->getTPeriodes();
				foreach($periodesAgenda as $oneHoraire) {
					$oneHoraire->delete();
				}
				$oneAgenda->delete();
			}
		}
		foreach($prestationAssociees as $onePrest) {
			$tAgenda = new TAgenda();
			$tAgenda->setIdAgent($tAgent->getIdAgent());
			$tAgenda->setIdPrestation($onePrest['idPrestation']);
			//$tAgenda->setPeriodicite($onePrest['periodicite']);
			foreach($onePrest['horaires'] as $oneHoraire) {
				$tperiode = $this->getPeriode($oneHoraire);
				$tAgenda->addTPeriode($tperiode);
				$tAgenda->save();
			}
		}
		if($tAgent->getIdAgent()>0 && (!isset($_GET["idRessource"]) || $this->passwordUser->Text!='xxxxxx')) {//Envoi de mail de creation ou de modification de compte Agent
				$this->envoiMail($tAgent->getLogin(), $this->passwordUser->Text, $tAgent->getEmailUtilisateur(), 41);
			}
		$this->response->redirect("?page=administration.GestionRessources&search");
	}
	
	/**
	 * Envoi des infos du compte par mail
	 *
	 * @param string $login
	 * @param string $mdp
	 * @param string $email
	 */
	protected function envoiMail($login, $mdp, $email, $idTypeProfil)
	{
		$mail = new Atexo_Utils_Mail();
		$pfUrl = $this->getPfUrl();

		if($idTypeProfil == Atexo_Config::getParameter("ID_PROFIL_ADMIN_ORGANISATION") || 
			$idTypeProfil == Atexo_Config::getParameter("ID_PROFIL_ADMIN_ETABLISSSEMENT") || 
			$idTypeProfil == Atexo_Config::getParameter("ID_PROFIL_ADMIN_SYSTEM")) {


			$urlAuthentification = $pfUrl.'?page=administration.AdministrationAccueil';
		} else {
			$urlAuthentification = $pfUrl.'?page=agent.AgentAccueil';
		}
		if(isset($_GET["idRessource"]))
		{
			$objet = prado::localize('COMPTE_MAIL_PREFIXE_OBJET') ." ".Prado::localize("TRANS_CHAMP_VIDE")." ". prado::localize('MDPREC_OBJET_COURRIEL_COMPTE_MODIFIE');
			$debutMail = prado::localize('MAIL_MODIFICATION_IDENTIFIANT_PART1');
		} else {
			$objet = prado::localize('COMPTE_MAIL_PREFIXE_OBJET') ." ".Prado::localize("TRANS_CHAMP_VIDE")." ". prado::localize('COMPTE_COMPTE_ACCES');
			$debutMail = prado::localize('MAIL_CREATION_COMPTE_PART1');
		}
		$corpsMessage = $debutMail . $login;
		if($mdp != prado::localize('TRANS_MDP_ETOILE'))
		{
			$corpsMessage .= prado::localize('MAIL_CREATION_IDENTIFIANT_PART2') . $mdp;
		}
		$corpsMessage .= prado::localize('MAIL_CREATION_IDENTIFIANT_PART3').' <a href="'.$urlAuthentification.'">'.$urlAuthentification.'</a>';
		$corpsMessage .= prado::localize('MAIL_GENERATION_COMPTE_OF_PART4')
		. prado::localize('MAIL_CREATION_COMPTE_OF_PART6')
		. prado::localize('MAIL_CREATION_COMPTE_OF_PART7');
		try {
			$mail->envoyerMail(Atexo_Config::getParameter('PF_MAIL_FROM'),$email,$objet,$corpsMessage);
		}catch (Exception $e){
			$logger = Atexo_LoggerManager::getLogger("rdvLogErreur");
		    $logger->error($e->getMessage());
			Atexo_Utils_GestionException::catchException($e);
		}
	}
	public function getPeriode($oneHoraire) {
		$tperiode = new TPeriode();
		$tperiode->setDebutPeriode(Atexo_Utils_Util :: frnDate2iso($oneHoraire->getDebutPeriode()));
		$tperiode->setfinPeriode(Atexo_Utils_Util :: frnDate2iso($oneHoraire->getfinPeriode()));
		$tperiode->setPeriodicite($oneHoraire->getPeriodicite());
		$tperiode->setLundiHeureDebut1($oneHoraire->getLundiHeureDebut1());
		$tperiode->setLundiHeureFin1($oneHoraire->getLundiHeureFin1());
		$tperiode->setLundiCapacite1((int)$oneHoraire->getLundiCapacite1());
		$tperiode->setLundiNbRdvSite1((int)$oneHoraire->getLundiNbRdvSite1());
		$tperiode->setLundiHeureDebut2($oneHoraire->getLundiHeureDebut2());
		$tperiode->setLundiHeureFin2($oneHoraire->getLundiHeureFin2());
		$tperiode->setLundiCapacite2((int)$oneHoraire->getLundiCapacite2());
		$tperiode->setLundiNbRdvSite2((int)$oneHoraire->getLundiNbRdvSite2());
		$tperiode->setMardiHeureDebut1($oneHoraire->getMardiHeureDebut1());
		$tperiode->setMardiHeureFin1($oneHoraire->getMardiHeureFin1());
		$tperiode->setMardiCapacite1((int)$oneHoraire->getMardiCapacite1());
		$tperiode->setMardiNbRdvSite1((int)$oneHoraire->getMardiNbRdvSite1());
		$tperiode->setMardiHeureDebut2($oneHoraire->getMardiHeureDebut2());
		$tperiode->setMardiHeureFin2($oneHoraire->getMardiHeureFin2());
		$tperiode->setMardiCapacite2((int)$oneHoraire->getMardiCapacite2());
		$tperiode->setMardiNbRdvSite2((int)$oneHoraire->getMardiNbRdvSite2());
		$tperiode->setMercrediHeureDebut1($oneHoraire->getMercrediHeureDebut1());
		$tperiode->setMercrediHeureFin1($oneHoraire->getMercrediHeureFin1());
		$tperiode->setMercrediCapacite1((int)$oneHoraire->getMercrediCapacite1());
		$tperiode->setMercrediNbRdvSite1((int)$oneHoraire->getMercrediNbRdvSite1());
		$tperiode->setMercrediHeureDebut2($oneHoraire->getMercrediHeureDebut2());
		$tperiode->setMercrediHeureFin2($oneHoraire->getMercrediHeureFin2());
		$tperiode->setMercrediCapacite2((int)$oneHoraire->getMercrediCapacite2());
		$tperiode->setMercrediNbRdvSite2((int)$oneHoraire->getMercrediNbRdvSite2());
		$tperiode->setJeudiHeureDebut1($oneHoraire->getJeudiHeureDebut1());
		$tperiode->setJeudiHeureFin1($oneHoraire->getJeudiHeureFin1());
		$tperiode->setJeudiCapacite1((int)$oneHoraire->getJeudiCapacite1());
		$tperiode->setJeudiNbRdvSite1((int)$oneHoraire->getJeudiNbRdvSite1());
		$tperiode->setJeudiHeureDebut2($oneHoraire->getJeudiHeureDebut2());
		$tperiode->setJeudiHeureFin2($oneHoraire->getJeudiHeureFin2());
		$tperiode->setJeudiCapacite2((int)$oneHoraire->getJeudiCapacite2());
		$tperiode->setJeudiNbRdvSite2((int)$oneHoraire->getJeudiNbRdvSite2());
		$tperiode->setVendrediHeureDebut1($oneHoraire->getVendrediHeureDebut1());
		$tperiode->setVendrediHeureFin1($oneHoraire->getVendrediHeureFin1());
		$tperiode->setVendrediCapacite1((int)$oneHoraire->getVendrediCapacite1());
		$tperiode->setVendrediNbRdvSite1((int)$oneHoraire->getVendrediNbRdvSite1());
		$tperiode->setVendrediHeureDebut2($oneHoraire->getVendrediHeureDebut2());
		$tperiode->setVendrediHeureFin2($oneHoraire->getVendrediHeureFin2());
		$tperiode->setVendrediCapacite2((int)$oneHoraire->getVendrediCapacite2());
		$tperiode->setVendrediNbRdvSite2((int)$oneHoraire->getVendrediNbRdvSite2());
		$tperiode->setSamediHeureDebut1($oneHoraire->getSamediHeureDebut1());
		$tperiode->setSamediHeureFin1($oneHoraire->getSamediHeureFin1());
		$tperiode->setSamediCapacite1((int)$oneHoraire->getSamediCapacite1());
		$tperiode->setSamediNbRdvSite1((int)$oneHoraire->getSamediNbRdvSite1());
		$tperiode->setSamediHeureDebut2($oneHoraire->getSamediHeureDebut2());
		$tperiode->setSamediHeureFin2($oneHoraire->getSamediHeureFin2());
		$tperiode->setSamediCapacite2((int)$oneHoraire->getSamediCapacite2());
		$tperiode->setSamediNbRdvSite2((int)$oneHoraire->getSamediNbRdvSite2());
		$tperiode->setDimancheHeureDebut1($oneHoraire->getDimancheHeureDebut1());
		$tperiode->setDimancheHeureFin1($oneHoraire->getDimancheHeureFin1());
		$tperiode->setDimancheCapacite1((int)$oneHoraire->getDimancheCapacite1());
		$tperiode->setDimancheNbRdvSite1((int)$oneHoraire->getDimancheNbRdvSite1());
		$tperiode->setDimancheHeureDebut2($oneHoraire->getDimancheHeureDebut2());
		$tperiode->setDimancheHeureFin2($oneHoraire->getDimancheHeureFin2());
		$tperiode->setDimancheCapacite2((int)$oneHoraire->getDimancheCapacite2());
		$tperiode->setDimancheNbRdvSite2((int)$oneHoraire->getDimancheNbRdvSite2());
		
		return $tperiode;
	}
	
	public function modifierTraductionAgent($tAgent) {
		//Nom
		$tTraductionNom = $tAgent->getTTraductionRelatedByCodeNomUtilisateur();
		if($tTraductionNom instanceof TTraduction) {
			foreach($this->listeNomLangues->getItems() as $item) {
				$tTraductionLibelleQuery = new TTraductionLibelleQuery();
				$tTraductionLibelleNom = $tTraductionLibelleQuery->getTraductionLibelleById($tTraductionNom->getIdTraduction(), $item->langNom->Value);

				if($tTraductionLibelleNom instanceof TTraductionLibelle) {
					$tTraductionLibelleNom->setLibelle($item->nomRessource->getSafeText());
					$tTraductionLibelleNom->save();
				}
				else {//Si l'agent n'a pas de nom saisi dans une des langues
					$tTraductionLibelleNom = new TTraductionLibelle();
					$tTraductionLibelleNom->setIdTraduction($tTraductionNom->getIdTraduction());
					$tTraductionLibelleNom->setLang($item->langNom->Value);
					$tTraductionLibelleNom->setLibelle($item->nomRessource->getSafeText());
					$tTraductionLibelleNom->save();
				}
			}
		} else {
			//Si l'agent n'a pas de nom saisi dans aucune langue
			$tTraductionNom = new TTraduction();
			foreach($this->listeNomLangues->getItems() as $item) {
				$tTraductionLibelle = new TTraductionLibelle();
				$tTraductionLibelle->setLang($item->langNom->Value);
				$tTraductionLibelle->setLibelle($item->nomRessource->getSafeText());
				$tTraductionNom->addTTraductionLibelle($tTraductionLibelle);
			}
			$tAgent->setTTraductionRelatedByCodeNomUtilisateur($tTraductionNom);
		}

		//Preom
		$tTraductionPrenom = $tAgent->getTTraductionRelatedByCodePrenomUtilisateur();
		if($tTraductionPrenom instanceof TTraduction) {
			foreach($this->listePrenomLangues->getItems() as $item) {
				$tTraductionLibelleQuery = new TTraductionLibelleQuery();
				$tTraductionLibellePrenom = $tTraductionLibelleQuery->getTraductionLibelleById($tTraductionPrenom->getIdTraduction(), $item->langPrenom->Value);

				if($tTraductionLibellePrenom instanceof TTraductionLibelle) {
					$tTraductionLibellePrenom->setLibelle($item->prenomRessource->getSafeText());
					$tTraductionLibellePrenom->save();
				}
				else {//Si l'agent n'a pas de prenomnom saisi dans une des langues
					$tTraductionLibellePrenom = new TTraductionLibelle();
					$tTraductionLibellePrenom->setIdTraduction($tTraductionPrenom->getIdTraduction());
					$tTraductionLibellePrenom->setLang($item->langPrenom->Value);
					$tTraductionLibellePrenom->setLibelle($item->prenomRessource->getSafeText());
					$tTraductionLibellePrenom->save();
				}
			}
		} else {
			//Si l'agent n'a pas de prenom saisi
			$tTraductionPrenom = new TTraduction();
			foreach($this->listePrenomLangues->getItems() as $item) {
				$tTraductionLibelle = new TTraductionLibelle();
				$tTraductionLibelle->setLang($item->langPrenom->Value);
				$tTraductionLibelle->setLibelle($item->prenomRessource->getSafeText());
				$tTraductionPrenom->addTTraductionLibelle($tTraductionLibelle);
			}
			$tAgent->setTTraductionRelatedByCodePrenomUtilisateur($tTraductionPrenom);
		}

		//Code
		$tTraductionCode = $tAgent->getTTraductionRelatedByCodeUtilisateur();
		if($tTraductionCode instanceof TTraduction) {
			foreach($this->listeCodeLangues->getItems() as $item) {
				$tTraductionLibelleQuery = new TTraductionLibelleQuery();
				$tTraductionLibelleCode = $tTraductionLibelleQuery->getTraductionLibelleById($tTraductionCode->getIdTraduction(), $item->langCode->Value);

				if($tTraductionLibelleCode instanceof TTraductionLibelle) {
					$tTraductionLibelleCode->setLibelle($item->codeRessource->getSafeText());
					$tTraductionLibelleCode->save();
				}
				else {//Si l'agent n'a pas de codenom saisi dans une des langues
					$tTraductionLibelleCode = new TTraductionLibelle();
					$tTraductionLibelleCode->setIdTraduction($tTraductionCode->getIdTraduction());
					$tTraductionLibelleCode->setLang($item->langCode->Value);
					$tTraductionLibelleCode->setLibelle($item->codeRessource->getSafeText());
					$tTraductionLibelleCode->save();
				}
			}
		} else {
			//Si l'agent n'a pas de code saisi
			$tTraductionCode = new TTraduction();
			foreach($this->listeCodeLangues->getItems() as $item) {
				$tTraductionLibelle = new TTraductionLibelle();
				$tTraductionLibelle->setLang($item->langCode->Value);
				$tTraductionLibelle->setLibelle($item->codeRessource->getSafeText());
				$tTraductionCode->addTTraductionLibelle($tTraductionLibelle);
			}
			$tAgent->setTTraductionRelatedByCodeUtilisateur($tTraductionCode);
		}
	}

	public function remplirOld($idOldAgent){
		$tAgentQuery = new TAgentQuery();
		$tOldAgent = $tAgentQuery->getAgentById($idOldAgent);
		$this->ouiProfil->Checked = false;
        $this->nonProfil->Checked = true;
        $this->identifiant->setText(null);
        $this->emailUtilisateur->setText(null);
        $this->telUtilisateur->setText(null);

        if ($tOldAgent instanceof TAgent){
        	$this->organisationRessource->setSelectedValue($tOldAgent->getIdOrganisationAttache());
			if($tOldAgent->getIdOrganisationAttache()!=null) {
				//Remplir liste Etablissement
				$etablissements = new Atexo_Etablissement_Gestion();
				$this->etablissementRessource->DataSource = $etablissements->getEtablissementByIdProvinceIdOrganisation($this->_lang, $tOldAgent->getIdOrganisationAttache(), null, Prado::localize('SELECTIONNEZ'));
				$this->etablissementRessource->DataBind();
				$this->etablissementRessource->setSelectedValue($tOldAgent->getIdEtablissementAttache());
			}

			$prestationAssociees = array();
			$agendas = $tOldAgent->getTAgendas();
			foreach($agendas as $oneAgenda) {
				if($oneAgenda->getIdPrestation()>0) {
					$periodesAgenda = $oneAgenda->getTPeriodes();
					$tPrestationQuery = new TPrestationQuery();
					$prestation = $tPrestationQuery->getPrestationById($oneAgenda->getIdPrestation());
					$prestationAssociees[$oneAgenda->getIdPrestation()]['idTypePrestation'] = $prestation->getIdTypePrestation();
					$prestationAssociees[$oneAgenda->getIdPrestation()]['idPrestation'] = $oneAgenda->getIdPrestation();
					//$prestationAssociees[$oneAgenda->getIdPrestation()]['periodicite'] = $oneAgenda->getPeriodicite();
					$prestationAssociees[$oneAgenda->getIdPrestation()]['horaires'] = $this->contructListeHoraire($periodesAgenda, $prestation->getIdTypePrestation(), $oneAgenda->getIdPrestation());
				}
			}
			$this->nbrePrestationAttachees->Value = count($prestationAssociees);
			$this->Page->setViewState("prestationAssociees",$prestationAssociees);
			$this->listePrestationsAssociees->DataSource = $prestationAssociees ;
			$this->listePrestationsAssociees->DataBind();
			$this->PrestationAssocie->setStyle('display:block');
			
			$jourIndispo = $tOldAgent->getTPeriodeIndisponibilitesSupDateJour();
			
			if(count($jourIndispo)==0) {
				$this->indisponibilitePanel->Visible=false;
			}
			else {
				$this->indisponibilitePanel->Visible=true;
				$this->repeatIndisponbibilite->DataSource = $jourIndispo;
				$this->repeatIndisponbibilite->DataBind();
			}

			$this->non->Checked = true;
        }
	}


	public function remplir($idAgent) {
		$tAgentQuery = new TAgentQuery();
		$tAgent = $tAgentQuery->getAgentById($idAgent);
		if($tAgent->getLogin() != null){
            $this->identifiant->setText($tAgent->getLogin());
            $this->emailUtilisateur->setText($tAgent->getEmailUtilisateur());
            $this->telUtilisateur->setText($tAgent->getTelephoneUtilisateur());
            $this->ouiProfil->Checked = true;
            $this->nonProfil->Checked = false;
        }
		else{
            $this->ouiProfil->Checked = false;
            $this->nonProfil->Checked = true;
            $this->identifiant->setText(null);
            $this->emailUtilisateur->setText(null);
            $this->telUtilisateur->setText(null);
        }

		if ($tAgent instanceof TAgent){
			$this->organisationRessource->setSelectedValue($tAgent->getIdOrganisationAttache());
			if($tAgent->getIdOrganisationAttache()!=null) {
				//Remplir liste Etablissement
				$etablissements = new Atexo_Etablissement_Gestion();
				$this->etablissementRessource->DataSource = $etablissements->getEtablissementByIdProvinceIdOrganisation($this->_lang, $tAgent->getIdOrganisationAttache(), null, Prado::localize('SELECTIONNEZ'));
				$this->etablissementRessource->DataBind();
				$this->etablissementRessource->setSelectedValue($tAgent->getIdEtablissementAttache());
			}
			//recupérer les langues
			$langues[]= explode(",", Atexo_Config::getParameter("LANGUES_ACTIVES"));

			$index=0;
			foreach($langues[0] as $lan){
				$this->_dataNom[$index]['nomRessource'] = $tAgent->getNomUtilisateurTraduit($lan);
				$this->_dataNom[$index]['nomLibelleLang'] = Prado::localize('NOM');
				$this->_dataNom[$index]['lang'] = '('.Prado::localize('LANG_'.strtoupper($lan)).')';
				$this->_dataNom[$index]['langNom'] = $lan;
				$index++;
			}
			$index=0;
			foreach($langues[0] as $lan){
				$this->_dataPrenom[$index]['prenomRessource'] = $tAgent->getPrenomUtilisateurTraduit($lan);
				$this->_dataPrenom[$index]['prenomLibelleLang'] = Prado::localize('PRENOM');
				$this->_dataPrenom[$index]['lang'] = '('.Prado::localize('LANG_'.strtoupper($lan)).')';
				$this->_dataPrenom[$index]['langPrenom'] = $lan;
				$index++;
			}
			$index=0;
			foreach($langues[0] as $lan){
				$this->_dataCode[$index]['codeRessource'] = $tAgent->getCodeUtilisateurTraduit($lan);
				$this->_dataCode[$index]['codeLibelleLang'] = Prado::localize('CODE_RESSOURCE');
				$this->_dataCode[$index]['lang'] = '('.Prado::localize('LANG_'.strtoupper($lan)).')';
				$this->_dataCode[$index]['langCode'] = $lan;
				$index++;
			}
			if($tAgent->getActif()=="1") {
				$this->oui->Checked = true;
			}
			elseif($tAgent->getActif()=="2") {
				$this->visibleReaffectation->Checked = true;
			}else{
				$this->non->Checked = true;
			}


            $idProfil = $tAgent->getIdProfil();
            $tProfilQuery = new TProfilQuery();
            $profilGestion = new Atexo_Profil_Gestion();
            $profil = $tProfilQuery->getProfilById($idProfil);
            if($profil instanceof TProfil) {
                $idTypeProfil = $profil->getIdTypeProfil();
                $this->profilUtilisateur->DataSource = $profilGestion->getAllProfilByType($idTypeProfil, $this->_lang, Prado::localize('SELECTIONNEZ'));
                $this->profilUtilisateur->DataBind();
                $this->profilUtilisateur->setselectedValue($idProfil);

            }
			//les agendas
			$prestationAssociees = array();
			$agendas = $tAgent->getTAgendas();
			foreach($agendas as $oneAgenda) {
				if($oneAgenda->getIdPrestation()>0) {
					$periodesAgenda = $oneAgenda->getTPeriodes();
					$tPrestationQuery = new TPrestationQuery();
					$prestation = $tPrestationQuery->getPrestationById($oneAgenda->getIdPrestation());
					$prestationAssociees[$oneAgenda->getIdPrestation()]['idTypePrestation'] = $prestation->getIdTypePrestation();
					$prestationAssociees[$oneAgenda->getIdPrestation()]['idPrestation'] = $oneAgenda->getIdPrestation();
					//$prestationAssociees[$oneAgenda->getIdPrestation()]['periodicite'] = $oneAgenda->getPeriodicite();
					$prestationAssociees[$oneAgenda->getIdPrestation()]['horaires'] = $this->contructListeHoraire($periodesAgenda, $prestation->getIdTypePrestation(), $oneAgenda->getIdPrestation());
				}
			}
			$this->nbrePrestationAttachees->Value = count($prestationAssociees);
			$this->Page->setViewState("prestationAssociees",$prestationAssociees);
			$this->listePrestationsAssociees->DataSource = $prestationAssociees ;
			$this->listePrestationsAssociees->DataBind();
			$this->PrestationAssocie->setStyle('display:block');
			
			$jourIndispo = $tAgent->getTPeriodeIndisponibilitesSupDateJour();
			
			if(count($jourIndispo)==0) {
				$this->indisponibilitePanel->Visible=false;
			}
			else {
				$this->indisponibilitePanel->Visible=true;
				$this->repeatIndisponbibilite->DataSource = $jourIndispo;
				$this->repeatIndisponbibilite->DataBind();
			}
		}
	}

	public function contructListeHoraire($liste, $idTypePrest, $idPrest) {
		$horaires = array();
		foreach($liste as $one) {
			$prestationVo = new Atexo_Prestation_PrestationAssocieeVo();
			$prestationVo->setIdTypePrestation($idTypePrest);
			$prestationVo->setIdPrestation($idPrest);
			$prestationVo->setPeriodicite($one->getPeriodicite());
			$prestationVo->setDebutPeriode($one->getDebutPeriode("d/m/Y"));
			$prestationVo->setfinPeriode($one->getfinPeriode("d/m/Y"));
			$prestationVo->setLundiHeureDebut1($one->getLundiHeureDebut1());
			$prestationVo->setLundiHeureFin1($one->getLundiHeureFin1());
			$prestationVo->setLundiCapacite1((int)$one->getLundiCapacite1());
			$prestationVo->setLundiNbRdvSite1((int)$one->getLundiNbRdvSite1());
			$prestationVo->setLundiHeureDebut2($one->getLundiHeureDebut2());
			$prestationVo->setLundiHeureFin2($one->getLundiHeureFin2());
			$prestationVo->setLundiCapacite2((int)$one->getLundiCapacite2());
			$prestationVo->setLundiNbRdvSite2((int)$one->getLundiNbRdvSite2());
			$prestationVo->setMardiHeureDebut1($one->getMardiHeureDebut1());
			$prestationVo->setMardiHeureFin1($one->getMardiHeureFin1());
			$prestationVo->setMardiCapacite1((int)$one->getMardiCapacite1());
			$prestationVo->setMardiNbRdvSite1((int)$one->getMardiNbRdvSite1());
			$prestationVo->setMardiHeureDebut2($one->getMardiHeureDebut2());
			$prestationVo->setMardiHeureFin2($one->getMardiHeureFin2());
			$prestationVo->setMardiCapacite2((int)$one->getMardiCapacite2());
			$prestationVo->setMardiNbRdvSite2((int)$one->getMardiNbRdvSite2());
			$prestationVo->setMercrediHeureDebut1($one->getMercrediHeureDebut1());
			$prestationVo->setMercrediHeureFin1($one->getMercrediHeureFin1());
			$prestationVo->setMercrediCapacite1((int)$one->getMercrediCapacite1());
			$prestationVo->setMercrediNbRdvSite1((int)$one->getMercrediNbRdvSite1());
			$prestationVo->setMercrediHeureDebut2($one->getMercrediHeureDebut2());
			$prestationVo->setMercrediHeureFin2($one->getMercrediHeureFin2());
			$prestationVo->setMercrediCapacite2((int)$one->getMercrediCapacite2());
			$prestationVo->setMercrediNbRdvSite2((int)$one->getMercrediNbRdvSite2());
			$prestationVo->setJeudiHeureDebut1($one->getJeudiHeureDebut1());
			$prestationVo->setJeudiHeureFin1($one->getJeudiHeureFin1());
			$prestationVo->setJeudiCapacite1((int)$one->getJeudiCapacite1());
			$prestationVo->setJeudiNbRdvSite1((int)$one->getJeudiNbRdvSite1());
			$prestationVo->setJeudiHeureDebut2($one->getJeudiHeureDebut2());
			$prestationVo->setJeudiHeureFin2($one->getJeudiHeureFin2());
			$prestationVo->setJeudiCapacite2((int)$one->getJeudiCapacite2());
			$prestationVo->setJeudiNbRdvSite2((int)$one->getJeudiNbRdvSite2());
			$prestationVo->setVendrediHeureDebut1($one->getVendrediHeureDebut1());
			$prestationVo->setVendrediHeureFin1($one->getVendrediHeureFin1());
			$prestationVo->setVendrediCapacite1((int)$one->getVendrediCapacite1());
			$prestationVo->setVendrediNbRdvSite1((int)$one->getVendrediNbRdvSite1());
			$prestationVo->setVendrediHeureDebut2($one->getVendrediHeureDebut2());
			$prestationVo->setVendrediHeureFin2($one->getVendrediHeureFin2());
			$prestationVo->setVendrediCapacite2((int)$one->getVendrediCapacite2());
			$prestationVo->setVendrediNbRdvSite2((int)$one->getVendrediNbRdvSite2());
			$prestationVo->setSamediHeureDebut1($one->getSamediHeureDebut1());
			$prestationVo->setSamediHeureFin1($one->getSamediHeureFin1());
			$prestationVo->setSamediCapacite1((int)$one->getSamediCapacite1());
			$prestationVo->setSamediNbRdvSite1((int)$one->getSamediNbRdvSite1());
			$prestationVo->setSamediHeureDebut2($one->getSamediHeureDebut2());
			$prestationVo->setSamediHeureFin2($one->getSamediHeureFin2());
			$prestationVo->setSamediCapacite2((int)$one->getSamediCapacite2());
			$prestationVo->setSamediNbRdvSite2((int)$one->getSamediNbRdvSite2());
			$prestationVo->setDimancheHeureDebut1($one->getDimancheHeureDebut1());
			$prestationVo->setDimancheHeureFin1($one->getDimancheHeureFin1());
			$prestationVo->setDimancheCapacite1((int)$one->getDimancheCapacite1());
			$prestationVo->setDimancheNbRdvSite1((int)$one->getDimancheNbRdvSite1());
			$prestationVo->setDimancheHeureDebut2($one->getDimancheHeureDebut2());
			$prestationVo->setDimancheHeureFin2($one->getDimancheHeureFin2());
			$prestationVo->setDimancheCapacite2((int)$one->getDimancheCapacite2());
			$prestationVo->setDimancheNbRdvSite2((int)$one->getDimancheNbRdvSite2());
			$horaires[] = $prestationVo;
		}
		return $horaires;
	}

	public function showPanelProfil(){
	    if($this->ouiProfil->Checked == true){
	        $this->panelProfil->Visible = true;
        }
	    if($this->nonProfil->Checked == true){
            $this->panelProfil->Visible = false;
        }

    }
}
