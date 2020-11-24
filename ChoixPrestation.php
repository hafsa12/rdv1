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
class ChoixPrestation extends AtexoPage
{
	protected $visibleOu = true;

    public function initialize() {
		$this->Page->Master->setShowPicker(false);
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$this->Page->Master->setPrendreRdv(false);
    	if(true || $this->Page->Master->isCalledFromAdmin()) {
    		$this->panelOu->Visible=false;
    	}
    	else {
	    	$entiteGestion = new Atexo_Entite_Gestion();
	    	$this->entite1->DataSource = $entiteGestion->getAllEntite(1,$lang,null,Prado::localize('SELECTIONNEZ'));
	    	$this->entite1->DataBind();
    	}
		$this->panelEntite3->Visible=false;
		$this->setViewState("etablissementVisio", null);
		$this->typeRdv->DataSource = array(
										'0' => Prado::localize('SELECTIONNEZ'),
										'1' => Prado::localize('Sur place'),
										'2' => Prado::localize('Teleaccueil'),
									 );
		$this->typeRdv->DataBind();

		$c = new Criteria();
        $c->add(TPrestationPeer:: VISIOCONFERENCE, '1');
	$c->addJoin(TPrestationPeer::ID_TYPE_PRESTATION, TTypePrestationPeer::ID_TYPE_PRESTATION);
        $c->addJoin(TTypePrestationPeer::ID_ETABLISSEMENT, TEtablissementPeer::ID_ETABLISSEMENT);
        $c->add(TEtablissementPeer::ID_ORGANISATION, $this->User->getCurrentOrganism());
        $res = TPrestationPeer::doSelect($c);

        $etabVisio = null;
        $numOfRes = count($res);
		if($numOfRes > 0){
        	foreach ($res as $prestationVisio) {
        		$typePrestationVisio = TTypePrestationPeer::retrieveByPk($prestationVisio->getIdTypePrestation());
        		if($typePrestationVisio->getVisibleCitoyen()){
        			$etab = TEtablissementPeer::retrieveByPk($typePrestationVisio->getIdEtablissement());
				if($this->Page->Master->isCalledFromAdmin () || $etab->getActive()=='1'){
                        		$etabVisio = $etab;	
					break;
				}
        		}
        		else{
        			continue;
        		}
        	}
		}
        if($etabVisio){
        	$this->setViewState("etablissementVisio", $etabVisio);
        	if($numOfRes>1){
        		$etabGestion = new Atexo_Etablissement_Gestion();
                $dataEtab = $etabGestion->getEtablissementByIdProvinceIdOrganisation ( $lang, $this->User->getCurrentOrganism (),
				null, Prado::localize ( 'SELECTIONNEZ' ), $this->Page->Master->isCalledFromAdmin (), !$this->Page->Master->isCalledFromAdmin () );
                if(count($dataEtab)>2) {
        		  $this->panelTypeRdv->Visible = true;
        		  $this->panelEntite->Visible = false;
                }
                else {
                    $this->typeRdv->SelectedValue = 2;
                    $this->loadTypeEtablissement();
                    $this->panelTypeRdv->Visible = false;
                }
		}
        	else{
        		$this->typeRdv->SelectedValue = 2;
        		$this->loadTypeEtablissement();
        		$this->panelTypeRdv->Visible = false;
        	}
        }else{
        	$this->panelTypeRdv->Visible = false;
        	$this->loadEtablissement(false);
        }
    }

    public function loadTypeEtablissement(){
    	$etabVisio = $this->getViewState("etablissementVisio");
    	$typeRdv = $this->typeRdv->SelectedValue;
    	if($typeRdv == 2){
			$this->viderEtablissement();
    		$this->loadEtablissement(false);
    		$this->panelEntite->Visible = false;
    		$this->etablissement->SelectedValue = $etabVisio->getIdEtablissement();
    		$this->loadTypePrestation();
    	}elseif ($typeRdv == 1) {
    		$this->viderEtablissement();
    		$this->loadEtablissement(false,$etabVisio);
    		$this->panelEntite->Visible = true;
    	}
    }

