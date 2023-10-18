<?php
/* voir fonction vignette ligne #485 de /htdocs/core/lib/images.lib.php
 * pour la génération des images 
 */
include ("./main.inc.php");
include ("./geoencode.php");
$csv=";";
//echo header_html("MAJ partielles de tables depuis la DRH");

if ($_REQUEST['Hello']) {
	echo $conf->sitename." speaking : ".$_REQUEST['Hello'];
}

$ret = [];

if ($_POST['majProj']) {
	$ret['debug'] = " POST['majProj'] speaking on ".$_SERVER['HTTP_HOST']; //.$_POST['majProj'];
	$rwProj = json_decode($_POST['majProj'], true);
	if ($rwProj !== null) {
		$rwProj['hikashoppid'] = (int)$rwProj['hikashoppid'] + 0;
		$ret['hikashoppid'] = crupChantier($rwProj['hikashoppid'], $rwProj);
	} else {
		$ret['error'] = "JSON Eror";
	}
	echo json_encode($ret);
	die();
}
//print_r($_FILES);
//
// gestion des images
// en deux fois : d'abord les copies, qui peuvent ne se faire que fichier par fichir
// puis via le POST['cleanFiles'] on renseigne les contents via CrupChamp
// path des images : /public/files/imagesChantier/
if (count($_FILES) > 0) {
//	print_r($_FILES);
//	 [0-82] => Array
//        (
//            [name] => 2018-144-1.jpg
//            [type] => image/jpeg
//            [tmp_name] => /tmp/php1J0noi
//            [error] => 0
//            [size] => 193291
//        )
	$tbfp = $tbff = [];
	foreach ($_FILES as $kf=>$file) {
		
		$ret[] = "fichier recu $kf : ".$file['name'];
		copy($file['tmp_name'], __DIR__."/../files/imagesChantier/".$file['name']);
		/*
		list($order, $idC) = explode("-", $kf);
		$fileinf = pathinfo($file['name']);
		if (strtolower($fileinf['extension']) == 'pdf') {
			$tbff[] = $file['name'];
		} else {
			$tbfp[] = $file['name'];
		}*/
	}
	/* print_r($tbfp);
	if (count($tbfp) > 0) {
		$tbfp4js = [];
		//[{"0": "IMG_20230727_145305.jpg", "alt": "", "media": "", "filename": "imagesChantier/IMG-20230727-145305.jpg"}, {"1": "IMG_20230727_145305.jpg", "alt": "", "media": "", "filename": "imagesChantier/IMG-20230727-145305.jpg"}]
		$i = 0;
		foreach($tbfp as $fn) {
			$tbfp4js[] = [$i => $fn, 'alt' => '', 'media' => '', 'filename' => 'imagesChantier/'.$fn];
			$i ++;
		}
		$ret[] = crupChamp($idC, 'gallery', 'imagelist', json_encode($tbfp4js));
	} else {
		$ret[] = "Champ gallery non mis à jour";
	}*/
	echo json_encode($ret);
	die();
}

/* renseignement du content */

if ($_POST['cleanFiles']) {
	$tblistfiles = json_decode($_POST['cleanFiles'], true);
	if (is_array($tblistfiles)) {
		foreach ($tblistfiles as $idC => $tbf) {
			foreach ($tbf as $k=>$f) {
				$fileinf = pathinfo($f);
				if (strtolower($fileinf['extension']) == 'pdf') {
					$tbfiles[] = $f;
				} else {
					$tbphotos[] = $f;
				}
			}
		}
		$ret[] = majChpGaleryOrFilelist($idC, 'gallery', $tbphotos);
		$ret[] = majChpGaleryOrFilelist($idC, 'files', $tbfiles);
	} else {
		$ret[] = "JSON Eror";
	}
	echo json_encode($ret);
	die();
}

die();
die("working progress");
include ("./main.inc.php");
include ("./geoencode.php");
$csv=";";
//echo header_html("MAJ partielles de tables depuis la DRH");

if ($_REQUEST['Hello']) {
	echo $conf->sitename." speaking : ".$_REQUEST['Hello'];
}

