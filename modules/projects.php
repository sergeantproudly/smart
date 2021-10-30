<?php

krnLoadLib('define');
krnLoadLib('settings');

class projects extends krn_abstract {

	function __construct() {
		global $_LEVEL;
		parent::__construct();
		
		$this->page = $this->db->getRow('SELECT Id, Title, Header, Code, Content, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code = ?s', 'projects');
		
		if ($this->workCode = $_LEVEL[2]) {
			$this->work = $this->db->getRow('SELECT Id, Title, Code, Image1340_1018 AS Image, Announce, Description AS Text, SeoTitle, SeoKeywords, SeoDescription FROM works WHERE Code = ?s', $this->workCode);
			$this->pageTitle = $this->work['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				'Главная' => '/',
				$this->page['Title'] => $this->page['Code'] . '/'),
				$this->pageTitle);

		} else {
			$this->pageTitle = $this->page['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				'Главная' => '/'),
				$this->pageTitle);
		}	
	}	

	function GetResult() {
		$Blocks = krnLoadModuleByName('blocks');

		if ($this->workCode) {
			$result = strtr(krnLoadPageByTemplate('base_project'), array(
				'<%META_KEYWORDS%>'		=> $this->work['SeoKeywords'],
	    		'<%META_DESCRIPTION%>'	=> $this->work['SeoDescription'],
	    		'<%PAGE_TITLE%>'		=> $this->work['SeoTitle'] ?: $this->pageTitle,
	    		'<%BREAD_CRUMBS%>'		=> '',
	    		'<%BREAD_CRUMBS2%>'		=> $this->breadCrumbs,
	    		'<%TITLE%>'				=> $this->work['Title'],
				'<%CONTENT%>'			=> $this->content,
				'<%ANNOUNCE%>'			=> $this->work['Announce'],
				'<%IMAGE%>'				=> $this->work['Image'],
				'<%ALT%>'				=> htmlspecialchars($this->work['Title'], ENT_QUOTES),
				'<%TEXT%>'				=> $this->work['Text']
			));
			
		} else {
			$result = strtr(krnLoadPageByTemplate('base_static'), array(
				'<%META_KEYWORDS%>'		=> $this->page['SeoKeywords'],
	    		'<%META_DESCRIPTION%>'	=> $this->page['SeoDescription'],
	    		'<%PAGE_TITLE%>'		=> $this->pageTitle,
	    		'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
	    		'<%TITLE%>'				=> $this->page['Header'] ?: $this->page['Title'],
	    		'<%TEXT%>'				=> $this->page['Content'] ? '<div class="text content wow fadeInUpSmall fast animated delay-02s">' . $this->page['Content'] . '</div>' : '',
				'<%CONTENT%>'			=> $this->GetWorksList(),
			));
		}
		
		$this->blocks = $Blocks->GetPageBlocks($this->page['Id']);
		foreach ($this->blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
		return $result;
	}
	
	/** Список выполненных проектов */
	function GetWorksList() {
		$element = LoadTemplate('projects_el');
		$content = '';

		$works = $this->db->getAll('SELECT Title, Code, Image1346_862 AS Image, Announce AS Text, ClientLogo229_76 AS ClientLogo FROM works ORDER BY IF(`Order`, -1000/`Order`, 0)');
		foreach ($works as $work) {
			$work['Alt'] = htmlspecialchars($work['Title'], ENT_QUOTES);
			$work['ClientAlt'] = htmlspecialchars($work['ClientName'], ENT_QUOTES);
			$work['Link'] = '/' . $this->page['Code'] . '/' . $work['Code'] . '/';
			$content .= SetAtribs($element, $work);
		}

		$result = strtr(LoadTemplate('projects'), array(
			'<%CONTENT%>'		=> $content,
		));
		return $result;
	}
}

?>