	public function setRdv(TRendezVous $rdv) {
		$this->etablissement->SelectedValue = $rdv->getIdEtablissement();
		$this->loadTypePrestation();
		$this->typePrestation->SelectedValue = $rdv->getTPrestation()->getIdTypePrestation();
		$this->loadPrestation();
		$this->prestation->SelectedValue = $rdv->getIdPrestation();
		$this->loadRessource(null,null);
		$this->ressource->SelectedValue = $rdv->getIdAgentRessource();
	}
    
	public function getTypePrestation() {
    	return $this->typePrestation->SelectedItem->Text;
    }
    
	public function getIdEtablissement() {
    	return $this->etablissement->SelectedValue;
    }
    
    public function getIdPrestation() {
    	return $this->prestation->SelectedValue;
    }
    
	public function getPrestation() {
    	return $this->prestation->SelectedItem->Text;
    }
    
    public function getIdRessource() {
    	return ($this->ressource->SelectedValue>0)?$this->ressource->SelectedValue:null;
    }
    
	public function getRessource() {
    	return ($this->ressource->SelectedValue>0)?$this->ressource->SelectedItem->Text:null;
    }

	public function getIdReferent() {
		return ($this->referent->SelectedValue>0)?$this->referent->SelectedValue:null;
	}

	public function getReferent() {
		return ($this->referent->SelectedValue>0)?$this->referent->SelectedItem->Text:null;
	}
    
	public function loadEntite2($sender = null) {
    	$lang = Atexo_User_CurrentUser::readFromSession("lang");
    	$entiteGestion = new Atexo_Entite_Gestion();
        if($this->entite1->SelectedValue) {
            $idEntite = $this->entite1->SelectedValue;
        }else{
            $idEntite = -1;
        }
    	$this->entite2->DataSource = $entiteGestion->getAllEntite(2,$lang,$idEntite,Prado::localize('SELECTIONNEZ'));
        $this->entite2->DataBind();

    	$this->entite3->DataSource = array();
        $this->entite3->DataBind();

    	$this->msgEtab->Text="";

        if($sender) {
            $this->motsCles->Text = "";
            $this->loadEntite3();
			$this->loadEtablissement(false);
		}
    }
    
	public function loadEntite3($sender = null) {
    	$lang = Atexo_User_CurrentUser::readFromSession("lang");
    	$entiteGestion = new Atexo_Entite_Gestion();

        if($this->entite2->SelectedValue) {
            $idEntite = $this->entite2->SelectedValue;
        }elseif($this->entite1->SelectedValue){
            $idEntite = $entiteGestion->getAllIdChildEntite($this->entite1->SelectedValue);
        }else{
            $idEntite = -1;
        }

        $this->entite3->DataSource = $entiteGestion->getAllEntite(3,$lang,$idEntite,Prado::localize('SELECTIONNEZ'));
    	$this->entite3->DataBind();

        $this->msgEtab->Text="";
		if($sender) {
    		$this->motsCles->Text = "";
            $this->loadEtablissement(false);
    	}
		$this->script->Text = "<script>J('.chosen-select').trigger('chosen:updated');</script>";
    }

