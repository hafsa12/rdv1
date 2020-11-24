<com:TActivePanel Id="panelTypeRdv" CssClass="control-group">
		<label class="control-label" for="<%=$this->typeRdv->ClientId%>"><com:TTranslate>TYPE_RDV</com:TTranslate> : </label>
		<div class="controls">
			<com:TActiveDropDownList Id="typeRdv" OnCallBack="loadTypeEtablissement" CssClass="" Attributes.OnChange="<%= $this->typeRdv->ActiveControl->Javascript %>.dispatch();">
				<prop:ClientSide.OnLoading>J('#bloc-loader').show();</prop:ClientSide.OnLoading>
			 	<prop:ClientSide.OnFailure>J("#bloc-loader").hide();</prop:ClientSide.OnFailure>
				<prop:ClientSide.OnSuccess>J("#bloc-loader").hide();</prop:ClientSide.OnSuccess>
			</com:TActiveDropDownList>
		</div>
	</com:TActivePanel>
<com:TActivePanel Id='panelEntite' style="display:<%=(!$this->visibleOu)?'none':''%>">
		<!--<legend><com:TTranslate>OU</com:TTranslate></legend>-->
		<com:TPanel Id="panelOu">
		<div class="control-group">
			<label class="control-label" for="<%=$this->motsCles->ClientId%>"><com:TTranslate>MOTS_CLES</com:TTranslate> :</label>
			<div class="controls">
				<com:TAutoComplete
					    ID="motsCles"
					    Attributes.placeholder="<%=Prado::localize('VILLE_OU_NOM_ETABLISSEMENT')%>"
					    OnSuggest="suggestNames"
					    OnSuggestionSelected="suggestionSelected"
					    Suggestions.DataKeyField="ID_ETABLISSEMENT"
					    ResultPanel.CssClass="acomplete"
					    MinChars="3"
					    Attributes.onmousedown="dropText(this)"
					    CssClass="input-xxlarge"
					    >
					    <prop:Suggestions.ItemTemplate>
					    	<li title="<%# $this->Data['NOM_ETAB']%>"><%# $this->Data['NOM_ETAB']%></li>
					    </prop:Suggestions.ItemTemplate>
				</com:TAutoComplete>
			</div>
		</div>
	<div class="control-group">
		<label class="control-label" for="<%=$this->entite1->ClientId%>"><com:TTranslate>ENTITE_1</com:TTranslate> : </label>
		<div class="controls">
			<com:TActiveDropDownList Id="entite1" OnCallBack="loadEntite2" CssClass="chosen-select" Attributes.OnChange="<%= $this->entite1->ActiveControl->Javascript %>.dispatch();">
				<prop:ClientSide.OnLoading>J('#bloc-loader').show();</prop:ClientSide.OnLoading>
			 	<prop:ClientSide.OnFailure>J("#bloc-loader").hide();</prop:ClientSide.OnFailure>
				<prop:ClientSide.OnSuccess>J("#bloc-loader").hide();</prop:ClientSide.OnSuccess>
			</com:TActiveDropDownList>
		</div>
	</div>
	 <div class="control-group">
		<label class="control-label" for="<%=$this->entite2->ClientId%>"><com:TTranslate>ENTITE_2</com:TTranslate> : </label>
		<div class="controls">
			<com:TActiveDropDownList Id="entite2" OnCallBack="loadEntite3" CssClass="chosen-select" Attributes.OnChange="<%= $this->entite2->ActiveControl->Javascript %>.dispatch();">
				<prop:ClientSide.OnLoading>J('#bloc-loader').show();</prop:ClientSide.OnLoading>
			 	<prop:ClientSide.OnFailure>J("#bloc-loader").hide();</prop:ClientSide.OnFailure>
				<prop:ClientSide.OnSuccess>J("#bloc-loader").hide();</prop:ClientSide.OnSuccess>
			</com:TActiveDropDownList>
		</div>
	</div>
	 <com:TPanel Id="panelEntite3" CssClass="control-group">
		<label class="control-label" for="<%=$this->entite3->ClientId%>"><com:TTranslate>ENTITE_3</com:TTranslate> : </label>
		<div class="controls">
			<com:TActiveDropDownList Id="entite3" OnCallBack="loadEtablissement" CssClass="chosen-select" Attributes.OnChange="<%= $this->entite3->ActiveControl->Javascript %>.dispatch();">
				<prop:ClientSide.OnLoading>J('#bloc-loader').show();</prop:ClientSide.OnLoading>
			 	<prop:ClientSide.OnFailure>J("#bloc-loader").hide();</prop:ClientSide.OnFailure>
				<prop:ClientSide.OnSuccess>J("#bloc-loader").hide();</prop:ClientSide.OnSuccess>
			</com:TActiveDropDownList>
		</div>
	</com:TPanel>
	</com:TPanel>
	 <div class="control-group">
		<label class="control-label" for="<%=$this->etablissement->ClientId%>"><com:TTranslate>ETABLISSEMENT</com:TTranslate> : </label>
		<div class="controls">
			<com:TActiveDropDownList Id="etablissement" OnCallBack="loadTypePrestation" CssClass="chosen-select" Attributes.OnChange="<%= $this->etablissement->ActiveControl->Javascript %>.dispatch();">
				<prop:ClientSide.OnLoading>J('#bloc-loader').show();</prop:ClientSide.OnLoading>
			 	<prop:ClientSide.OnFailure>J("#bloc-loader").hide();</prop:ClientSide.OnFailure>
				<prop:ClientSide.OnSuccess>J("#bloc-loader").hide();</prop:ClientSide.OnSuccess>
			</com:TActiveDropDownList>
			<com:TCustomValidator
				ValidationGroup="ValidationChoix"
				ControlToValidate="etablissement"
				Display="Dynamic"
				Text="<%=Prado::localize('CHAMP_OBLIGATOIRE')%>"
				ClientValidationFunction="validationDropDownList"
				/>
		</div>
	</div>
	<com:TActiveLabel Id="msgEtab"/>