if ($_POST['majProj']) {
	$ret = [];
	$ret['debug'] = $conf->sitename." speaking on ".$_SERVER['HTTP_HOST']; //.$_POST['majProj'];
	$rwProj = json_decode($_POST['majProj'], true);
	if ($rwProj !== null) {
		$rwProj['hikashoppid'] = (int) $rwProj['hikashoppid'] + 0;
		$tbi['product_description'] = "<h2>Caractéristiques de l'opération :</h2>". nl2br($rwProj['Desc_lots']);
		$tbi['product_name'] = $rwProj['title'];
		$tbi['product_alias'] = createSlug(cv2purasci2($rwProj['title']));
		$tbi['product_meta_description'] = nl2br($rwProj['description']);
		$tbi['product_code'] = $rwProj['ref'];
		$tbi['product_published'] = $rwProj['pubwww'];
		$tbi['adresseduchantier'] = (trim($rwProj['address']) ? trim($rwProj['address']).'<br/>' : '').(trim($rwProj['codpost']) ? trim($rwProj['codpost']).' ' : '').trim($rwProj['ville']);
		if (trim($rwProj['codpost']) && $rwProj['codpost'] > 0) {
			$address = str_ireplace('<br/>', ' ', $tbi['adresseduchantier']);
			//echo "The Address $address";
			$tbi['latlong'] = geoencode(trim($rwProj['codpost']) + 0, $address);
		}
		$tbi['datedecrationdelaffaire'] = $rwProj['dateo'] ? dateF($rwProj['dateo']) : '';
		$tbi['datederception'] = $rwProj['datrecept'] ? dateF($rwProj['datrecept']) : '';
		$tbi['montantdeshonoraires'] = $rwProj['Budg_hors_ss_trait'] > 0 ? number_format($rwProj['Budg_hors_ss_trait'], 0, ',', ' ').' €' : '';
		$tbi['montantdestravaux'] = $rwProj['Mt_trav_TCE'] > 0 ? number_format($rwProj['Mt_trav_TCE'], 0, ',', ' ').' €' : '';
		$tbi['lotsbtiments'] = $rwProj['lotsbtiments'];
		$tbi['lotsenvironnement'] = $rwProj['lotsenvironnement'];
		$tbi['matreduvre'] = $rwProj['matreduvre'];
		$tbi['maitredouvrage'] = $rwProj['maitredouvrage'];
		
		$tbi['opqibi'] = $rwProj['opqibi'];
		
		// correspondance categ (et agence=brand)) entre dolibarr et hikashop
		$tbCorrCats = db_qr_compres4ld("select category_dol_ids,category_id from rcej1_hikashop_category where trim(category_dol_ids) <> ''");
		// maintenant on peut avoir plusieurs catégories dolibarr mappées dans une categ joomla
		foreach ($tbCorrCats as $category_dol_ids=>$category_id) {
			if (strstr($category_dol_ids, ',')) {
				$tbCatDolids = explode(",",$category_dol_ids);
				unset($tbCorrCats[$category_dol_ids]);
				foreach ($tbCatDolids as $catd) $tbCorrCats[$catd] = $category_id;
			}
		}
		// la brand est stockée dans les categ, mais mappée directemenr dans product
		if ($tbCorrCats[$rwProj['catagence']] > 0) $tbi['product_manufacturer_id'] = $tbCorrCats[$rwProj['catagence']];
		
		if ($rwProj['hikashoppid'] > 0 && $rwProj['pubwww'] == 1) { // test d'abord que le produit existe bien et qu'il n'a pas été supprimé à l'arrache depuis Joomla (s'il faut le synchroniser)
			$testPexists = db_qr_1val("select product_id from rcej1_hikashop_product where product_id=".$rwProj['hikashoppid']);
			if (!$testPexists) $rwProj['hikashoppid'] = 0; // s'il existe pas on le recrée
		}
		if ($rwProj['hikashoppid'] > 0) { // update
			$ret['hikashoppid'] = $rwProj['hikashoppid'];
			db_query("update rcej1_hikashop_product set ". tbset2set($tbi, 2)." where product_id=".$rwProj['hikashoppid']);
			$ret['info'] = "Fiche product_id=".$rwProj['hikashoppid']." Mise à jour !";
		} elseif ($rwProj['pubwww'] == 1) {
			$tbi['product_type'] = 'main'; // Important sinon y s'affiche pas
			db_query("insert into rcej1_hikashop_product ". tbset2insert($tbi, 2));
			$ret['hikashoppid'] = db_last_id();
			$ret['info'] = "Fiche product_id=".$ret['hikashoppid']." créée !";
		} else {
			$ret['info'] = "Aucune action effectuée, fiche inexistante et non à synchroniser";
		}
		
		// gestion des catégories
		if ($rwProj['pubwww'] == 1) {
			$tbcatw = array();
			foreach (explode(",",$rwProj['catactivite']) as $idCatDol) {
				//$idCatDol = (int)$idCatDol; // bah non car il y a des cats qui sont des clés
				if (!empty($idCatDol) && $tbCorrCats[$idCatDol]>0 && $idCatDol != $rwProj['catagence']) {
					$tbcatw[] = $tbCorrCats[$idCatDol];
				}
			}
			if (count($tbcatw)>0) {
				$ret['debug'] .= "\n tb cats ".var_export($tbcatw, true);
				foreach ($tbcatw as $catw) {
					// on ne créer la correspondance que si elle existe pas déjà 
					if (empty(db_qr_1val("select product_category_id from rcej1_hikashop_product_category where product_id={$ret['hikashoppid']} and category_id=$catw"))) {
						db_query("insert into rcej1_hikashop_product_category ".tbset2insert(array("product_id" => $ret['hikashoppid'], "category_id" => $catw)));
					}
				}
				$ret['info'] .= "\n".count($tbcatw)." catégorie(s) affectée(s) à ce produit";
				$incatw = " and category_id not in(".implode(',', $tbcatw).")"; 
			} else $incatw = "";
			// et maintenant on efface les categs plus mappées à ce prod
			db_query("delete from rcej1_hikashop_product_category where product_id = ".$rwProj['hikashoppid'].$incatw);
		}
	} else {
		$ret['error'] = "JSON Eror";
	}
	echo json_encode($ret);
	die();
}

