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
class Atexo_Etablissement_Gestion {

	
	/**
	 * retourne un tableau des établissement de la table "TEtablissement"
	 * @param $lang, $idOrganisation : id de l'organisation, $idEntite: id de l'entite, $valeurPraDefaut : gestion de mot selectionnez, 
	 * $onlyGere : true pour avoir juste l'établissement géré, $onlyActive : true pour avoir juste les établissements actifs 
	 * @return : tableau d'établissements la table "TEtablissement"
	 */
	public function getEtablissementByIdProvinceIdOrganisation($lang, $idOrganisation, $idEntite=null, $valeurParDefaur = false, $onlyGere=false, $onlyActive=false, $idsEntite=null){
		$arrayEtab = array();
		$connexion = Propel :: getConnection(Atexo_Config::getParameter("DB_NAME").Atexo_Config::getParameter("CONST_READ_ONLY"));
		$c = new Criteria();
		if($idEntite) {
			if(is_array($idEntite)) {
				$c->add ( TEtablissementPeer::ID_ENTITE, $idEntite, Criteria::IN );
			}
			else{
				$c->add ( TEtablissementPeer::ID_ENTITE, $idEntite);
			}
		}
		if($idOrganisation>0) {
			$c->add(TEtablissementPeer::ID_ORGANISATION,$idOrganisation);
		}
		
		if($onlyGere && (Atexo_User_CurrentUser::isAdminEtab() || Atexo_User_CurrentUser::isAdminOrgWithEtab())) {
			$c->add(TEtablissementPeer::ID_ETABLISSEMENT,explode(",",Atexo_User_CurrentUser::getIdEtablissementGere()), Criteria::IN);
		}
		if($onlyActive) {

			$c->add(TEtablissementPeer::ACTIVE,"1");
		}
		if(is_array($idsEntite)) {
			$c->add(TEtablissementPeer::ID_ENTITE,$idsEntite, Criteria::IN);
		}
		
		$c->addAscendingOrderByColumn(TEtablissementPeer::ID_ENTITE);
		$arrayObjetEtab = TEtablissementPeer::doSelect($c,$connexion);
		if($valeurParDefaur){
			if($onlyGere && (Atexo_User_CurrentUser::isAdminEtab() || Atexo_User_CurrentUser::isAdminOrgWithEtab())) {
				$index = Atexo_User_CurrentUser::getIdEtablissementGere().",0";
			}
			else {
				$index = 0;
			}
			$arrayEtab[$index] = "000".$valeurParDefaur;
		}
		foreach($arrayObjetEtab as $etab){
			if($etab->getTEntite()) {
				$libEntite = $etab->getTEntite()->getLibelleTraduit($lang);
				if(trim($libEntite)!="")
					$entiteTraduit = $etab->getTEntite()->getLibelleTraduit($lang)." - ";
				else
					$entiteTraduit = "";
			}
			else {
				$entiteTraduit = "";
			}
			$arrayEtab[$etab->getIdEtablissement()] = $entiteTraduit.$etab->getDenominationEtablissementTraduit($lang);//Traduit($lang);
		}
		asort($arrayEtab);

		if($valeurParDefaur){
			$arrayEtab[$index] = $valeurParDefaur;
		}
		return $arrayEtab;
	}
}