</com:TActivePanel>
<com:TPanel Visible="<%=Atexo_User_CurrentUser::isAdmin()%>">
<fieldset>
	<legend><com:TTranslate>QUAND</com:TTranslate><span class="raccourci">(<kbd>Ctrl</kbd> : <i><com:TTranslate>AFFICHER_RACCOURCIS</com:TTranslate></i>)</span></legend>

	<div class="control-group radio-list-flex">
		<label class="control-label"></label>
		<label class="radio inline" >
			<com:TRadioButton GroupName="quand" id="plutot" Text="<%=Prado::localize('PLUTOT')%>" Checked="true" Attributes.OnClick="hideDiv('xMois');hideDiv('xJours');hideDiv('xRdvJours');"/><kbd class="ctrl">alt</kbd><plus> + </plus><kbd class="ctrl">p</kbd>
		</label>
		<label class="radio inline" >
			<com:TRadioButton GroupName="quand" id="dansXJours" Text="<%=Prado::localize('DANS_X_JOURS')%>" Attributes.OnClick="hideDiv('xMois');showDiv('xJours');hideDiv('xRdvJours');"/><kbd class="ctrl">alt</kbd><plus> + </plus><kbd class="ctrl">j</kbd>
		</label>
		<label class="radio inline" >
			<com:TRadioButton GroupName="quand" id="dansXMois" Text="<%=Prado::localize('DANS_X_MOIS')%>" Attributes.OnClick="showDiv('xMois');hideDiv('xJours');hideDiv('xRdvJours');"/><kbd class="ctrl">alt</kbd><plus> + </plus><kbd class="ctrl">m</kbd>
		</label>
		<label class="radio inline" >
			<com:TRadioButton GroupName="quand" id="rdvXJours" Text="<%=Prado::localize('RDV_GROUPE')%>" Attributes.OnClick="hideDiv('xMois');hideDiv('xJours');showDiv('xRdvJours');"/><kbd class="ctrl">alt</kbd><plus> + </plus><kbd class="ctrl">s</kbd>
		</label>
		<label class="radio inline" >
			<com:TRadioButton GroupName="quand" id="recherche" Text="<%=Prado::localize('RECHERCHE')%>" Attributes.OnClick="hideDiv('xMois');hideDiv('xJours');hideDiv('xRdvJours');"/><kbd class="ctrl">alt</kbd><plus> + </plus><kbd class="ctrl">r</kbd>
		</label>
		<label class="radio inline" id="step1">
			<com:TRadioButton GroupName="quand" id="rechercheEtendu"  Visible="<%=Atexo_User_CurrentUser::hasHabilitation('RechercheEtendueRdv')%>"
				Text="<%=Prado::localize('RECHERCHE_ETENDUE')%>" Attributes.OnClick="hideDiv('xMois');hideDiv('xJours');"/><kbd class="ctrl">alt</kbd><plus> + </plus><kbd class="ctrl">e</kbd>
		</label>
		&nbsp;
		<a tabindex="-1" style="display:<%=Atexo_User_CurrentUser::hasHabilitation('RechercheEtendueRdv')?'':'none'%>" href="javascript:void(0);" onclick="startIntro()"><i class="icon-question-sign"></i></a>
		
	</div>
	<div id="xRdvJours" style="display:<%=($this->rdvXJours->Checked)?'':'none'%>">
		<div class="control-group">
			<label class="control-label" for="<%=$this->nbRdvGrp->ClientId%>"><com:TTranslate>NOMBRE_RENDEZ_VOUS</com:TTranslate> : </label>
			<div class="controls">
					<com:TTextBox id="nbRdvGrp" Text="1" cssclass="input-xmini" />
				<com:TCompareValidator
						ControlToValidate="nbRdvGrp"  ValueToCompare="1"
						DataType="Integer" Operator="GreaterThanEqual" ValidationGroup="ValidationChoix" Display="Dynamic"
						ErrorMessage="<%=prado::localize('MSG_VALEUR_CHAMP_POSITIVE')%>">
				</com:TCompareValidator>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="<%=$this->intervalJours->ClientId%>"><com:TTranslate>INTERVAL</com:TTranslate> : </label>
			<div class="controls">
				<div class="input-append">
					<com:TTextBox id="intervalJours" Text="1" cssclass="input-xmini text-right" /><span class="add-on"><com:TTranslate>JOURS_PLRS</com:TTranslate></span>
				</div>
				<com:TCompareValidator
					ControlToValidate="intervalJours"  ValueToCompare="1"
					DataType="Integer" Operator="GreaterThanEqual" ValidationGroup="ValidationChoix" Display="Dynamic"
					ErrorMessage="<%=prado::localize('MSG_VALEUR_CHAMP_POSITIVE')%>">
				</com:TCompareValidator>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="<%=$this->intervalXRdvJours->ClientId%>"><com:TTranslate>A_PARTIR_DE</com:TTranslate> : </label>
			<div class="controls">
				<div class="date calendrier" data-date="">
					<com:TTextBox id="intervalXRdvJours" Text="<%=date('d/m/Y')%>" cssClass="input-date"/>
				</div>
				<com:TCompareValidator
						ControlToValidate="intervalXRdvJours"  ValueToCompare="<%=date('d/m/Y')%>" DateFormat="dd/MM/yyyy"
						DataType="Date" Operator="GreaterThanEqual" ValidationGroup="ValidationChoix" Display="Dynamic"
						ErrorMessage="<%=prado::localize('ERROR_DATE_A_PARTIR')%>">
				</com:TCompareValidator>
			</div>
		</div>
	</div>
	<div class="control-group" id="xJours" style="display:<%=($this->dansXJours->Checked)?'':'none'%>">
		<label class="control-label" for="<%=$this->numberJours->ClientId%>"><com:TTranslate>NOMBRE_JOURS</com:TTranslate> : </label>
		<div class="controls">
			<div class="input-append">
				<com:TTextBox id="numberJours" Text="1" cssclass="input-xmini text-right" /><span class="add-on"><com:TTranslate>JOURS_M</com:TTranslate></span>
			</div>
			<com:TCompareValidator
					ControlToValidate="numberJours"  ValueToCompare="1"
					DataType="Integer" Operator="GreaterThanEqual" ValidationGroup="ValidationChoix" Display="Dynamic"
					ErrorMessage="<%=prado::localize('MSG_VALEUR_CHAMP_POSITIVE')%>">
			</com:TCompareValidator>
		</div>
	</div>
	<div class="control-group" id="xMois" style="display:<%=($this->dansXMois->Checked)?'':'none'%>">
		<label class="control-label" for="<%=$this->numberMois->ClientId%>"><com:TTranslate>NOMBRE_MOIS</com:TTranslate> : </label>
		<div class="controls">
			<div class="input-append">
				<com:TTextBox id="numberMois" Text="1" cssclass="input-xmini text-right" /><span class="add-on"><com:TTranslate>MOIS</com:TTranslate></span>
			</div>
			<com:TCompareValidator  ControlToValidate="numberMois"  ValueToCompare="1"
									DataType="Integer" Operator="GreaterThanEqual" ValidationGroup="ValidationChoix" Display="Dynamic"
									ErrorMessage="<%=prado::localize('MSG_VALEUR_CHAMP_POSITIVE')%>"
			>
			</com:TCompareValidator>
		</div>
	</div>
