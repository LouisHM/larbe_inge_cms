<?php
/* 
 * Copyright (C) 20xx VMA Vincent Maury <vmaury@timgroup.fr>
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY
 */

/** création / mis à jour d'un chantier
 * 
 * @param int $idC id content 
 * @param array $rwProj tableau propriété projet issu de Dolibarr
 * @return int $idC id chantier/content
 */
function crupChantier(int $idC = 0, array $rwProj) {
	global $ret;
	//print_r($rwProj);
	// crup bolt_content
	if ($idC > 0) $tbiC['id'] = $idC;
	$tbiC['author_id'] = 1;
	$tbiC['content_type'] = 'chantiers';
	$tbiC['status'] = $tbiM['status'] = ($rwProj['pubwww'] == 1  ? 'published' : 'held');
	$tbiC['created_at'] = $tbiC['modified_at'] = $tbiM['modified_at'] = date('Y-m-d H:i:s');
	
	if ($idC > 0) {
		$res = db_query(insertondkupdate('bolt_content',$tbiC, $tbiM, 2));
		$idC = db_last_id($res) >0 ? db_last_id($res) : $idC;
	} else {
		db_query("insert into bolt_content ".tbset2insert($tbiC, 2));
		$idC = db_last_id($res);
	}
	$tbFields = cvDolProj2BoltChantier($rwProj); // prépare le tableau des champs à CReer updater
	foreach ($tbFields as $name=>$tfield) {
		$tbFields[$name] = crupChamp($idC, $name, $tfield['type'], $tfield['value']) ;
	}
	affectBoltTaxo($idC,$rwProj);
	//$ret['tbFields'] = var_export($tbFields, 1);
	//print_r($tbFields);
	return $idC;
	
}

/** création / mis à jour d'un champ
 * 
 * @param int $idC id Content
 * @param string $nomChp nom du champ 
 * @param string $typChp text slug image 
 * @param string|array $valChp valeur champ
 * @return array ['name'=>$nomChp, 'type'=>$typChp,'idDef'=>$tfid id bolt_field, 'idVal'=>$vfid id bolt_field_translation]
 */
function crupChamp($idC, $nomChp, $typChp, $valChp) {
	// gestion table bolt_field def du champ
	$tfid = db_qr_1val("select id from bolt_field where content_id=$idC and name='$nomChp'");
	if (empty($tfid)) {
		/* Modify	id		content_id	parent_id	name	sortorder	version	type
			edit	3441	409			NULL		files	0			NULL	filelist */
		$tbif['content_id'] = $idC;
		$tbif['name'] = $nomChp;
		$tbif['type'] = $typChp;
		$sql = "insert into bolt_field ".tbset2insert($tbif, 2);
		//echo "sql = $sql \n";
		$res = db_query($sql);
		$tfid = db_last_id($res);
	} else db_query("update bolt_field set type='".$typChp."' where content_id=$idC and name='$nomChp'");
	// maitenant on passe au contenu
	$vfid = db_qr_1val("select id from bolt_field_translation where translatable_id=$tfid and locale='".boltLocale."'");
	$valChpJS = '';
	$valChp = str_replace('&#149;', '&nbsp;*&nbsp;',$valChp); // y passent pas les points
	if (in_array($typChp, ['filelist', 'imagelist', 'image']))  {
		$valChpJS = db_escape_string($valChp);
	} else {
		if (!empty($valChp)) {
			$valChpJS = str_replace(chr(92).'r',chr(92).chr(92).'r',db_escape_string( nl2br($valChp)));
			$valChpJS = str_replace(chr(92).'n',chr(92).chr(92).'n',$valChpJS);
			$valChpJS = str_replace(chr(92).'"',chr(92).chr(92).'"',$valChpJS);
			$valChpJS = '[\"'.$valChpJS.'\"]';
			//json_encode
		}
	}
	if (!empty($valChpJS)) {
		if (empty($vfid)) { 
			$tbvif['translatable_id'] = $tfid;
			$tbvif['value'] = "'".$valChpJS."'";
			$tbvif['locale'] = "'".boltLocale."'";
			$sqlv = "insert into bolt_field_translation ".tbset2insert($tbvif, 0);
			//echo "sql insert val  = #$sqlv# \n";
			$res = db_query($sqlv);
			$vfid = db_last_id($res);
		} else {
			$sqlv = "update bolt_field_translation set value='".$valChpJS."' where id=$vfid";
			//echo "sql update val  = #$sqlv# \n";
			db_query($sqlv);
		}
	}
	return ['name'=>$nomChp, 'type'=>$typChp,'idDef'=>$tfid, 'idVal'=>$vfid, 'orVal' => $valChp, 'val4bolt' => $valChpJS /*, 'sqlv' => $sqlv*/];
}

