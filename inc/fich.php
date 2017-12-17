<?php
// *** LICENSE ***
//
// This file is part of C60.
// Since 2016, by Timo Van Neerden.
// C60 is free software, under MIT/X11 Licence.

// THIS FILE
//
// This file contains functions relative to search and list data posts.
// It also contains functions about files : creating, deleting files, etc.

function creer_dossier($dossier, $make_htaccess='') {
	if ( !is_dir($dossier) ) {
		if (mkdir($dossier, 0777) === TRUE) {
			fichier_index($dossier); // fichier index.html pour éviter qu'on puisse lister les fihciers du dossier
			if ($make_htaccess == 1) fichier_htaccess($dossier); // pour éviter qu'on puisse accéder aux fichiers du dossier directement
			return TRUE;
		} else {
			return FALSE;
		}
	}
	return TRUE; // si le dossier existe déjà.
}


function fichier_user() {
	$fichier_user = DIR_CONFIG.'user.ini';
	$content = '';
	if (strlen(trim($_POST['mdp'])) == 0) {
		$new_mdp = USER_PWHASH;
	} else {
		$new_mdp = password_hash($_POST['mdp'], PASSWORD_BCRYPT);
	}
	$content .= '; <?php die(); /*'."\n\n";
	$content .= '; This file contains user login + password hash.'."\n\n";

	$content .= 'USER_LOGIN = \''.addslashes(clean_txt(htmlspecialchars($_POST['identifiant']))).'\''."\n";
	$content .= 'USER_PWHASH = \''.$new_mdp.'\''."\n";

	if (file_put_contents($fichier_user, $content) === FALSE) {
		return FALSE;
	} else {
		return TRUE;
	}
}

function fichier_adv_conf() {
	$fichier_advconf = DIR_CONFIG.'config-advanced.ini';
	$conf='';
	$conf .= '; <?php die(); /*'."\n\n";
	$conf .= '; This file contains some more advanced configuration features.'."\n\n";
	$conf .= 'BLOG_UID = \''.sha1(uniqid(mt_rand(), true)).'\''."\n";
	$conf .= 'DISPLAY_PHP_ERRORS = -1;'."\n";
	$conf .= 'USE_IP_IN_SESSION = 0;'."\n\n\n";
	$conf .= '; */ ?>'."\n";

	if (file_put_contents($fichier_advconf, $conf) === FALSE) {
		return FALSE;
	} else {
		return TRUE;
	}
}


function fichier_prefs() {
	$fichier_prefs = DIR_CONFIG.'prefs.php';
	if(!empty($_POST['_verif_envoi'])) {
		$lang = (isset($_POST['langue']) and preg_match('#^[a-z]{2}$#', $_POST['langue'])) ? $_POST['langue'] : 'fr';
		$auteur = addslashes(clean_txt(htmlspecialchars($_POST['auteur'])));
		$email = addslashes(clean_txt(htmlspecialchars($_POST['email'])));
		$racine = addslashes(trim(htmlspecialchars($_POST['racine'])));
		$format_date = htmlspecialchars($_POST['format_date']);
		$format_heure = htmlspecialchars($_POST['format_heure']);
		$fuseau_horaire = addslashes(clean_txt(htmlspecialchars($_POST['fuseau_horaire'])));
	} else {
		$lang = (isset($_POST['langue']) and preg_match('#^[a-z]{2}$#', $_POST['langue'])) ? $_POST['langue'] : 'fr';
		$auteur = addslashes(clean_txt(htmlspecialchars(USER_LOGIN)));
		$email = 'mail@example.com';
		$racine = addslashes(clean_txt(trim(htmlspecialchars($_POST['racine']))));
		$format_date = '0';
		$format_heure = '0';
		$fuseau_horaire = 'UTC';
	}
	$prefs = "<?php\n";
	$prefs .= "\$GLOBALS['lang'] = '".$lang."';\n";
	$prefs .= "\$GLOBALS['auteur'] = '".$auteur."';\n";
	$prefs .= "\$GLOBALS['email'] = '".$email."';\n";
	$prefs .= "\$GLOBALS['racine'] = '".$racine."';\n";
	$prefs .= "\$GLOBALS['format_date'] = '".$format_date."';\n";
	$prefs .= "\$GLOBALS['format_heure'] = '".$format_heure."';\n";
	$prefs .= "\$GLOBALS['fuseau_horaire'] = '".$fuseau_horaire."';\n";
	$prefs .= "?>";
	if (file_put_contents($fichier_prefs, $prefs) === FALSE) {
		return FALSE;
	} else {
		return TRUE;
	}
}

