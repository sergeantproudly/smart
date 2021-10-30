<?php

krnLoadLib('settings');

class main extends krn_abstract{	

	public function __construct(){
		parent::__construct();
		$this->page = $this->db->getRow('SELECT Id, Title, Header, Content, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code="main"');
		
		global $Config;
		$this->pageTitle = $this->page['Title'] ?: $this->settings->GetSetting('SiteTitle', $Config['Site']['Title'] ?: 'Главная');
	}	

	public function GetResult(){
		global $Config;
		$Blocks = krnLoadModuleByName('blocks', $this->page['Id']);

		$blocks = $Blocks->GetPageBlocks();
		
		$result = krnLoadPageByTemplate('base_main');
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->page['Keywords'] ?: $Config['Site']['Keywords'],
			'<%META_DESCRIPTION%>'	=> $this->page['Description'] ?: $Config['Site']['Description'],
			'<%PAGE_TITLE%>'		=> $this->pageTitle,
			'<%TITLE%>'				=> $this->page['Header'],
			'<%STATS%>'				=> $this->GetStats(),
		));

		foreach ($blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}

		return $result;
	}

	function GetStats() {
		$element = LoadTemplate('bl_stats_el');
		$elementLinked = LoadTemplate('bl_stats_linked_el');
		$content = '';

		$stats = $this->db->getAll('SELECT * FROM statistics WHERE OnMain = 1 ORDER BY IF (`Order`, -100/`Order`, 0) ASC');
		$counter = 0;
		foreach ($stats as $stat) {
			$counter += 2;
			$stat['I'] = $counter;
			$content .= SetAtribs($stat['Link'] ? $elementLinked : $element, $stat);
		}

		$result = SetContent(LoadTemplate('bl_stats'), $content);
		return $result;
	}

}
?>