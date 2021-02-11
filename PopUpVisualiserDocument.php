<?php

/**
 * description de la classe
 *
 * @author atexo
 * @copyright Atexo 2015
 * @version 0.0
 * @since Atexo.Bcf
 * @package atexo
 * @subpackage atexo
 */
class PopUpVisualiserDocument extends TPage
{
    public $url;
    public $nomFile;

    public function onInit()
    {

    }

    public function onLoad()
    {
        if (!$this->isPostBack) {

            $idRdv = $_GET["id"];
            $url = Atexo_Utils_Util::getUrlFromIndex();
            $data = array();
            if (Atexo_User_CurrentUser::getIdAgentConnected() && is_numeric($idRdv)) {
                $tRdv = new TRendezVousQuery();
                $rdv = $tRdv->findPk($idRdv);
                if($rdv){
                    $blobs = $rdv->getTBlobRdvs();
                    if($blobs){
                        $i=0;
                        foreach($blobs as $blob){
                            $nom = $blob->getTBlob()->getNomBlob();
                            $idBlob = $blob->getIdBlob();
                            $data[] = $nom.'##'.$url . Atexo_Utils_UrlProtector::protectUrl('?page=ressource.FrameDocument&id='.$idRdv.'&idBlob=' . $idBlob.'&nomBlob='.$nom)."#zoom=100";
                            if($i==0){
                                $this->nomFile = $nom;
                                $this->url = $url . Atexo_Utils_UrlProtector::protectUrl('?page=ressource.FrameDocument&id='.$idRdv.'&idBlob=' . $idBlob.'&nomBlob='.$nom);
                                $i++;
                            }
                        }
                        $this->listeFiles->DataSource = $data;
                        $this->listeFiles->DataBind();

                    }
                }
            }
        }
    }

}