function fichier_mysql($sgdb) {
	$fichier_mysql = DIR_CONFIG.'mysql.ini';

	$data = '';
	if ($sgdb !== FALSE) {
		$data .= '; <?php die(); /*'."\n\n";
		$data .= '; This file contains MySQL credentials and configuration.'."\n\n";
		$data .= 'MYSQL_LOGIN = \''.htmlentities($_POST['mysql_user'], ENT_QUOTES).'\''."\n";
		$data .= 'MYSQL_PASS = \''.htmlentities($_POST['mysql_passwd'], ENT_QUOTES).'\''."\n";
		$data .= 'MYSQL_DB = \''.htmlentities($_POST['mysql_db'], ENT_QUOTES).'\''."\n";
		$data .= 'MYSQL_HOST = \''.htmlentities($_POST['mysql_host'], ENT_QUOTES).'\''."\n\n";
		$data .= 'DBMS = \''.$sgdb.'\''."\n";
	}

	if (file_put_contents($fichier_mysql, $data) === FALSE) {
		return FALSE;
	} else {
		return TRUE;
	}
}

function fichier_index($dossier) {
	$content = '<html>'."\n";
	$content .= "\t".'<head>'."\n";
	$content .= "\t\t".'<title>Access denied</title>'."\n";
	$content .= "\t".'</head>'."\n";
	$content .= "\t".'<body>'."\n";
	$content .= "\t\t".'<a href="/">Retour a la racine du site</a>'."\n";
	$content .= "\t".'</body>'."\n";
	$content .= '</html>';
	$index_html = $dossier.'/index.html';

	if (file_put_contents($index_html, $content) === FALSE) {
		return FALSE;
	} else {
		return TRUE;
	}
}

function fichier_htaccess($dossier) {
	$content = '<Files *>'."\n";
	$content .= 'Order allow,deny'."\n";
	$content .= 'Deny from all'."\n";
	$content .= '</Files>'."\n";
	$htaccess = $dossier.'/.htaccess';

	if (file_put_contents($htaccess, $content) === FALSE) {
		return FALSE;
	} else {
		return TRUE;
	}
}


function liste_themes() {
	if ( $ouverture = opendir(DIR_THEMES) ) {
		while ($dossiers = readdir($ouverture) ) {
			if ( file_exists(DIR_THEMES.$dossiers.'/list.html') ) {
				$themes[$dossiers] = $dossiers;
			}
		}
		closedir($ouverture);
	}
	if (isset($themes)) {
		return $themes;
	}
}



// à partir de l’extension du fichier, trouve le "type" correspondant.
// les "type" et le tableau des extensions est le $GLOBALS['files_ext'] dans conf.php
function detection_type_fichier($extension) {
	$good_type = 'other'; // par défaut
	foreach($GLOBALS['files_ext'] as $type => $exts) {
		if ( in_array($extension, $exts) ) {
			$good_type = $type;
			break; // sort du foreach au premier 'match'
		}
	}
	return $good_type;
}


function open_serialzd_file($fichier) {
	$liste  = (file_exists($fichier)) ? unserialize(base64_decode(substr(file_get_contents($fichier), strlen('<?php /* '), -strlen(' */')))) : array();
	return $liste;
}

