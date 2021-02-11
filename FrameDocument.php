<?php
/**
 * description de la classe
 *
 * @author atexo
 * @copyright Atexo 2021
 * @version 2.0
 * @since Atexo.rdv
 * @package atexo
 * @subpackage atexo
 */

class FrameDocument extends TPage {

    public function onLoad()
    {
		$idRdv = $_GET["id"];
        if (!$this->isPostBack) {
            if (isset($_GET["idBlob"]) && isset($_GET["nomBlob"])) {
                $idBlob = $_GET['idBlob'];
                $nomFichier = $_GET['nomBlob'];
                $dest = Atexo_Config::getParameter("PATH_FILE")."/";
                if (Atexo_User_CurrentUser::getIdAgentConnected() && is_numeric($idBlob) && is_numeric($idRdv)) {
					
					$tRdv = new TRendezVousQuery();
					$rdv = $tRdv->findPk($idRdv);
					if(!$rdv) {
						echo "Ressource interdite";
						exit;
					}
					$idOrg = $rdv->getTEtablissement()->getIdOrganisation();
					if($idOrg!=$this->User->getCurrentOrganism()) {
						echo "Ressource interdite";
						exit;
					}
					$exist = false;
					if($rdv){
						$blobs = $rdv->getTBlobRdvs();
						if($blobs){
							$i=0;
							foreach($blobs as $blob){
								if($blob->getIdBlob() == $idBlob) {
									$exist = true;
								}
							}
						}
					}
					if(!$exist)  {
						echo "Ressource interdite";
						exit;
					}  
					
                    $path_parts = pathinfo($nomFichier);
                    $extension = $path_parts['extension'];

                    $chemin = $dest.$idBlob;
                    $extension = strtolower($extension);

                    $notFileConverted = array("txt", "xml", "csv", "jpeg", "jpg", "png", "bmp", "gif", "pdf");
                    $fileConverted = array("doc", "docx", "xls", "xlsx", "ppt", "pptx", "odt", "ods", "odp");

                    if (in_array($extension, $notFileConverted)) {
                        $content = file_get_contents($chemin);
                        if ($extension == "pdf") {
                            $typeData = "application/pdf";
                        } elseif ($extension == "txt" || $extension == "csv" || $extension == "xml") {
                            $typeData = "text/html";
                        } else {
                            echo "<img style='max-width: 100%;height: auto;width: auto;max-height: 100%;' src='data:image/".$extension.";base64, ".base64_encode($content)."'>";
                            exit;
                        }
                        header("Content-Type: " . $typeData);
                        header("Content-Disposition: inline; filename=\"".$nomFichier."\";");
                        if ($extension == "csv")
                            echo utf8_encode($content);
                        else
                            echo $content;
                        exit;
                    } elseif (in_array($extension, $fileConverted)) {
                        $newChemin = $chemin;
                        chmod($newChemin, 0777);
                        $pdfGeneratorC = new PdfGeneratorClient();
                        header("Content-Type: application/pdf");

                        echo $pdfGeneratorC->genererPdf($newChemin);
                        @unlink($newChemin);
                        exit;
                    } else {
                        echo "Ressource interdite";
                        exit;
                    }
                } else {
                    echo "Ressource interdite";
                    exit;
                }                          
            }
        }
    }


}