</fieldset>
</com:TPanel>
<fieldset>
	<legend><com:TTranslate>QUOI</com:TTranslate></legend>
	<com:TPanel Visible="<%=Atexo_User_CurrentUser::isAdmin()%>" CssClass="control-group">
			<label class="control-label" for="<%=$this->motsClesRessource->ClientId%>"><com:TTranslate>MOTS_CLES</com:TTranslate> :</label>
			<div class="controls">
				<com:TAutoComplete
					    ID="motsClesRessource"
					    Attributes.placeholder="<%=Prado::localize('PRESTATION_OU_NOM_RESSOURCE')%>"
					    OnSuggest="suggestNamesRessources"
					    OnSuggestionSelected="suggestionRessourcesSelected"
					    Suggestions.DataKeyField="ID_RESSOURCE"
					    ResultPanel.CssClass="acomplete"
					    MinChars="3"
					    Attributes.onmousedown="dropText(this)"
					    CssClass="input-xxlarge"
					    >
					    <prop:Suggestions.ItemTemplate>
					    	<li title="<%# $this->Data['NOM_RESSOURCE']%>"><%# $this->Data['NOM_RESSOURCE']%></li>
					    </prop:Suggestions.ItemTemplate>
				</com:TAutoComplete>
			</div>
		</com:TPanel>
    <div class="control-group">
		<label class="control-label" for="<%=$this->typePrestation->ClientId%>"><com:TTranslate>NIVEAU1</com:TTranslate> : </label>
		<div class="controls">
			<com:TActiveDropDownList 
									Id="typePrestation" 
									OnCallBack="loadPrestation" 
									CssClass="chosen-select"
									DataGroupField="GROUPE"  
									DataTextField="NOM_TYPE_PRESTATION"
                                    DataValueField="ID_TYPE_PRESTATION"  
									Attributes.OnChange="<%= $this->typePrestation->ActiveControl->Javascript %>.dispatch();">
				<prop:ClientSide.OnLoading>J('#bloc-loader').show();</prop:ClientSide.OnLoading>
			 	<prop:ClientSide.OnFailure>J('#bloc-loader').hide();</prop:ClientSide.OnFailure>
				<prop:ClientSide.OnSuccess>J('#bloc-loader').hide();</prop:ClientSide.OnSuccess>
			</com:TActiveDropDownList>
		<com:TRequiredFieldValidator
			ValidationGroup="ValidationChoix"
			ControlToValidate="typePrestation"
			InitialValue="0"
			Text="<%=Prado::localize('CHAMP_OBLIGATOIRE')%>"/>
		</div>
	</div>
	 <div class="control-group">
		<label class="control-label" for="<%=$this->prestation->ClientId%>"><com:TTranslate>NIVEAU2</com:TTranslate> : </label>
		<div class="controls">
			<com:TActiveDropDownList Id="prestation" OnCallBack="loadRessource" CssClass="chosen-select" Attributes.OnChange="<%= $this->prestation->ActiveControl->Javascript %>.dispatch();">
				<prop:ClientSide.OnLoading>J('#bloc-loader').show();</prop:ClientSide.OnLoading>
			 	<prop:ClientSide.OnFailure>J('#bloc-loader').hide();</prop:ClientSide.OnFailure>
				<prop:ClientSide.OnSuccess>J('#bloc-loader').hide();</prop:ClientSide.OnSuccess>
			</com:TActiveDropDownList>
		<com:TRequiredFieldValidator
			ValidationGroup="ValidationChoix"
			ControlToValidate="prestation"
			InitialValue="0"
			Text="<%=Prado::localize('CHAMP_OBLIGATOIRE')%>"/>
		</div>
	</div>
	<script type="text/javascript">
				function textObligatoire(sender,params) {
					if(J('#'+sender.options.ControlToValidate).length > 0)
					{
						if(params != "")
							return true;
						else
							return false;
					}
					else{
						return true;
					}
					
				}
			</script>
	<com:TActiveRepeater Id="champsSupPresta">
		<prop:ItemTemplate>
			<com:THiddenField Id="libellefr" Value="<%#$this->DataItem['libelle_fr']%>"/>
			<com:THiddenField Id="libellear" Value="<%#$this->DataItem['libelle_ar']%>"/>
			<com:TPanel id="typeText" CssClass="control-group" Visible="<%#$this->DataItem['typeChamp']=='TEXT' || $this->DataItem['typeChamp']=='NUMERIC' || $this->DataItem['typeChamp']=='LONGTEXT'%>">
				<label class="control-label"><com:TLabel Text="<%#$this->DataItem['libelle']%>"/> <%#$this->DataItem['labelObligatoire']%> : </label>
				<div class="controls">
					<com:TTextBox ID="text" Rows="3" TextMode="<%#$this->DataItem['typeChamp']=='LONGTEXT'?'MultiLine':'SingleLine'%>"/>
					<com:TCustomValidator
							ControlToValidate="text"
							ValidationGroup="ValidationChoix"
							ErrorMessage="<%=Prado::localize('CHAMP_OBLIGATOIRE')%>"
							Visible="<%#$this->DataItem['obligatoire']%>"
							Display="Dynamic"
							ClientValidationFunction="textObligatoire"
							EnableClientScript="true"  />
					<com:TDataTypeValidator
							ControlToValidate="text"
							ValidationGroup="validation"
							ErrorMessage="<%=Prado::localize('CHAMP_OBLIGATOIRE').' - '.Prado::localize('NUMERIC')%>"
							Visible="<%#$this->DataItem['typeChamp']=='NUMERIC'%>"
							Display="Dynamic" />
				</div>
			</com:TPanel>
			
			<com:TPanel id="typeList" CssClass="control-group" Visible="<%#$this->DataItem['typeChamp']=='LISTE'%>">
				<label class="control-label"><com:TLabel Text="<%#$this->DataItem['libelle']%>"/> <%#$this->DataItem['labelObligatoire']%> :</label>
				<div class="controls">
					<com:TDropDownList ID="list" DataSource="<%#$this->DataItem['listeValues']%>" />
					<com:TCustomValidator 
							ControlToValidate="list"
							ValidationGroup="ValidationChoix"
							ErrorMessage="<%=Prado::localize('CHAMP_OBLIGATOIRE')%>"
							Visible="<%#$this->DataItem['obligatoire']%>"
							Display="Dynamic"
							ClientValidationFunction="textObligatoire"
							EnableClientScript="true" />
				</div>
			</com:TPanel>
			<com:TPanel id="typeMultiChoix" Visible="<%#$this->DataItem['typeChamp']=='MULTICHOIX'%>">
				<div class="control-group">
					<label class="control-label"><com:TLabel Text="<%#$this->DataItem['libelle']%>"/> <%#$this->DataItem['labelObligatoire']%> :</label>
					<com:TRepeater Id="listCheck" DataSource="<%#$this->DataItem['listeValues']%>">
						<prop:ItemTemplate>
								<div class="controls">
									<label class="checkbox inline">
										<com:TCheckBox id="check" Value="<%#$this->DataItem['value']%>" Text="<%#$this->DataItem['text']%>"
													   Attributes.OnClick="validateCheck('<%#$this->Parent->Parent->Parent->checkValide->ClientId%>',this);"/>
									</label>
								</div>
						</prop:ItemTemplate>
					</com:TRepeater>
					<com:THiddenField id="checkValide"/>
					<div class="controls">
					<com:TCustomValidator
							ControlToValidate="checkValide"
							ValidationGroup="ValidationChoix"
							ErrorMessage="<%=Prado::localize('CHAMP_OBLIGATOIRE')%>"
							Visible="<%#$this->DataItem['obligatoire']%>"
							Display="Dynamic"
							ClientValidationFunction="textObligatoire"
							EnableClientScript="true" />
						</div>
				</div>
			</com:TPanel>
		</prop:ItemTemplate>
	</com:TActiveRepeater>
	 <com:TActivePanel Id="panelRessource" CssClass="control-group" Style="display:none">
		<label class="control-label" for="<%=$this->ressource->ClientId%>">
			<com:TTranslate>NIVEAU3</com:TTranslate>
			<com:TActiveHyperLink Id="aideBloc">
			<i class="icon-info-sign"></i>
			</com:TActiveHyperLink>	:
		</label>
		<div class="controls"><com:TActiveDropDownList Id="ressource" AutoPostBack="false" CssClass="chosen-select"/>
		<com:TRequiredFieldValidator
			Id="validateurRessource"
			ValidationGroup="ValidationChoix"
			ControlToValidate="ressource"
			InitialValue="0"
			Text="<%=Prado::localize('CHAMP_OBLIGATOIRE')%>"/>
		<com:TCustomValidator
			Id="validateurRessourceEtendue"
			ValidationGroup="ValidationChoix"
			ControlToValidate="ressource"
			Text="<%=Prado::localize('CHAMP_OBLIGATOIRE')%>"
			ClientValidationFunction="validateRechercheEtendu"/>
		</div>
	</com:TActivePanel>
	<com:TActivePanel Id="panelReferent" CssClass="control-group" Style="display:none">
		<label class="control-label" for="<%=$this->referent->ClientId%>"><com:TTranslate>REFERENT</com:TTranslate> : </label>
		<div class="controls"><com:TActiveDropDownList Id="referent"/>
		</div>
	</com:TActivePanel>
	 <com:TPanel Visible="<%=!Atexo_User_CurrentUser::isAdmin()%>" Cssclass="control-group">
		<div class="controls">
            <com:TCheckBox Id="conditions"/>
            <a target="_blank" href="?page=citoyen.ConditionsGenerales">
            <com:TTranslate>CONDITIONS_GENERALES_TEXT</com:TTranslate>
            </a>
		<com:TRequiredFieldValidator
			Id="validateurCondition"
			ValidationGroup="ValidationChoix"
			ControlToValidate="conditions"
			Text="<%=Prado::localize('CHAMP_OBLIGATOIRE')%>"/>
		</div>
	</com:TPanel>