/** convertit les données projet Dolibarr en données chantier Bolt, concaténation, etc ..
 * 
 * @param type $rwProj
 * return array $tbFields
 */
function cvDolProj2BoltChantier($rwProj) {
	$tbFields = [
		'files' => ['type' => 'filelist'],
		'gallery' => [ 'type' => 'imagelist'],
		'description' => ['type' => 'redactor'],
		'cost' => ['type' => 'text'],
		'date' => ['type' => 'date'],
		'miniature' => ['type' => 'image'],
		'slug' => ['type' => 'slug'],
		'title' => ['type' => 'text'],
		'adress' => ['type' => 'text'],
		'cdpstville' => ['type' => 'text'],
		'archi' => ['type' => 'text'],
		'lots' => ['type' => 'text'],
		'qualifs' => ['type' => 'text'],
	];
	$tbFields['title']['value'] = $rwProj['title'];
	$tbFields['slug']['value'] = toSlug($rwProj['title']);
	$tbFields['cost']['value'] = $rwProj['Mt_trav_TCE'] > 0 ? number_format($rwProj['Mt_trav_TCE'], 0, ',', ' ').' €' : '';
	$tbFields['adress']['value'] = trim($rwProj['address']);
	$tbFields['cdpstville']['value'] = trim($rwProj['codpost'].' '.$rwProj['ville']);
	$tbFields['archi']['value'] = trim($rwProj['matreduvre']);
	$tbFields['qualifs']['value'] = trim($rwProj['opqibi']);
	$tbFields['lots']['value'] = trim($rwProj['lotscertif']);
	
	/* $tbFields['lots']['value'] = trim($rwProj['lotsbtiments']);
	if (trim($rwProj['lotsbtiments']) != '' && trim($rwProj['lotsenvironnement']) != '') $tbFields['lots']['value'] .= '<br/>';
	if (trim($rwProj['lotsenvironnement']) != '') $tbFields['lots']['value'] .= trim($rwProj['lotsenvironnement']);*/
	
	
	$tbi['date'] = $rwProj['datrecept'] ? $rwProj['datrecept'] : $rwProj['dateo'];
	
	//$tbFields['description']['value'] =  nl2br($rwProj['Desc_lots']);
	$tbFields['description']['value'] = $rwProj['description'];
//	if (!empty($rwProj['opqibi'])) {
//		$tbFields['description']['value'] .=  '<h3 class="chantier-txt-desc-sstitle">Qualifications OPQIPI concernées</h3>'.$rwProj['opqibi'];
//	}
	
	return $tbFields;
}
/** convertit titre en slug
 * 
 * @param string $str titre
 * @return string
 */
function toSlug($str) {
   $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
   $clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
   $clean = strtolower(trim($clean, '-'));
   return preg_replace("/[\/_| -]+/", '-', $clean);
}

/** affecte les taxonomy bolt en fonction des catégories Dolibarr
 * 
 * @param int $idC
 * @param array $rwProj
 */
function affectBoltTaxo($idC,$rwProj) {
	/*
	 * [libpole] => Bâtiment
    [catpole] => 6
    [libagence] => Limoges 87
    [catagence] => 90
    [libactivite] => Santé, Ehpad
    [catactivite] => 6,11,genclim,vrd,genelec
    [catactivites4bolt] => 6,11
    [catmetier] => genclim,vrd,genelec
	 */
	$tbIdTaxo2Aff = [];
	
	$tbIdTaxo2Aff += getListIdsTaxo('poles', $rwProj['catpole']);
	$tbIdTaxo2Aff += getListIdsTaxo('activites', $rwProj['catactivites4bolt']);
	$tbIdTaxo2Aff += getListIdsTaxo('metiers', $rwProj['catmetier']);
//	print_r($tbIdTaxo2Aff);
//	die();
	$tbitax['content_id'] = $idC;
	foreach ($tbIdTaxo2Aff as $idTaxo) {
		$tbitax['taxonomy_id'] = $idTaxo;
		db_query(insertondkupdate('bolt_taxonomy_content', $tbitax, $tbitax));
	}
	if (count($tbIdTaxo2Aff) > 1) db_query("delete from bolt_taxonomy_content where content_id=$idC and taxonomy_id not in (".implode (',', $tbIdTaxo2Aff).")");
}
/**
 * 
 * @param type $type
 * @param type $tbdolcats
 */
