<?php
    
    class Site {

    	protected $db;
		protected $settings;

		protected $modals;
		protected $scripts;

		public function __construct() {
			global $Params;
			global $Settings;
			$this->db = $Params['Db']['Link'];
			$this->settings = $Settings;
		}

    	public function GetCurrentPage() {
			$page = false;
			if (preg_match('/\/([a-zA-Z0-9_\-\-]+)\/?$/', $_SERVER['REQUEST_URI'], $match)) {
				$page = $match[1];
			} elseif (preg_match('/\/$/', $_SERVER['REQUEST_URI'])) {
				$page = '/';
			}
			return $page;
		}

		public function GetPageFromLink($link) {
			$page = false;
			if (preg_match('/\/([a-zA-Z0-9_\-]+)\/?$/', $link, $match)) {
				$page = $match[1];
			} elseif (preg_match('/\/$/', $link)) {
				$page = '/';
			}
			return $page;
		}

		public function SetLinks($html) {
			$result = preg_replace('~<a +href="(?!http[s]?://)([^\>]+)~i', '<a href="/$1', $html);
			return strtr($result, array(
				'<a href="//'		=> '<a href="/',
				'<a href="/#'		=> '<a href="#',
				'<a href="/tel:'	=> '<a href="tel:',
				'<a href="/mailto'	=> '<a href="mailto' 
			));
		}

		public function AddModal($html) {
			$this->modals .= $html;
		}

		public function GetModals() {
			return $this->modals;
		}

		public function AddScript($link) {
			$this->scripts .= '<script src="' . $link . '"></script>';
		}

		public function GetScripts() {
			return $this->scripts;
		}

		public function GetPage() {
			krnLoadLib('define');
			krnLoadLib('menu');
			krnLoadLib('modal');
			global $krnModule;

			$Blocks = krnLoadModuleByName('blocks');
			$Main = krnLoadModuleByName('main');

			// menus
			$menuMain = new Menu([
				'menuDb'			=> 'menu_items',
				'subMenuDb'			=> 'menu_sub_items',
				'template'			=> 'mn_main',
				'templateEl'		=> 'mn_main_el',
				'templateSub'		=> 'mn_sub',
				'templateSubEl'		=> 'mn_sub_el',
			]);

			$menuBottom = new Menu([
				'menuDb'			=> 'menu_bottom_items',
				'template'			=> 'mn_bottom',
				'templateEl'		=> 'mn_bottom_el',
			]);

			// contacts
			$contact = $this->db->getRow('SELECT * FROM contacts ORDER BY IF(`Order`, -100/`Order`, 0) ASC LIMIT 0, 1');			
			$tel = $contact['Tel1'];
			$tellink = preg_replace('/[^\d\+]/', '', $tel);
			$tel2 = $contact['Tel2'];
			$tellink2 = preg_replace('/[^\d\+]/', '', $tel2);
			$email = $contact['Email1'];
			$email2 = $contact['Email2'];
			$address = $contact['Address'];

			// settings
			$siteTitle = $this->settings->GetSetting('SiteTitle', $Config['Site']['Title']);

			// user agreement and policy
			$files = $this->db->getAll('SELECT Title, Code, File FROM files');
			foreach ($files as $file) {
				if ($file['Code'] == 'agreement' || $file['Code'] == 'policy') $law[$file['Code']] = '<a href="' . $file['File'] . '">' . $file['Title'] . '</a>';
			}

			$result = strtr($krnModule->GetResult(), array(
				'<%VERSION%>'				=> $this->settings->GetSetting('AssetsVersion') ? '?v2.' . $this->settings->GetSetting('AssetsVersion') : '',
		    	'<%META_KEYWORDS%>'			=> $Config['Site']['Keywords'],
		    	'<%META_DESCRIPTION%>'		=> $Config['Site']['Description'],
		    	'<%META_IMAGE%>'			=> '',
		    	'<%PAGE_TITLE%>'			=> $siteTitle,
		    	'<%SITE_TITLE%>'			=> $siteTitle,
		    	'<%SITE_TITLE_ALT%>'		=> htmlspecialchars($siteTitle, ENT_QUOTES),
		    	'<%SITE_URL%>'				=> $this->settings->GetSetting('SiteProtocol') . $this->settings->GetSetting('SiteDomain'),
		    	'<%TEL%>'					=> $tel,
		    	'<%TELLINK%>'				=> $tellink,
		    	'&lt;%TEL%&gt;'				=> $tel,
		    	'&lt;%TELLINK%&gt;'			=> $tellink,
		    	'<%TEL2%>'					=> $tel2,
		    	'<%TELLINK2%>'				=> $tellink2,
		    	'&lt;%TEL2%&gt;'			=> $tel2,
		    	'&lt;%TELLINK2%&gt;'		=> $tellink2,
		    	'<%EMAIL%>'					=> $email,
		    	'&lt;%EMAIL%&gt;'			=> $email,
		    	'<%EMAIL2%>'				=> $email2,
		    	'&lt;%EMAIL2%&gt;'			=> $email2,
		    	'<%ADDRESS%>'				=> str_replace('Республика Татарстан, <br>', '', $address),
		    	'&lt;%ADDRESS%&gt;'			=> str_replace('Республика Татарстан, <br>', '', $address),
		    	'<%FULLADDRESS%>'			=> $address,
		    	'&lt;%FULLADDRESS%&gt;'		=> $address,
		    	'<%COORDS%>'				=> $contact['MapCoords'],
		    	'<%META_VERIFICATION%>'		=> $this->settings->GetSetting('MetaVerification'),
		    	'<%YANDEX_METRIKA%>'		=> $this->settings->GetSetting('YandexMetrika'),
		    	'<%MN_MAIN%>'				=> $menuMain->GetMenu(),
		    	'<%LAYOUTCLASS%>'			=> '',
		    	'<%BREAD_CRUMBS%>'			=> '',
		    	'<%COPYRIGHT%>'				=> str_replace('<%YEAR%>', date('Y'), $this->settings->GetSetting('Copyright')),
		    	'<%DIRECTION%>'				=> $this->settings->GetSetting('Direction'),
		    	'<%MN_BOTTOM%>'				=> $menuBottom->GetMenu(),
		    	'<%MODALS%>'				=> $this->GetModals(),
		    	'<%CONSULTANT%>'			=> $this->settings->GetSetting('ConsultantCode'),
		    	'<%ANALYTICS%>'				=> $this->settings->GetSetting('AnalyticsCode'),
		    	'<%SCRIPTS%>'				=> $this->getScripts(),
		    	'<%BLOCK1%>'				=> '',
		    	'<%BLOCK2%>'				=> '',
		    	'<%BLOCK3%>'				=> '',
		    	'<%BLOCK4%>'				=> '',
		    	'<%BLOCK5%>'				=> '',
		    	'<%BLOCK6%>'				=> '',
		    	'<%BLOCK7%>'				=> '',
		    	'<%BLOCK8%>'				=> '',
		    	'<%BLOCK9%>'				=> '',
		    	'<%BLOCK10%>'				=> '',
		    	'<%BLOCK11%>'				=> $Blocks->BlockContacts(),
		    	'<%YEAR%>'  				=> date('Y'),
			));

			return $this->SetLinks($result);
		}	

	}
	
?>