// gestion des images
// path des images : ~/www/site-dev/images/com_hikashop/upload/
//					~/www/site-dev/images/com_hikashop/upload/thumbnails/100x100/, etc ...
if (count($_FILES) > 0) {
//	print_r($_FILES);
//	 [0-82] => Array
//        (
//            [name] => 2018-144-1.jpg
//            [type] => image/jpeg
//            [tmp_name] => /tmp/php1J0noi
//            [error] => 0
//            [size] => 193291
//        )
	$tbfp = [];
	foreach ($_FILES as $kf=>$file) {
		
		$ret .= "\nfichier reçu $kf : ".$file['name'];
		copy($file['tmp_name'], __DIR__."/../images/com_hikashop/upload/".$file['name']);
		/*
		 *  file_id	file_name	file_description	file_path		file_type	file_ref_id	file_free_download	file_ordering	file_limit
			38		2011-107_3						2011-107_3.jpg	product		8			0					1				0
		 */
		list($order, $pid) = explode("-", $kf);
		list($fn, $bid)= explode(".", $file['name']);
		$tbi['file_name'] = $fn;
		$tbi['file_path'] = $file['name'];
		$tbi['file_type'] = 'product';
		$tbi['file_ref_id'] = $pid;
		$tbi['file_free_download'] = 0;
		$tbi['file_ordering'] = $order;
		$tbi['file_limit'] = 0;
		if($fid = RecupLib('rcej1_hikashop_file', 'file_path', 'file_id', addslashes($file['name']))) {
			db_query("update rcej1_hikashop_file set ".tbset2set($tbi,2)." where file_id = $fid");
			$ret .= "\n maj fileid = $fid ($order)";
		} else {
			db_query("insert into rcej1_hikashop_file ". tbset2insert($tbi,2));
			$fid = db_last_id();
			$ret .= "\n création fileid = $fid ($order)";
		}
	}
	echo $ret;
	die();
}

// effacement des fichiers images par produit qui n'ont pas été uploadés => ils ont été supprimés

if ($_POST['cleanFiles']) {
	$tbfp = json_decode($_POST['cleanFiles'], true);
	if (is_array($tbfp)) {
		foreach ($tbfp as $pid => $tbf) {
			foreach ($tbf as $k=>$f) $tbf[$k] = addslashes($f);
			$listf = implode("','", $tbf);
			$reqDelFich = "delete from rcej1_hikashop_file where file_ref_id=$pid and file_path not in ('$listf')";
			db_query($reqDelFich);
			$ret .= "$reqDelFich \n";
		}
	} else {
		$ret = "JSON Eror";
	}
	echo $ret;
	die();
}

function createSlug($str, $delimiter = '-') {
    $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
    return $slug;
} 

die();

/*****************************************************************/