</fieldset>
<com:TPanel Visible="<%=Atexo_User_CurrentUser::hasHabilitation('RechercheEtendueRdv')%>">
<link href="<%=t()%>/css/introjs.css" rel="stylesheet">
<%=(Atexo_User_CurrentUser::readFromSession('lang')=='ar')?'<link href="'.t().'/css/introjs-rtl.css" rel="stylesheet">':''%>
<script type="text/javascript" src="<%=t()%>/js/intro.js"></script>
    <script type="text/javascript">

      function startIntro(){
        var intro = introJs();
          intro.setOptions({
            steps: [
              {
                element: '#step1',
                intro: "<com:TTranslate>AIDE_RECHERCHE_ETENDUE</com:TTranslate>"
              }
            ]
          	,
          	skipLabel : "x",
          	showStepNumbers : false
          });

          intro.start();
      }

	  J(document).bind('keydown', function(e) {
		  if (e.ctrlKey) { J(".ctrl").toggle();J("plus").toggle();}
	  });
	  J(document).bind('keydown', function(e) {
		  if(e.altKey) {
			  if (e.which == 80) {// p
				  J("#<%=$this->plutot->ClientId%>").prop("checked", true);
				  hideDiv('xMois');hideDiv('xJours');hideDiv('xRdvJours');
				  J("#<%=$this->motsClesRessource->ClientId%>").focus();
				  return false;
			  }
			  if ( e.which == 74) {// j
				  J("#<%=$this->dansXJours->ClientId%>").prop("checked", true);
				  hideDiv('xMois');showDiv('xJours');hideDiv('xRdvJours');
				  J("#<%=$this->numberJours->ClientId%>").focus();
				  return false;
			  }
			  if (e.which == 77) {// m
				  J("#<%=$this->dansXMois->ClientId%>").prop("checked", true);
				  showDiv('xMois');hideDiv('xJours');hideDiv('xRdvJours');
				  J("#<%=$this->numberMois->ClientId%>").focus();
				  return false;
			  }
			  if (e.which == 83) {// s
				  J("#<%=$this->rdvXJours->ClientId%>").prop("checked", true);
				  hideDiv('xMois');hideDiv('xJours');showDiv('xRdvJours');
				  J("#<%=$this->nbRdvGrp->ClientId%>").focus();
				  return false;
			  }
			  if (e.which == 82) {// r
				  J("#<%=$this->recherche->ClientId%>").prop("checked", true);
				  hideDiv('xMois');hideDiv('xJours');hideDiv('xRdvJours');
				  J("#<%=$this->motsClesRessource->ClientId%>").focus();
				  return false;
			  }
			  if (e.which == 69) {// e
				  J("#<%=$this->rechercheEtendu->ClientId%>").prop("checked", true);
				  hideDiv('xMois');hideDiv('xJours');hideDiv('xRdvJours');
				  J("#<%=$this->motsClesRessource->ClientId%>").focus();
				  return false;
			  }
		  }
	  });
    </script>
</com:TPanel>

<script type="text/javascript">
	function validateRechercheEtendu() {
		return validateDDLWithCondition('<%=$this->rdvXJours->getClientId()%>', '<%=$this->rechercheEtendu->getClientId()%>', '<%=$this->ressource->getClientId()%>');
	}
</script>

<script type="text/javascript">
	J(document).ready(function () {
		J('.input-date').datepicker({
			format: 'dd/mm/yyyy',
			weekStart: 1,
			pickerPosition: 'bottom-right'
		});
	});
	function validateCheck(id,elem) {
		if(elem.checked) {
			J('#'+id).val(J('#'+id).val()+"#"+elem.value+"#");
		}
		else {
			J('#'+id).val(J('#'+id).val().replace("#"+elem.value+"#",""));
		}
	}
	function myFunction() {
                setTimeout(function(){ J('.chosen-select').trigger('chosen:updated');J('.info-bulle').tooltip();myFunction(); }, 1000);
        }
        //myFunction();
</script>
<com:TActiveLabel style="display:none" id="script" />
<com:TActiveLabel style="display:none" id="scriptDisable" />