function getListIdsTaxo($type, $dolcats) {
	/* bolt_taxonomy :	id	type		slug		name	sortorder	dolcat
						458	poles		cou			coucou	0			,6,
						457	metiers		tce			TCE		0			,tce,
						443	activites	logements	LGI		0		,26,97,	 */
	$tbret = [];
	$tbdolcats = explode(',',$dolcats);
	foreach ($tbdolcats as $dolcat) {
		$tbcatsid = db_qr_compres4ld("select id from bolt_taxonomy where type = '$type' and dolcat like '%,$dolcat,%'");
		if (is_array($tbcatsid)) $tbret += $tbcatsid;
	}
	return $tbret;
}

/** maj champ gallery photo ou filelist
 * 
 * @param int $idC id content
 * @param string $name nom du champ : gallery ou files
 * @param array $tbfp : tableau des fichiers reçus
 */
function majChpGaleryOrFilelist($idC,$name, $tbfp) {
	/*
	 * 'files' => ['type' => 'filelist'],
		'gallery' => [ 'type' => 'imagelist'],
	 */
	$type = $name == 'files' ? 'filelist' : 'imagelist';
	if (is_array($tbfp) && count($tbfp) > 0) {
		$tbfp4js = [];
		//[{"0": "IMG_20230727_145305.jpg", "alt": "", "media": "", "filename": "imagesChantier/IMG-20230727-145305.jpg"}, {"1": "IMG_20230727_145305.jpg", "alt": "", "media": "", "filename": "imagesChantier/IMG-20230727-145305.jpg"}]
		// la req pour lister les contenus et valeurs 
		// select * from bolt_field bf left join bolt_field_translation bft on bf.id=bft.translatable_id where bf.content_id=410
		$i = 0;
		$miniatureOk = false;
		foreach($tbfp as $fn) {
			$tb1f = [(string)$i => $fn, 'alt' => '', 'media' => '', 'filename' => 'imagesChantier/'.$fn];
			// mise à jour de la miniature, le 1er fichier **photo** rencontré
			if ($i == 0 && $name == 'gallery' && !$miniatureOk) {
				$ret[] = crupChamp($idC, 'miniature', 'image', json_encode($tb1f));
				$miniatureOk = true;
			} else {
				$tbfp4js[] = $tb1f;
				$i ++;
			}
		}
		$ret[] = crupChamp($idC, $name, $type, json_encode($tbfp4js));
	} else {
		crupChamp($idC, $name, $type, json_encode([]));
		$ret = "Champ $name raz ";
	}
	return $ret;
}
/*
 * Array
(
    [rowid] => 1127
    [fk_soc] => 384
    [datec] => 2017-01-01 00:00:00
    [tms] => 2022-07-01 12:15:19
    [dateo] => 2017-07-18
    [datee] => 
    [ref] => 2017-176
    [entity] => 1
    [title] => DOMUSVI  - MELLE
    [description] => Construction d&rsquo;un EHPAD de 112 lits<br />
(dont 14 lits en unit&eacute; prot&eacute;g&eacute;e et PASA de 6 &agrave; 10 places &ndash; Salle Polyvalente &ndash; 2 logements)
    [fk_user_creat] => 1
    [fk_user_modif] => 37
    [public] => 1
    [fk_statut] => 2
    [fk_opp_status] => 6
    [opp_percent] => 100.00
    [date_close] => 2022-07-01 14:15:19
    [fk_user_close] => 50
    [note_private] => Projet importé depuis l'ancien logiciel.
Mémo :         25 000 € en moins pour élec Cité 4 soit un total de 153 200 €
ARCHI
    [note_public] => 
    [opp_amount] => 168046.47000000
    [budget_amount] => 168046.47000000
    [import_key] => 
    [Mt_facturé] => 168046.45000000
    [Mt_fact_hstt] => 168046.43000000
    [Mt_réglé] => 168046.49000000
    [Reste_a_payer] => -0.04000000
    [Reste_a_facturer] => 0.02000000
    [Reste_a_fact_hstt] => 0.04000000
    [Tps_passé] => 29.91000000
    [Recap] => <a name='atbodrm_1127'></a><table  class='noborder listrecap'>
<thead><tr class='etlistrecap'><th align='left' class='colste'><a class="bttgltbodrm" id="bttgltbodrm_1127" href="#atbodrm_1127" title="Afficher/masquer le détail du tableau"> <i class="fa fa-window-restore valignmiddle"></i></a> &nbsp;Lot-mission</th><th align='left' class='coltrecv'>Tps prev/pass</th><th align='left' class='coltrecv'>Comd/Fact</th></tr></thead><tbody id='tbodrm_1127' class='tbodrm' style='display:none'>
<tr><td class='colste' colspan='2'><b>Cmd CO1707-0010</b></td><td>168&nbsp;046,47&nbsp;€</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6127'>Lot Fluides : APS</td><td>15.45j / 0j (0%)</td><td>10&nbsp;040,51&nbsp;€/10&nbsp;040,51&nbsp;€ (100%)</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6128'>Lot Fluides : APD</td><td>30.72j / 0j (0%)</td><td>19&nbsp;966,99&nbsp;€/19&nbsp;966,99&nbsp;€ (100%)</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6129'>Lot Fluides : PRO</td><td>54.16j / 0j (0%)</td><td>35&nbsp;205,68&nbsp;€/35&nbsp;205,68&nbsp;€ (100%)</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6130'>Lot Fluides : ACT</td><td>9.09j / 0j (0%)</td><td>5&nbsp;910,35&nbsp;€/5&nbsp;910,35&nbsp;€ (100%)</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6131'>Lot Fluides : VISA</td><td>12.17j / 6.52j (54%)</td><td>7&nbsp;908,89&nbsp;€/7&nbsp;908,86&nbsp;€ (100%)</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6132'>Lot Fluides : DET</td><td>115.47j / 21.28j (18%)</td><td>75&nbsp;053,79&nbsp;€/75&nbsp;053,79&nbsp;€ (100%)</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6133'>Lot Fluides : AOR</td><td>7.08j / 0.36j (5%)</td><td>4&nbsp;600,07&nbsp;€/4&nbsp;600,08&nbsp;€ (100%)</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6134'>Lot Fluides : CONTRÔLE RESERVES</td><td>7.08j / 1.08j (15%)</td><td>4&nbsp;600,07&nbsp;€/4&nbsp;600,07&nbsp;€ (100%)</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6135'>Lot Fluides : DGD</td><td>2.48j / 0.43j (17%)</td><td>1&nbsp;614,06&nbsp;€/1&nbsp;614,06&nbsp;€ (100%)</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6136'>Lot Fluides : APA</td><td>2.48j / 0.23j (9%)</td><td>1&nbsp;614,06&nbsp;€/1&nbsp;614,06&nbsp;€ (100%)</td></tr>
<tr class='oddeven'><td class='colste'  title='missionId 6137'>Lot Fluides : BONIFICATION</td><td>2.36j / 0j (0%)</td><td>1&nbsp;532,00&nbsp;€/1&nbsp;532,00&nbsp;€ (100%)</td></tr>
</tbody><tfoot><tr><td><b>Totaux</b></td><td><b>258.54j / 29.9j (12%)</b></td><td><b>168&nbsp;046,47&nbsp;€  / 168&nbsp;046,43&nbsp;€  (100%)</b></td></tr></ftoot></table>

    [En_attente] => 0.00
    [Liste_tiers] => <div class='listtiers'>
<div class='blktiers tint'><div><b>Chargé d'affaire</b>&nbsp;: <b>AURICHE Fabien</b>  (<span style='color:green'>16.31</span>/59.84j) </div>
<div>Contributeur&nbsp;: DUMIGNARD Benoît  (<span style='color:green'>9.76</span>/59.84j) GINDRAT Manon  (<span style='color:green'>0</span>/59.84j) LARBRE J Jérémy  (<span style='color:green'>2.23</span>/59.84j) SENAQUE Nicolas  (<span style='color:green'>0</span>/59.84j) </div></div>

<div class='blktiers tex'><div>Architecte&nbsp;: <a href="/larbre-prod/htdocs/societe/card.php?socid=757" title="&lt;div class=&quot;centpercent&quot;&gt;&lt;u&gt;Third-party&lt;/u&gt;&lt;br&gt;&lt;b&gt;Name:&lt;/b&gt; Atelier 4 Lim&lt;br&gt;&lt;b&gt;Email:&lt;/b&gt; accueil@atelier-4.fr&lt;br&gt;&lt;b&gt;Country:&lt;/b&gt; FR&lt;br&gt;&lt;b&gt;VAT ID:&lt;/b&gt; FR88513482588&lt;br&gt;&lt;b&gt;Customer Code:&lt;/b&gt; CL00724&lt;br&gt;&lt;b&gt;CustomerAccountancyCode:&lt;/b&gt; 4111ALM&lt;br&gt;&lt;b&gt;Status:&lt;/b&gt; &lt;span class=&quot;badge  badge-status4 badge-status&quot; title=&quot;Open&quot;&gt;Open&lt;/span&gt;&lt;/div&gt;" class="classfortooltip refurl">Atelier 4 Lim</a> </div>
<div>Client&nbsp;: <a href="/larbre-prod/htdocs/societe/card.php?socid=384" title="&lt;div class=&quot;centpercent&quot;&gt;&lt;u&gt;Third-party&lt;/u&gt;&lt;br&gt;&lt;b&gt;Name:&lt;/b&gt; SAS IMMOBILIERE DOMUSVI&lt;br&gt;&lt;b&gt;Email:&lt;/b&gt; &lt;br&gt;&lt;b&gt;Country:&lt;/b&gt; FR&lt;br&gt;&lt;b&gt;Customer Code:&lt;/b&gt; CL00385&lt;br&gt;&lt;b&gt;CustomerAccountancyCode:&lt;/b&gt; 4111DOMUSV&lt;br&gt;&lt;b&gt;Status:&lt;/b&gt; &lt;span class=&quot;badge  badge-status4 badge-status&quot; title=&quot;Open&quot;&gt;Open&lt;/span&gt;&lt;/div&gt;" class="classfortooltip refurl">SAS IMMOBILIERE DOMUSVI</a> </div></div>
</div>
    [tauxvalo] => 650.00000000
    [archi_mandat] => 1
    [Tps_restant] => 228.62000000
    [Tps_prévu] => 258.53000000
    [Tps_valorisé] => 19441.50000000
    [Budg_hors_ss_trait] => 168046.47000000
    [charges] => 0.00000000
    [phase] => 3
    [address] => 
    [codpost] => 79500
    [ville] => MELLE
    [Desc_lots] => * Chauffage/refroidissement : 
 * Plomberie/Sanitaire : 
 * VRD (565 000 €) : 12 000 m2 de terrain d'assiette
10 000 m3 de déblais 
1670 m2 de parking
1250 m2 de béton désactivé 
2010 ml de réseaux divers

 * Electricité :
    [Mt_trav_TCE] => 8250000.00000000
    [datouvchant] => 
    [statenvcertif] => 0
    [cmtenvcertif] => 
    [pubwww] => 1
    [Ids_OPQIBI] => 1103,2202
    [autcertifs] => 
    [etatcertif] => Fiches existantes : <br/><a class="documentdownload paddingright" href="/larbre-prod/htdocs/document.php?modulepart=project&amp;file=2017-176%2F2017-176-EHPAD+MELLE+A3+.pdf"><i class="fa fa-file-pdf-o paddingright" title="Fichier: 2017-176-EHPAD MELLE A3 .pdf"></i>2017-176-EHPAD MELLE A3 .pdf</a><a class="pictopreview documentpreview" href="/larbre-prod/htdocs/document.php?modulepart=project&attachment=0&file=2017-176%2F2017-176-EHPAD+MELLE+A3+.pdf" mime="application/pdf"  target="_blank"><span class="fa fa-search-plus" style="color: gray"></span></a><br/>
    [typindtec1] => m2
    [valindtec1] => 6124
    [typindtec2] => 0
    [valindtec2] => 
    [typindtec3] => 0
    [valindtec3] => 
    [typindtec4] => 0
    [valindtec4] => 
    [besref] => 1
    [typconst] => 1
    [typetud] => 1
    [datrecept] => 
    [hikashoppid] => 410
    [libpole] => Bâtiment
    [catpole] => 6
    [libagence] => Limoges 87
    [catagence] => 90
    [libactivite] => Santé, Ehpad
    [catactivite] => 6,11
    [catmetier] => genclim,vrd,genelec
    [opqibi] => &#149; 1103 - Etude de voiries courantes<br/>&#149; 2202 - Maîtrise des coûts en phase de conception et de réalisation
    [lotsbtiments] => 
    [lotsenvironnement] => 
    [matreduvre] => &#149; Atelier 4 Lim (87068 LIMOGES CEDEX)
)

 */