	public function loadEtablissement($viderMotCle=true, $eliminatedEtab=null) {
        $script = "J('#".$this->etablissement->ClientId."').on('chosen:ready', function(evt, params) {
    J('#".$this->etablissement->ClientId."').trigger('chosen:activate');
  });";
    	$lang = Atexo_User_CurrentUser::readFromSession("lang");
		if($this->entite1->SelectedValue) {
			$entiteGestion = new Atexo_Entite_Gestion();
			$idsCommune = $entiteGestion->getAllIdChildEntite ($this->entite1->SelectedValue);
		}
		if($this->entite2->SelectedValue) {
			$entiteGestion = new Atexo_Entite_Gestion();
			$idsCommune = $entiteGestion->getAllIdChildEntite ($this->entite2->SelectedValue);
		}
		if($this->entite3->getSelectedValue ()) {
			$idsCommune = $this->entite3->getSelectedValue ();
		}
        if($idsCommune || $this->Page->Master->isCalledFromAdmin()) {
            $etabGestion = new Atexo_Etablissement_Gestion();
            $data = $etabGestion->getEtablissementByIdProvinceIdOrganisation ( $lang, $this->User->getCurrentOrganism (),
                $idsCommune, Prado::localize ( 'SELECTIONNEZ' ), $this->Page->Master->isCalledFromAdmin (), !$this->Page->Master->isCalledFromAdmin () );
        }else{
			$etabGestion = new Atexo_Etablissement_Gestion();
			$data = $etabGestion->getEtablissementByIdProvinceIdOrganisation ( $lang, $this->User->getCurrentOrganism (),
				null, Prado::localize ( 'SELECTIONNEZ' ), $this->Page->Master->isCalledFromAdmin (), !$this->Page->Master->isCalledFromAdmin () );
            //$data = array("-1" => Prado::localize ( 'SELECTIONNEZ' ));
        }
        if($eliminatedEtab){
			if(count($data)>1){
				foreach ($data as $key => $etab) {
					if($key == $eliminatedEtab->getIdEtablissement()){
						unset($data[$key]);
					}
				}
			}
		}
    	$this->etablissement->DataSource = $data;
    	$this->etablissement->DataBind();
		$this->msgEtab->Text="";
		$this->setViewState('etabData',$data);

		
    	if(count($data)==2) {
    		$keys = array_keys($data);
    		$this->etablissement->SelectedValue = $keys[1];
    		$this->visibleOu = false;
    		$this->panelEntite->Visible = false;
    		$this->Page->gestionRdv->unEtab->Value = true;
            $script = "J('#".$this->plutot->ClientId."').focus();";
    		$this->loadTypePrestation();
			if($this->Page->Master->isCalledFromAdmin()) {
				$this->visibleOu = false;
			}
    	}
    	else {
    		$this->viderTypePrestation();
    		$this->panelEntite->Visible = true;
    		$this->Page->gestionRdv->unEtab->Value = false;
    	}

		if($viderMotCle) {
    		$this->motsCles->Text = "";
    	}
		$this->script->Text = "<script>J('.chosen-select').trigger('chosen:updated');".$script."</script>";
    }
    
	public function loadTypePrestation() {
    	$lang = Atexo_User_CurrentUser::readFromSession("lang");
    	$presGestion = new Atexo_TypePrestation_Gestion();
    	$data = $presGestion->getTypePrestationByIdEtab($lang, $this->etablissement->SelectedValue, Prado::localize('SELECTIONNEZ'), !$this->Page->Master->isCalledFromAdmin(),true, true);
    	
    	$this->typePrestation->DataSource = $data;
    	$this->typePrestation->DataBind();

    	$disable = false;
		if(count($data)==2) {
    		$keys = array_keys($data);
    		$this->typePrestation->SelectedValue = $keys[1];
    		$disable = true;
            $this->loadPrestation();
    	}
    	else {
    		$disable = false;
    		$this->viderPrestation();
    	}
    	$tEtabQuery = new TEtablissementQuery();
		$tEtablissement = $tEtabQuery->getEtablissementById($this->etablissement->SelectedValue);
		if(isset($tEtablissement) && !$this->Page->Master->isCalledFromAdmin()) {
			$this->msgEtab->Text="<div class='alert alert-info'>".Prado::localize('TEXT_TELEPHONE_PRISE_RDV').' <span dir="ltr"><strong>'.$tEtablissement->getTelephoneRdv().'</strong></span></div>';
		}
		$this->script->Text = "<script>J('#".$this->plutot->ClientId."').focus();J('.chosen-select').trigger('chosen:updated');</script>";
		$this->scriptDisable->Text = "<script>J('#".$this->typePrestation->ClientId."').prop('disabled', ".$disable.").trigger('chosen:updated');console.log('disabled');</script>";
	
    }
    
	public function loadPrestation() {
        $script = "J('#" . $this->prestation->ClientId . "').trigger('chosen:activate');";
        $lang = Atexo_User_CurrentUser::readFromSession ( "lang" );
        $idOrg = $this->User->getCurrentOrganism ();
        if ( $idOrg > 0 ) {
            $tOrganisationQuery = new TOrganisationQuery();
            $tOrganisation = $tOrganisationQuery->getOrganisationById ( $idOrg );
            $typePrestation = $tOrganisation->getTypePrestation ();
        } else {
            $typePrestation = Atexo_Config::getParameter ( 'PRESTATION_SAISIE_LIBRE' );
        }
        $presGestion = new Atexo_Prestation_Gestion();
        if($this->typePrestation->SelectedValue>0)
		$data = $presGestion->getPrestationByIdTypePrestation ( $lang, $this->typePrestation->SelectedValue, $typePrestation, Prado::localize ( 'SELECTIONNEZ' ), true );
	else
		$data = array();
	$this->prestation->DataSource = $data;
        $this->prestation->DataBind ();
        if ( count ( $data ) == 2 ) {
            $keys = array_keys ( $data );
            $this->prestation->SelectedValue = $keys[ 1 ];
            $script = "J('#" . $this->ressource->ClientId . "').focus();";
			$script .= $this->loadRessource ( null, null );
        } else {
            $this->viderRessource ();
        }
        $this->script->Text = "<script>" . $script . "J('.chosen-select').trigger('chosen:updated');</script>";
        return $script;
    }
    
	public function loadRessource($sender,$param) {
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
        $script = "J('#".$this->ressource->ClientId."').focus();";
		$tPrestationQuery = new TPrestationQuery();
		$tPrestation = $tPrestationQuery->getPrestationById($this->prestation->SelectedValue);
		if(!isset($tPrestation)) {
                        return;
                }
		if($_SESSION['typePrestation'] && $tPrestation->getIdRefPrestation() && $tPrestation->getTRefPrestation()->getIdParametreForm()) {
			$this->loadPrestaForm($tPrestation);
		}
		else {
			$this->champsSupPresta->DataSource = array();
                        $this->champsSupPresta->DataBind();
		}
		$this->aideBloc->Style="display:none";
		$tParametrage = $tPrestation->getTParametragePrestation();
		if(isset($tParametrage) && $tParametrage->getCodeAide()>0) {
			$aide = str_replace("\r\n","<br>",str_replace("'","&apos;",$tPrestation->getTParametragePrestation()->getAideTraduit($lang)));
			$script .= "J('#".$this->aideBloc->ClientId."').attr('data-original-title', '".$aide."').tooltip('fixTitle');";
			if($aide!="") {
				$this->aideBloc->Style="display:";
				$this->aideBloc->Attributes["data-placement"] = "bottom";
				$this->aideBloc->Attributes["data-html"] = "true";
				$this->aideBloc->Attributes["title"] = $aide;
				$this->aideBloc->Attributes["href"] = "javascript:void(0);";
				$this->aideBloc->CssClass="info-bulle";
				$this->aideBloc->Attributes["onload"] = "bottom";
			}
		}

		if(!$this->Page->Master->isCalledFromAdmin() && $tPrestation->getRessourceVisible()=="0") {
			$this->panelRessource->Style = "display:none";
			$this->validateurRessource->Visible = false;
			$this->validateurRessourceEtendue->Visible = false;
			return;
		}
		if(!$this->Page->Master->isCalledFromAdmin() && $tPrestation->getReferentVisible()=="0") {
			$this->panelReferent->Style = "display:none";
		}
    	$lang = Atexo_User_CurrentUser::readFromSession("lang");
    	$ressourceGestion = new Atexo_Agent_Gestion();

		if(!$this->Page->Master->isCalledFromAdmin()) {
			$data = $ressourceGestion->getRessourceByIdPrestation($lang, $this->prestation->SelectedValue, Prado::localize('SELECTIONNEZ'), true, $_SESSION['typeRessource']);
		}
		else {
			$data = $ressourceGestion->getRessourceByIdPrestation($lang, $this->prestation->SelectedValue, Prado::localize('SELECTIONNEZ'), true);
		}

    	$this->ressource->DataSource = $data;
    	$this->ressource->DataBind();
    	if(count($data)>1) {
    		$this->panelRessource->Style = "display:";
	    	if(count($data)==2) {
	    		$keys = array_keys($data);
	    		$this->ressource->SelectedValue = $keys[1];
                $script = "J('#".$this->TemplateControl->etapeSuivante->ClientId."').focus();";
	    	}
    	}
    	else {
    		$this->panelRessource->Style = "display:none";
    		$this->validateurRessource->Visible = false;
    		$this->validateurRessourceEtendue->Visible = false;
    	}
    	
		if($tPrestation->getRessourceObligatoire()=="0") {
			$this->validateurRessource->Visible = false;
		}
		else {
			$this->validateurRessource->Visible = true;
		}
		if($param!=null) {
			$this->panelRessource->render($param->NewWriter);
		}
		if($this->etablissement->SelectedValue>0) {
		$tEtabQuery = new TEtablissementQuery();
		$tEtablissement = $tEtabQuery->getEtablissementById($this->etablissement->SelectedValue);

		$referentGestion = new Atexo_Referent_Gestion();
		$data = $referentGestion->getReferentByIdEntite($lang, $tEtablissement->getIdEntite(), Prado::localize('SELECTIONNEZ'));
		$this->referent->DataSource = $data;
		$this->referent->DataBind();
		if(count($data)>1) {
			$this->panelReferent->Style = "display:";
            $script = "J('#".$this->referent->ClientId."').focus();";
			if(count($data)==2) {
				$keys = array_keys($data);
				$this->referent->SelectedValue = $keys[1];
                $script = "J('#".$this->TemplateControl->etapeSuivante->ClientId."').focus();";
			}
		}
		else {
			$this->panelReferent->Style = "display:none";
		}
		}
		$this->script->Text = "<script>".$script."J('.chosen-select').trigger('chosen:updated');</script>";
        return $script;
    }

	public function loadPrestaForm(TPrestation $tPrestation) {
		$i=0;
		$data = array();
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$otherLang=($lang=="fr")?"ar":"fr";
		if($tPrestation->getIdChampsSupp1()>0) {
			$tChampsSupp = $tPrestation->getTChampsSuppRelatedByIdChampsSupp1();
			$data[$i]["libelle"]=$tChampsSupp->getLibelleChampsSuppTraduit($lang);
			$data[$i]["libelle_fr"]=$tChampsSupp->getLibelleChampsSuppTraduit("fr");
			$data[$i]["libelle_ar"]=$tChampsSupp->getLibelleChampsSuppTraduit("ar");
			$data[$i]["obligatoire"]=$tChampsSupp->getObligatoire();
			$data[$i]["typeChamp"]=$tChampsSupp->getChampsType();
			if($tChampsSupp->getObligatoire()){
				$data[$i]["labelObligatoire"] = '<i class="required">*</i>';
			}else{
				$data[$i]["labelObligatoire"] = "";
			}
			$liste = json_decode($tChampsSupp->getValueListe(), true);
			$liste[$lang] = array_filter(explode("#",$liste[$lang]));
			$liste[$otherLang] = array_filter(explode("#",$liste[$otherLang]));
			$j=0;
			if($data[$i]["typeChamp"]=="LISTE") {
				$dataSource = array(" "=>Prado :: Localize("SELECTIONNEZ"));
				foreach($liste[$lang] as $value) {
					$dataSource[$liste[$otherLang][$j]]=$value;
					$j++;
				}
			}
			if($data[$i]["typeChamp"]=="MULTICHOIX") {
				$dataSource = array();
				foreach($liste[$lang] as $value) {
					$dataSource[$j]["value"]=$liste[$otherLang][$j];
					$dataSource[$j]["text"]=$value;
					$j++;
				}
			}

			$data[$i]["listeValues"]=$dataSource;
			$i++;
		}

		if($tPrestation->getIdChampsSupp2()>0) {
			$tChampsSupp = $tPrestation->getTChampsSuppRelatedByIdChampsSupp2();
			$data[$i]["libelle"]=$tChampsSupp->getLibelleChampsSuppTraduit($lang);
			$data[$i]["libelle_fr"]=$tChampsSupp->getLibelleChampsSuppTraduit("fr");
			$data[$i]["libelle_ar"]=$tChampsSupp->getLibelleChampsSuppTraduit("ar");
			$data[$i]["obligatoire"]=$tChampsSupp->getObligatoire();
			$data[$i]["typeChamp"]=$tChampsSupp->getChampsType();
			$liste = json_decode($tChampsSupp->getValueListe(), true);
			$liste[$lang] = array_filter(explode("#",$liste[$lang]));
			$liste[$otherLang] = array_filter(explode("#",$liste[$otherLang]));
			if($tChampsSupp->getObligatoire()){
				$data[$i]["labelObligatoire"] = '<i class="required">*</i>';
			}else{
				$data[$i]["labelObligatoire"] = "";
			}
			$j=0;
			if($data[$i]["typeChamp"]=="LISTE") {
				$dataSource = array(" "=>Prado :: Localize("SELECTIONNEZ"));
				foreach($liste[$lang] as $value) {
					$dataSource[$liste[$otherLang][$j]]=$value;
					$j++;
				}
			}
			if($data[$i]["typeChamp"]=="MULTICHOIX") {
				$dataSource = array();
				foreach($liste[$lang] as $value) {
					$dataSource[$j]["value"]=$liste[$otherLang][$j];
					$dataSource[$j]["text"]=$value;
					$j++;
				}
			}

			$data[$i]["listeValues"]=$dataSource;
			$i++;
		}
		if($tPrestation->getIdChampsSupp3()>0) {
			$tChampsSupp = $tPrestation->getTChampsSuppRelatedByIdChampsSupp3();
			$data[$i]["libelle"]=$tChampsSupp->getLibelleChampsSuppTraduit($lang);
			$data[$i]["libelle_fr"]=$tChampsSupp->getLibelleChampsSuppTraduit("fr");
			$data[$i]["libelle_ar"]=$tChampsSupp->getLibelleChampsSuppTraduit("ar");
			$data[$i]["obligatoire"]=$tChampsSupp->getObligatoire();
			$data[$i]["typeChamp"]=$tChampsSupp->getChampsType();
			$liste = json_decode($tChampsSupp->getValueListe(), true);
			$liste[$lang] = array_filter(explode("#",$liste[$lang]));
			$liste[$otherLang] = array_filter(explode("#",$liste[$otherLang]));
			if($tChampsSupp->getObligatoire()){
				$data[$i]["labelObligatoire"] = '<i class="required">*</i>';
			}else{
				$data[$i]["labelObligatoire"] = "";
			}
			$j=0;
			if($data[$i]["typeChamp"]=="LISTE") {
				$dataSource = array(" "=>Prado :: Localize("SELECTIONNEZ"));
				foreach($liste[$lang] as $value) {
					$dataSource[$liste[$otherLang][$j]]=$value;
					$j++;
				}
			}
			if($data[$i]["typeChamp"]=="MULTICHOIX") {
				$dataSource = array();
				foreach($liste[$lang] as $value) {
					$dataSource[$j]["value"]=$liste[$otherLang][$j];
					$dataSource[$j]["text"]=$value;
					$j++;
				}
			}

			$data[$i]["listeValues"]=$dataSource;
			$i++;
		}//print_r($data);exit;
		$this->champsSupPresta->DataSource = $data;
		$this->champsSupPresta->DataBind();
	}
    
    public function viderEtablissement() {
    	$this->etablissement->DataSource = array();
    	$this->etablissement->DataBind();
    	$this->viderTypePrestation();
    }
    
	public function viderTypePrestation() {
    	$this->typePrestation->DataSource = array();
    	$this->typePrestation->DataBind();
    	$this->scriptDisable->Text = "<script>J('#".$this->typePrestation->ClientId."').prop('disabled', false).trigger('chosen:updated');console.log('disabled');</script>";
    	$this->viderPrestation();
    }
    
	public function viderPrestation() {
    	$this->prestation->DataSource = array();
    	$this->prestation->DataBind();
    	$this->viderRessource();
    }
    
	public function viderRessource() {
		$this->panelRessource->Style = "display:none";
    	$this->ressource->DataSource = array();
    	$this->ressource->DataBind();
		$this->champsSupPresta->DataSource = array();
		$this->champsSupPresta->DataBind();
    }
    
	protected function suggestNames($sender,$param) {
        // Get the token
        $token=$param->getToken();
        // Sender is the Suggestions repeater
        $lang = Atexo_User_CurrentUser::readFromSession("lang");
        $tEtabPeer = new TEtablissementPeer();
        $sender->DataSource=$tEtabPeer->getEtablissementByMotsCle($this->User->getCurrentOrganism(), $token, $lang, !$this->Page->Master->isCalledFromAdmin());
        $sender->dataBind();
    }
    
	public function suggestionSelected($sender,$param) {
		if(count($sender->Suggestions->DataKeys)>0 && isset($sender->Suggestions->DataKeys[ $param->selectedIndex ])) {
			$idEtab = $sender->Suggestions->DataKeys[ $param->selectedIndex ];
			$this->loadFromEtab($idEtab);
		}
	}
	
	public function loadFromEtab($idEtab) {
		$tEtabQuery = new TEtablissementQuery();
		$tEtablissement = $tEtabQuery->getEtablissementById($idEtab);
		$entite = $tEtablissement->getTEntite();
		$entiteParent = $entite->getTEntiteRelatedByIdEntiteParent();
		$this->entite1->SelectedValue = $entiteParent->getIdEntiteParent();
		$this->loadEntite2();
		$this->entite2->SelectedValue = $entiteParent->getIdEntite();
		$this->loadEntite3();
		$this->entite3->SelectedValue = $tEtablissement->getIdEntite();
		$this->loadEtablissement(false);
		$this->etablissement->SelectedValue = $idEtab;
		$this->loadTypePrestation();
		$this->msgEtab->Text="<div class='alert alert-info'>".Prado::localize('TEXT_TELEPHONE_PRISE_RDV').' <span dir="ltr"><strong>'.$tEtablissement->getTelephoneRdv().'</strong></span></div>';
	}
	
	protected function suggestNamesRessources($sender,$param) {
        // Get the token
        $token=$param->getToken();
        // Sender is the Suggestions repeater
        $lang = Atexo_User_CurrentUser::readFromSession("lang");
        $tAgentPeer = new TAgentPeer();
        $sender->DataSource=$tAgentPeer->getRessourcesByMotsCle($this->etablissement->SelectedValue, $token, $lang, $_SESSION['typePrestation'],null,"1");
        $sender->dataBind();
    }
    
	public function suggestionRessourcesSelected($sender,$param) {
		if(count($sender->Suggestions->DataKeys)>$param->selectedIndex) {
			$ids = $sender->Suggestions->DataKeys[$param->selectedIndex];
			list($idRessource, $idPrestation, $idTypePrestation) = split("-", $ids);
			$this->typePrestation->SelectedValue = $idTypePrestation;
			$this->loadPrestation();
			$this->prestation->SelectedValue = $idPrestation;
			$this->loadRessource($sender, $param);
			$this->ressource->SelectedValue = $idRessource;
            $this->script->Text = "<script>J('#".$this->TemplateControl->etapeSuivante->ClientId."').focus();J('.chosen-select').trigger('chosen:updated');</script>";
		}
	}
	
	public function isRecherche() {
		return ($this->Page->Master->isCalledFromAdmin() && $this->recherche->Checked) || !$this->Page->Master->isCalledFromAdmin();
	}
	
	public function isRechercheEtendu() {
		return ($this->Page->Master->isCalledFromAdmin() && Atexo_User_CurrentUser::hasHabilitation('RechercheEtendueRdv') && $this->rechercheEtendu->Checked);
	}

	public function getNbJours() {
		if($this->dansXJours->Checked) {
			return intval($this->numberJours->Text);
		}
		if($this->rdvXJours->Checked) {
			return count(Atexo_Utils_Util::getJoursEntreDate(date('Y-m-d'),Atexo_Utils_Util::frnDate2iso($this->intervalXRdvJours->Text)))+1;
		}
		return 0;
	}

	public function getNbMois() {
		if($this->dansXMois->Checked) {
			return intval($this->numberMois->Text);
		}
		return 0;
	}

	public function getNbRdvGroupe() {
		if($this->rdvXJours->Checked && intval($this->nbRdvGrp->Text)>0) {
			return intval($this->nbRdvGrp->Text);
		}
		return 0;
	}

	public function getIntervalJour() {
		if($this->rdvXJours->Checked && intval($this->intervalJours->Text)>0) {
			return intval($this->intervalJours->Text);
		}
		return 0;
	}

	public function getValuePrestaForm() {
		return $this->getViewState("ValuePrestaForm");
	}

	public function saveValuePrestaForm() {
		$lang = Atexo_User_CurrentUser::readFromSession("lang");
		$otherLang=($lang=="fr")?"ar":"fr";
		$data = array();
		$i=0;
		foreach($this->champsSupPresta->Items as $item){
			$libelleLang = "libelle".$lang;
			$libelleOtherLang = "libelle".$otherLang;
			$data[$lang][$i] = $item->$libelleLang->Value." : ";
			$data[$otherLang][$i] = $item->$libelleOtherLang->Value." : ";
			if($item->typeText->Visible) {
				$data[$lang][$i] .= strip_tags($item->text->SafeText);
				$data[$otherLang][$i] .= strip_tags($item->text->SafeText);
			}
			if($item->typeList->Visible) {
				$data[$lang][$i] .= $item->list->SelectedItem->Text;
				$data[$otherLang][$i] .= $item->list->SelectedValue;
			}
			if($item->typeMultiChoix->Visible) {
				$checksValues=array();
				$j=0;
				foreach($item->listCheck->Items as $itemCheck){
					if($itemCheck->check->Checked) {
						$checksValues[$lang][$j] = $itemCheck->check->Text;
						$checksValues[$otherLang][$j] = $itemCheck->check->Value;
						$j++;
					}
				}
				$data[$lang][$i] .= implode(", ",$checksValues[$lang]);
				$data[$otherLang][$i] .= implode(", ",$checksValues[$otherLang]);
			}
			$i++;
		}
		$this->setViewState("ValuePrestaForm",$data);
	}

	public function setVisibleOuFalse(){
        $etabData = $this->getViewState('etabData');
        if(count($etabData)==2){
            $this->visibleOu = false;
        }
    }
}
