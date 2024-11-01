<?php
class Logic{
	Protected $dati = array();
	Protected $upgCore = array();
	Protected $upgPlugins = array();
	Protected $upgTheme = array();
	Protected $datas = array();
	
	private function creaMail(){
		if(get_option('wun_wp','0') != '1'){
			$id_user = get_option('wp_panel_code');
			$url_blog = get_bloginfo('url');
			$wpu = null;
			$wpu = get_site_transient('update_core');
			if($wpu!=null){
				foreach ($wpu->updates as $wu) {
					if(version_compare($wu->current,get_bloginfo('version')) > 0) {
						if($wu->locale != 'en_US'){
							$this->add('Wordpress en_US',
								'Wordpress Core',
								get_bloginfo('version'),
								$wu->current,
								$wu->url);
								$this->datas['Wordpress '.$wu->locale] = $wu->current;
						} else {
							$this->add('Wordpress '.get_bloginfo('language'),
								'Wordpress Core',
								get_bloginfo('version'),
								$wu->current,
								'http://wordpress.org/download/');
								$this->datas['Wordpress '.get_bloginfo('language')] = get_bloginfo('version');
						}
					}
					$this->upgCore = $this->dati;
				}
			}		
		}
		if(get_option('wun_plugins','0') != '1'){
			unset($this->dati);
			$all_plugins = get_plugins();
			$current = get_site_transient( 'update_plugins' );
			
			foreach ( (array)$all_plugins as $plugin_file => $plugin_data) {
				if ( isset($current->response[ $plugin_file ]->new_version)) {
					$this->add($plugin_data['Name'],
						'plugin',
						$plugin_data['Version'],
						$current->response[ $plugin_file ]->new_version,
						$plugin_data['PluginURI']);
						$this->datas[$plugin_data['Name']] = $current->response[ $plugin_file ]->new_version;
				} else {
				/*	$this->add($plugin_data['Name'],
						'plugin',
						$plugin_data['Version'],
						$plugin_data['Version'],
						$plugin_data['PluginURI']);
						*/
				}
			}		
			$this->upgPlugins = $this->dati;
		}
		
		if(get_option('wun_themes','0') != '1'){
			unset($this->dati);
			$themes = get_themes();
			$current = get_site_transient('update_themes');
			
			foreach ( $themes as $theme ) {
				$theme = (object)$theme;
				if (isset($current->response[$theme->Template]['new_version'])) {
					$this->add($theme->Name,
						'theme',
						$theme->Version,
						$current->response[$theme->Template ]['new_version'],
						$current->response[$theme->Template ]['url']);
						$this->datas[$theme->Name] = $current->response[$theme->Template ]['new_version'];
				} else {
				/*	$this->add($theme->Name,
						'theme',
						$theme->Version,
						$theme->Version,
						'');	*/
				}
			}
			$this->upgTheme = $this->dati;
		}
	}
	
	private function add($nome, $tipo, $ver, $upg, $lnk){
		$pass = array();
		if(isset($nome)) $pass['nome'] = $nome;
		if(isset($ver)) $pass['ver'] = $ver;
		if(isset($upg)) $pass['upg'] = $upg;
		if(isset($lnk)) $pass['lnk'] = $lnk;
		
		$this->dati[] = $pass;
		unset($pass);
	}
	
	Public function sendMail(){
		$this->creaMail();
		if ( (get_option('wun_data') != md5(http_build_query($this->datas))) && (count($this->datas) != 0)){
			$lamail = get_bloginfo('admin_email');
			if(get_option('wun_email')) { $lamail.= ','. get_option('wun_email'); }
			update_option('wun_data',md5(http_build_query($this->datas)));
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			wp_mail($lamail, __('Update notifications: new updates to the site ','upn').get_bloginfo('url'), $this->layMail(),$headers);
		}
	}
	
	private function layMail(){
		$allData = '<div id="email" style="font:12px Helvetica,Arial,Verdana;color:#525252;">';
		$allData.= '<p>'.__('There are new updates to the site ','upn'). get_bloginfo('name').' (url:'. get_bloginfo('url').')<br />
					'.__('To update click on the following','upn').' <a href="'. get_bloginfo('url').'/wp-admin/update-core.php" title="'.get_bloginfo('name').'">link</a><br /><br />
					'.__('Below is the list.','upn').'</p>';
		if(count($this->upgCore)>0){
			$allData.='<h2 style="font-size:15px;color:#658be2;">'.__('Upgrading Wordpress','upn').'</h2><ul style="list-style-type:none;padding:0px;color:#525252;">';
			foreach ($this->upgCore as $value) {
				$allData.= $this->addList($value);
			}
			$allData.='</ul>';
		}
		if(count($this->upgPlugins)>0){
			$allData.='<h2 style="font-size:15px;color:#658be2;">'.__('Upgrading Plugins','upn').'</h2><ul style="list-style-type:none;padding:0px;color:#525252;">';
			foreach ($this->upgPlugins as $value) {
				$allData.= $this->addList($value);
			}
			$allData.='</ul>';
		}
		if(count($this->upgTheme)>0){
			$allData.='<h2 style="font-size:15px;color:#658be2;">'.__('Upgrading Themes','upn').'</h2><ul style="list-style-type:none;padding:0px;color:#525252;">';
			foreach ($this->upgTheme as $value) {
				$allData.= $this->addList($value);
			}
			$allData.='</ul>';
		}
		$allData.= '<p>------------------------------------------------------------------------------------------------------------------------------------------------------<br />'
		.__('This email was created with the plugin Update Notifications.','upn').
		'<br />------------------------------------------------------------------------------------------------------------------------------------------------------</p>';
		$allData.= '</div>';
		return($allData);
	}
	
	private function addList($value){
		$temp = '<li>'.$value['nome'].' ver. '.$value['upg'].'</li>';
		return($temp);
	}
}