// $feeds is an array of URLs: Array( [http://…], [http://…], …)
// Returns the same array: Array([http://…] [[headers]=> 'string', [body]=> 'string'], …)
function request_external_files($feeds, $timeout, $echo_progress=false) {
	// uses chunks of 30 feeds because Curl has problems with too big (~150) "multi" requests.
	$chunks = array_chunk($feeds, 30, true);
	$results = array();
	$total_feed = count($feeds);
	if ($echo_progress === true) {
		echo '0/'.$total_feed.' '; ob_flush(); flush(); // for Ajax
	}

	foreach ($chunks as $chunk) {
		set_time_limit(30);
		$curl_arr = array();
		$master = curl_multi_init();
		$total_feed_chunk = count($chunk)+count($results);

		// init each url
		foreach ($chunk as $i => $url) {

			$curl_arr[$url] = curl_init(trim($url));
			curl_setopt_array($curl_arr[$url], array(
					CURLOPT_RETURNTRANSFER => TRUE, // force Curl to return data instead of displaying it
					CURLOPT_FOLLOWLOCATION => TRUE, // follow 302 ans 301 redirects
					CURLOPT_CONNECTTIMEOUT => 100, // 0 = indefinately ; no connection-timeout (ruled out by "set_time_limit" hereabove)
					CURLOPT_TIMEOUT => $timeout, // downloading timeout
					CURLOPT_USERAGENT => BLOGOTEXT_UA, // User-agent (uses the UA of browser)
					CURLOPT_SSL_VERIFYPEER => FALSE, // ignore SSL errors
					CURLOPT_SSL_VERIFYHOST => FALSE, // ignore SSL errors
					CURLOPT_ENCODING => "gzip", // take into account gziped pages
					//CURLOPT_VERBOSE => 1,
					CURLOPT_HEADER => 1, // also return header
				));
			curl_multi_add_handle($master, $curl_arr[$url]);
		}

		// exec connexions
		$running = $oldrunning = 0;

		do {
			curl_multi_exec($master, $running);

			if ($echo_progress === true) {
				// echoes the nb of feeds remaining
				echo ($total_feed_chunk-$running).'/'.$total_feed.' '; ob_flush(); flush();
			}
			usleep(100000);
		} while ($running > 0);


		// multi select contents
		foreach ($chunk as $i => $url) {
			$response = curl_multi_getcontent($curl_arr[$url]);
			$header_size = curl_getinfo($curl_arr[$url], CURLINFO_HEADER_SIZE);
			$results[$url]['headers'] = http_parse_headers(mb_strtolower(substr($response, 0, $header_size)));
			$results[$url]['body'] = trim(substr($response, $header_size));
		}
		// Ferme les gestionnaires
		curl_multi_close($master);
	}
	return $results;
}


function rafraichir_cache_lv1() {
	creer_dossier(DIR_CACHE, 1);
	creer_dossier(DIR_CACHE.'static/', 1);
	$arr_a = liste_elements("SELECT * FROM articles WHERE bt_statut=1 ORDER BY bt_date DESC LIMIT 0, 20", array(), 'articles');
	$arr_c = liste_elements("SELECT c.*, a.bt_title FROM commentaires AS c, articles AS a WHERE c.bt_statut=1 AND c.bt_article_id=a.bt_id ORDER BY c.bt_id DESC LIMIT 0, 20", array(), 'commentaires');
	$arr_l = liste_elements("SELECT * FROM links WHERE bt_statut=1 ORDER BY bt_id DESC LIMIT 0, 20", array(), 'links');
	$file = DIR_CACHE.'static/cache_rss_array.dat';
	return file_put_contents($file, '<?php /* '.chunk_split(base64_encode(serialize(array('c' => $arr_c, 'a' => $arr_a, 'l' => $arr_l)))).' */');
}


/* retrieve all the feeds, returns the amount of new elements */
function refresh_rss($feeds) {
	$new_feed_elems = array();
	$guid_in_db = rss_list_guid();
	$count_new = 0;
	$total_feed = count($feeds);

	$retrieved_elements = retrieve_new_feeds(array_keys($feeds));

	if (!$retrieved_elements) return 0;

	foreach ($retrieved_elements as $feed_url => $feed_elmts) {
		if ($feed_elmts === FALSE) {
			continue;
		} else {
			// there are new posts in the feed (md5 test on feed content file is positive). Now test on each post.
			// only keep new post that are not in DB (in $guid_in_db) OR that are newer than the last post ever retreived.
			foreach($feed_elmts['items'] as $key => $item) {
				if ( (in_array($item['bt_id'], $guid_in_db)) or ($item['bt_date'] <= $feeds[$feed_url]['time']) ) {
					unset($feed_elmts['items'][$key]);
				} else { // init bt_statut & bt_bbokmarked
					$feed_elmts['items'][$key]['bt_statut'] = 1;
					$feed_elmts['items'][$key]['bt_bookmarked'] = 0;

				}
				// only save elements that are more recent
				// we save the date of the last element on that feed
				// we do not use the time of last retreiving, because it might not be correct due to different time-zones with the feeds date.
				if ($item['bt_date'] > $GLOBALS['liste_flux'][$feeds[$feed_url]['link']]['time']) {
					$GLOBALS['liste_flux'][$feeds[$feed_url]['link']]['time'] = $item['bt_date'];
				}
			}
			if (!empty($feed_elmts['items'])) {
				// populates the list of post we keep, to be saved in DB
				$new_feed_elems = array_merge($new_feed_elems, $feed_elmts['items']);
			}
		}
	}

	// if list of new elements is !empty, save new elements
	if (!empty($new_feed_elems)) {
		$count_new = count($new_feed_elems);
		$ret = bdd_rss($new_feed_elems, 'enregistrer-nouveau');
		if ($ret !== TRUE) {
			echo $ret;
		}
	}

	// save last success time in the feed list
	file_put_contents(FEEDS_DB, '<?php /* '.chunk_split(base64_encode(serialize($GLOBALS['liste_flux']))).' */');

	// returns the new feeds in JSON format

	return $new_feed_elems;
	//return $count_new;
}



