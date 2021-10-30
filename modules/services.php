<?php

krnLoadLib('define');
krnLoadLib('settings');

class services extends krn_abstract{	

	function __construct(){
		global $_LEVEL;
		parent::__construct();

		$this->serviceCode = $_LEVEL[2];

		$this->folder = $this->db->getRow('SELECT Id, Title, Header, Code, Content, SeoTitle, SeoKeywords, SeoDescription, TemplateCode FROM static_pages WHERE Code = ?s', 'services');

		// услуга
		if ($this->serviceCode) {
			$query = 'SELECT s.Id, s.Title, s.Image, s.Announce, '
					.'p.Id AS PageId, p.SeoTitle, p.SeoKeywords, p.SeoDescription, p.TemplateCode, p.Content '
					.'FROM services s '
					.'LEFT JOIN static_pages p ON s.Code = p.Code '
					.'WHERE s.Code = ?s';
			$this->service = $this->db->getRow($query, $this->serviceCode);

			if (!$this->service) {
				$this->notFound = true;
			}

			$this->pageTitle = $this->service['SeoTitle'] ?: $this->service['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				'Главная' => '/',
				$this->folder['Title'] => $this->folder['Code'] . '/'),
				$this->service['Title']);

		} else {
			$this->page = $this->folder;
			$this->pageTitle = $this->page['Header'] ?: $this->page['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				'Главная' => '/'),
				$this->pageTitle);
		}
	}	

	function GetResult() {
		$this->blocks = krnLoadModuleByName('blocks');

		if ($this->notFound) {
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
			$this->pageTitle = 'Страница не найдена';
			$result = krnLoadPageByTemplate('base_static');
			$result = strtr($result, array(
				'<%META_KEYWORDS%>'		=> $this->page['SeoKeywords'],
				'<%META_DESCRIPTION%>'	=> $this->page['SeoDescription'],
				'<%PAGE_TITLE%>'		=> $this->pageTitle,
				'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
				'<%TITLE%>'				=> $this->page['Header'] ?: $this->page['Title'],
				'<%CONTENT%>'			=> LoadTemplate('404'),
			));
			return $result;
		}

		if ($this->serviceCode) {
			$this->content = $this->GetService();
			$result = krnLoadPageByTemplate('base_service');
			$result = strtr($result, array(
				'<%META_KEYWORDS%>'		=> $this->service['SeoKeywords'] ?: $Config['Site']['Keywords'],
				'<%META_DESCRIPTION%>'	=> $this->service['SeoDescription'] ?: $Config['Site']['Description'],
		    	'<%PAGE_TITLE%>'		=> $this->service['SeoTitle'] ?: $this->pageTitle,
		    	'<%BREAD_CRUMBS%>'		=> '',
		    	'<%BREAD_CRUMBS2%>'		=> $this->breadCrumbs,
				'<%CONTENT%>'			=> $this->content,
				'<%TITLE%>'				=> $this->service['Title'] ?: $this->service['Title'],
				'<%ALT%>'				=> htmlspecialchars($this->service['Title'] ?: $this->service['Title'], ENT_QUOTES),
				'<%ANNOUNCE%>'			=> $this->service['Announce'],
				'<%IMAGE%>'				=> $this->service['Image'],
			));			

		} else {
			$this->content = $this->GetServices();
			$result = krnLoadPageByTemplate('base_services');
			$result = strtr($result, array(
				'<%META_KEYWORDS%>'		=> $this->page['SeoKeywords'] ?: $Config['Site']['Keywords'],
				'<%META_DESCRIPTION%>'	=> $this->page['SeoDescription'] ?: $Config['Site']['Description'],
		    	'<%PAGE_TITLE%>'		=> $this->page['SeoTitle'] ?: $this->pageTitle,
		    	'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
				'<%CONTENT%>'			=> $this->content,
				'<%TITLE%>'				=> $this->page['Header'] ?: $this->page['Title'],
			));
		}

		$Blocks = krnLoadModuleByName('blocks', $this->page['Id'] ?: $this->service['PageId']);
		$blocks = $Blocks->GetPageBlocks();

		foreach ($blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
				
		return $result;
	}

	public function GetServices() {
		$element = LoadTemplate('bl_services_chess_el');
		$elementVideo = LoadTemplate('bl_services_chess_video_el');
		$content = '';
		$services = $this->db->getAll('SELECT Id, Title, Code, Image FROM services ORDER BY IF(`Order`, -100/`Order`, 0) ASC');
		$counter = 0;
		foreach ($services as $service) {
			if ($service['Id'] != VIDEO_SERVICE_ID) {
				$content .= strtr($element, [
					'<%TITLE%>'		=> $service['Title'],
					'<%ALT%>'		=> htmlspecialchars($service['Title'], ENT_QUOTES),
					'<%LINK%>'		=> '/services/' . $service['Code'] . '/',
					'<%IMAGE%>'		=> $service['Image'],
					'<%I%>'			=> $counter
				]);

			} else {
				krnLoadLib('modal');
				krnLoadLib('youtube');

				global $Site;
				$Site->addScript('https://www.youtube.com/iframe_api');
				$youtube = new Youtube();
				$modalVideo = new Modal('video', ['VideoId' => '0']);
				$Site->addModal($modalVideo->getModal());

				$content .= strtr($elementVideo, [
					'<%TITLE%>'		=> $service['Title'],
					'<%ALT%>'		=> htmlspecialchars($service['Title'], ENT_QUOTES),
					'<%CODE%>'		=> $youtube->GetCodeFromSource($this->settings->GetSetting('YoutubeCode')),
					'<%IMAGE%>'		=> $service['Image'],
					'<%I%>'			=> $counter
				]);
			}
		}

		$result = LoadTemplate('bl_services_chess');
		$result = $content ? strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}
	
	/** Услуга */
	function GetService() {
		$blocks = $this->blocks->GetPageBlocks($this->service['PageId'], $this->service);

		if ($this->service['TemplateCode']) {
			$result = LoadTemplate($this->service['TemplateCode']);
			foreach ($blocks as $i => $block) {
				$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
			}
		} else {
			$result = implode(PHP_EOL, $blocks);
		}

		$result = strtr($result, array(
			'<%CONTENT%>'		=> $this->service['Content'],
		));

		return $result;
	}
}

?>