if ($_FILES['divdump4gdp2']) {
	echo "<h3>Import tables UNITE_FONCTION et consort depuis GRH2</h3>";
	if (!file_exists($_FILES['divdump4gdp2']['tmp_name'])) {
		echo "<h4>Erreur : Impossible d'ouvrir le fichier !! </h4>";
	} else {
		$cmd ="mysql -u $DB_User -p$DB_Pwd -h $DB_Host < ".$_FILES['divdump4gdp2']['tmp_name'];
		echo $cmd."\n";
/*		$timeav = time();
		echo "time avant".$timeav;*/
		exec ($cmd,$out,$return);
/*		while (time() < ($timeav + 15)) {};
		echo "time apres".time();   */
		echo "<BR/>resultat:".print_r($out);
		print_r($return);

	}
	if ($_REQUEST['otherpost']) {
		echo "YA DOTRES CHOSES...la liste des tables<br/>";
		$_REQUEST['otherpost'] = unserialize($_REQUEST['otherpost']);
		print_r($_REQUEST['otherpost']);
		$ltbex = db_show_tables($DB); // liste des tables existantes
		foreach ($_REQUEST['otherpost'] as $tbl) {
			if (in_array($tbl, $ltbex)) { // on fait ça car des fois le fichier de dump est quasi vide (pb de droits mysq), et du coup on efface les tables et après ça plante; cf ano GDP2VA n°323
				db_query("DROP TABLE IF EXISTS zgrh_$tbl");
				db_query("RENAME TABLE $tbl TO zgrh_$tbl");
			} else {
				echo "Erreur sur la table $tbl, elle n'a pas dû être dumpée depuis BCASTRE...<br/>\n";
			}
		}
	}
}
if ($_FILES['valomoymetier']) {
	echo "<h3>Import valos par metier depuis GRH2</h3>";
	if (!($handle = fopen($_FILES['valomoymetier']['tmp_name'],"rb"))) {
		echo "<h4>Erreur : Impossible d'ouvrir le fichier !! </h4>";
	} else {
		echo "<pre>";
		$first = true;
		while (!feof($handle)) {
			$tb = traiteLine(fgets($handle));
			$nolig++;
			if ($first) { // entete= liste des champs à maj
				$tblchp = $tb;
				$first = false;
			} else { // valeurs
				if ($tb[0] > 0 ) {
					for ($i=0;$i<count($tb);$i++) $tbh[$tblchp[$i]] = $tb[$i];
					//print_r($tbh);
		// COM_NUCOMPET  	 COM_COCOMPET  	 COM_LLCOMPET  	 COM_TXDESCR_COMPET  	 COM_LCNIV_COMPET  	 COM_NUTX_VALO  	 COM_DTCREA  	 COM_DTMAJ  	 COM_COOPE
		//NUMETIER;LCMETIER;LLMETIER;FLVALO
					
					$tbi = array();
					$tbi['COM_NUCOMPET'] = $tbh['NUMETIER'];
					$tbi['COM_COCOMPET'] = $tbh['LCMETIER'];
					$tbi['COM_LLCOMPET'] = $tbh['LLMETIER'];
					$tbi['COM_NUTX_VALO'] = $tbh['FLVALO'];
					$tbi['COM_COOPE'] = 'moufifromgrh2';
					$tbi['COM_DTMAJ'] = date("Y-m-d");
					try  {
						db_query(insertondkupdate('COMPETENCES', $tbi, $tbi, 2));
					} catch (Exception $e) {
						echo "Exception $e";
					}
					/*
					if (RecupLib("COMPETENCES","COM_NUCOMPET","COM_COCOMPET",$tb[0])) {
						$requptd = "UPDATE COMPETENCES SET COM_COCOMPET='".addslashes($tbh["LCMETIER"])."',COM_LLCOMPET='".addslashes($tbh["LLMETIER"])."',COM_NUTX_VALO=".round($tbh["FLVALO"]).", COM_COOPE='moufifromgrh2',COM_DTMAJ='".date("Y-m-d")."' WHERE COM_NUCOMPET = ".$tbh["NUMETIER"];
						echo $requptd."\n";
						db_query($requptd);
					} else {
						$requptd = "INSERT INTO COMPETENCES SET COM_NUCOMPET = ".$tbh["NUMETIER"].", COM_COCOMPET='".addslashes($tbh["LCMETIER"])."',COM_LLCOMPET='".addslashes($tbh["LLMETIER"])."',COM_NUTX_VALO=".($tbh["FLVALO"]).", COM_COOPE='moufifromgrh2',COM_DTMAJ='".date("Y-m-d")."',COM_DTCREA = '".date("Y-m-d")."'";
						echo $requptd."\n";
						db_query($requptd);
					}*/
				} else break;
			}
		} // fin boucle
		
		echo "<h3>Import valos par metier depuis GRH2 termine</h3>";
	}
}
if ($_FILES['person2gdp2']) {
	echo "<h3>MAJ Personnes depuis GRH2</h3>";
	if (!($handle = fopen($_FILES['person2gdp2']['tmp_name'],"rb"))) {
		echo "<h4>Erreur : Impossible d'ouvrir le fichier !! </h4>";
	} else {
		echo "<pre>";
		echo "C'est parti !\n";
		$first = true;
		while (!feof($handle)) {
			$tb = traiteLine(fgets($handle));
			$nolig++;
			if ($first) { // entete= liste des champs à maj
				$tblchp = $tb;
				$first = false;
			} else { // valeurs
				if ($tb[0] != "" ) {
					for ($i=0;$i<count($tb);$i++) $tbh[$tblchp[$i]] = addslashes($tb[$i]);
					//print_r($tbh);
//  PER_UIDPERS   	 PER_LMTITREPER   	 PER_LLNOMPERS   	 PER_LLPRENOMPERS   	 PER_TELFIXE   	 PER_FAX   	 PER_PORPERS   	 PER_LCABREGE   	 PER_MAILPERS   	 PER_NUSOURCE_EXT   	 PER_IDSTRUCT   	 PER_BOSYNC   	 PER_PHOTO   	 PER_NUMETIER   	 PER_DTCREA   	 PER_DTMAJ   	 PER_COOPE

//"PER_UIDPERS".$csv."PER_LMTITREPER".$csv."PER_LLNOMPERS".$csv."PER_LLPRENOMPERS".$csv."PER_TELFIXE".$csv."PER_LCABREGE".$csv."PER_FAX".$csv."PER_PORPERS".$csv."PER_MAILPERS".$csv."PER_NUMETIER"."\r\n";
					// si personne désactivée, la met à nusourceext = 0, ou sis non défini car BCASTRE n'a pas été mis à jour
					$nusourceext = ($tbh['PER_COACTIVE'] == 'O' || $tbh['PER_COACTIVE'] == '') ? nuSourceInternalUsers : 0;
					$set = "PER_NUPERS=".$tbh['PER_NUPERS'].",PER_LMTITREPER='".$tbh['PER_LMTITREPER']."',PER_LLNOMPERS='".$tbh['PER_LLNOMPERS']."',PER_LLPRENOMPERS='".$tbh['PER_LLPRENOMPERS']."',
						PER_TELFIXE='".$tbh['PER_TELFIXE']."',PER_FAX='".$tbh['PER_FAX']."',PER_PORPERS='".$tbh['PER_PORPERS']."',PER_LCABREGE='".$tbh['PER_LCABREGE']."',
						PER_MAILPERS='".$tbh['PER_MAILPERS']."',PER_NUSOURCE_EXT=".$nusourceext.",PER_NUMETIER='".$tbh['PER_NUMETIER']."',PER_DTMAJ='".date("Y-m-d")."',PER_COOPE='moufifromgrh2'";
					// on ne crée que si la personne n'existe pas ds la base locale et est active
					if (!RecupLib("PERSONNE", "PER_UIDPERS", "PER_LLNOMPERS", $tbh['PER_UIDPERS']) && $tbh['PER_COACTIVE'] == 'O') {
						$requptd = "INSERT INTO PERSONNE SET PER_UIDPERS='".$tbh['PER_UIDPERS']."',PER_DTCREA='".date("Y-m-d")."',".$set." ON DUPLICATE KEY UPDATE $set";
					} else $requptd = "UPDATE PERSONNE SET ".$set." where PER_UIDPERS='".$tbh['PER_UIDPERS']."'"; 
					//echo $requptd;
					echo $tbh['PER_UIDPERS']." => PER_NUPERS=".$tbh['PER_NUPERS'].",PER_LMTITREPER='".$tbh['PER_LMTITREPER']."',PER_LLNOMPERS='".$tbh['PER_LLNOMPERS']."',PER_LLPRENOMPERS='".$tbh['PER_LLPRENOMPERS']."\n";
					db_query($requptd);
					if (alfrescoDocs) {
						alf_checkRH($tbh['PER_UIDPERS']);
						echo "   >Maj dans Alfresco..\n";
					}
				} else break;
			}
		} // fin boucle
		
		echo "<h3>MAJ Personnes depuis GRH2 termine</h3>";
	}
}