function retrieve_new_feeds($feedlinks, $md5='') {
	if (!$feeds = request_external_files($feedlinks, 25, true)) { // timeout = 25s
		return FALSE;
	}
	$return = array();
	foreach ($feeds as $url => $response) {
		if (!empty($response['body'])) {
			$new_md5 = hash('md5', $response['body']);
			// if Feed has changed : parse it (otherwise, do nothing : no need)
			if ($md5 != $new_md5 or '' == $md5) {
				$data_array = feed2array($response['body'], $url);
				if ($data_array !== FALSE) {
					$return[$url] = $data_array;
					$data_array['infos']['md5'] = $md5;
					// update RSSs of 30 feeds because Curl has problems with too big (~150) "multi" requests. last successfull update MD5
					$GLOBALS['liste_flux'][$url]['checksum'] = $new_md5;
					$GLOBALS['liste_flux'][$url]['iserror'] = 0;
				} else {
					if (isset($GLOBALS['liste_flux'][$url])) { // error on feed update (else would be on adding new feed)
						$GLOBALS['liste_flux'][$url]['iserror'] += 1;
					}
				}
			}
		}
	}

	if (!empty($return)) return $return;
	return FALSE;
}


# Based upon Feed-2-array, by bronco@warriordudimanche.net
function feed2array($feed_content, $feedlink) {
	$flux = array('infos'=>array(),'items'=>array());

	if (preg_match('#<rss(.*)</rss>#si', $feed_content)) { $flux['infos']['type'] = 'RSS'; } //RSS ?
	elseif (preg_match('#<feed(.*)</feed>#si', $feed_content)) { $flux['infos']['type'] = 'ATOM'; } //ATOM ?
	else { return false; } // the feed isn't rss nor atom

	try {
		if (@$feed_obj = new SimpleXMLElement($feed_content, LIBXML_NOCDATA)) {
			$flux['infos']['version']=$feed_obj->attributes()->version;
			if (!empty($feed_obj->channel->title)) { $flux['infos']['title'] = (string)$feed_obj->channel->title; }
			if (!empty($feed_obj->title)) {          $flux['infos']['title'] = (string)$feed_obj->title; }
			if (!empty($feed_obj->channel->link)) { $flux['infos']['link'] = (string)$feed_obj->channel->link; }
			if (!empty($feed_obj->link)) {          $flux['infos']['link'] = (string)$feed_obj->link; }

			if (!empty($feed_obj->channel->item)){ $items = $feed_obj->channel->item; }
			if (!empty($feed_obj->entry)){ $items = $feed_obj->entry; }

			if (empty($items)) { return $flux; }

			foreach ($items as $item) {
				$c = count($flux['items']);
				// title
				if (!empty($item->title)) { $flux['items'][$c]['bt_title'] = html_entity_decode((string)$item->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');}
				if (empty($flux['items'][$c]['bt_title'])) {
					$flux['items'][$c]['bt_title'] = "-";
				}

				// link
				if (!empty($item->link['href'])) {  $flux['items'][$c]['bt_link'] = (string)$item->link['href']; }
				if (!empty($item->link)) {          $flux['items'][$c]['bt_link'] = (string)$item->link; }

				// id
				if (!empty($item->id)) { $flux['items'][$c]['bt_id'] = (string)$item->id; }
				if (!empty($item->guid)) {   $flux['items'][$c]['bt_id'] = (string)$item->guid; }
				if (empty($flux['items'][$c]['bt_id'])) {
					$flux['items'][$c]['bt_id'] = microtime();
				}
				$flux['items'][$c]['bt_id'] = hash('crc32', $flux['items'][$c]['bt_id']);

				// date
				if (!empty($item->updated)) {       $flux['items'][$c]['bt_date'] = date('YmdHis', strtotime((string)$item->updated)); }
				if (!empty($item->pubDate)) {       $flux['items'][$c]['bt_date'] = date('YmdHis', strtotime((string)$item->pubDate)); }
				if (!empty($item->published)) {     $flux['items'][$c]['bt_date'] = date('YmdHis', strtotime((string)$item->published)); }
				if (empty($flux['items'][$c]['bt_date'])) {
					$flux['items'][$c]['bt_date'] = date('YmdHis');
				}

				// content
				//if (!empty($item->subtitle)) {      $flux['items'][$c]['bt_content'] = (string)$item->subtitle; }
				//if (!empty($item->summary)) {       $flux['items'][$c]['bt_content'] = (string)$item->summary; }
				if (!empty($item->description)) {   $flux['items'][$c]['bt_content'] = (string)$item->description; }
				if (!empty($item->content)) {       $flux['items'][$c]['bt_content'] = (string)$item->content; }
				if (!empty($item->children('content', true)->encoded)) { $flux['items'][$c]['bt_content'] = (string)$item->children('content', true)->encoded; }
				if (empty($flux['items'][$c]['bt_content'])) $flux['items'][$c]['bt_content'] = '-';

				// place le lien du flux (on a besoin de ça)
				$flux['items'][$c]['bt_feed'] = $feedlink;
				// place le dossier
				$flux['items'][$c]['bt_folder'] = (isset($GLOBALS['liste_flux'][$feedlink]['folder']) ? $GLOBALS['liste_flux'][$feedlink]['folder'] : '' ) ;

			}
		} else {
			return false;
		}

		return $flux;

	} catch (Exception $e) {
		echo $e-> getMessage();
		echo ' '.$feedlink." \n";
		return false;
	}
}

/* From the data out of DB, creates JSON, to send to browser
*/
function send_rss_json($rss_entries, $enclose_in_script_tag) {
	// send all the entries data in a JSON format
	$out = '';

	// RSS entries
	$out .= '['."\n";
	$count = count($rss_entries)-1;
	foreach ($rss_entries as $i => $entry) {
		$out .= '{'.
			'"id":'.json_encode($entry['bt_id']).', '.
			'"datetime":'.json_encode($entry['bt_date']).', '.
			'"feedhash":'.json_encode(crc32($entry['bt_feed'])).', '.
			'"statut":'.$entry['bt_statut'].', '.
			'"fav":'.$entry['bt_bookmarked'].', '.
			'"title":'.json_encode($entry['bt_title']).', '.
			'"link":'.json_encode($entry['bt_link']).', '.
			'"sitename":'.json_encode($GLOBALS['liste_flux'][$entry['bt_feed']]['title']).', '.
			'"folder":'.json_encode($GLOBALS['liste_flux'][$entry['bt_feed']]['folder']).', '.
			'"content":'.json_encode($entry['bt_content']).''.
//			'"content":'.json_encode('').''.
		'}'.(($count==$i) ? '' :',')."\n";
	}
	$out .= ']';

	if ($enclose_in_script_tag) {
		$out = '<script type="text/javascript">'.'var rss_entries = {"list": '.$out."\n".'}'.'</script>'."\n";
	}
	return $out;
}


if (!function_exists('http_parse_headers')) {
	function http_parse_headers($raw_headers) {
		$headers = array();

		$array_headers = (is_array($raw_headers) ? $raw_headers : explode("\n", $raw_headers));

		foreach ($array_headers as $i => $h) {
			$h = explode(':', $h, 2);

			if (isset($h[1])) {
				$headers[$h[0]] = trim($h[1]);
			}
		}
		return $headers;
	}
}
