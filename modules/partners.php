<?php

krnLoadLib('define');
krnLoadLib('settings');

class partners extends krn_abstract {

	function __construct() {
		parent::__construct();
		
		$this->page = $this->db->getRow('SELECT Title, Header, Code, Content, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code = ?s', 'partners');
		
		$this->pageTitle = $this->page['Title'];
		$this->breadCrumbs = GetBreadCrumbs(array(
			'Главная' => '/'),
			$this->pageTitle);
	}	

	function GetResult() {		
		$result = strtr(krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_static'), array(
			'<%META_KEYWORDS%>'		=> $this->new['SeoKeywords'] ?: $this->page['SeoKeywords'],
    		'<%META_DESCRIPTION%>'	=> $this->new['SeoDescription'] ?: $this->page['SeoDescription'],
    		'<%PAGE_TITLE%>'		=> $this->new['SeoTitle'] ?: $this->pageTitle,
    		'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
    		'<%TITLE%>'				=> $this->page['Header'] ?: $this->page['Title'],
    		'<%TEXT%>'				=> $this->page['Content'] ? '<div class="text content wow fadeInUpSmall fast animated delay-02s">' . $this->page['Content'] . '</div>' : '',
			'<%CONTENT%>'			=> $this->GetPartnersList(),
		));
		
		$Blocks = krnLoadModuleByName('blocks', $this->page['Id']);
		$this->blocks = $Blocks->GetPageBlocks();
		foreach ($this->blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
		return $result;
	}
	
	/** Список партнеров */
	function GetPartnersList() {
		$element = LoadTemplate('partners_el');
		$content = '';

		$partners = $this->db->getAll('SELECT Title, Link, Image, Text FROM partners ORDER BY IF(`Order`, -1000/`Order`, 0)');
		foreach ($partners as $partner) {
			$partner['Alt'] = htmlspecialchars($partner['Title'], ENT_QUOTES);
			$partner['Text'] = nl2br($partner['Text']);
			if ($partner['Link']) $partner['Link'] = '<a href="' . $partner['Link'] . '" target="_blank" class="external" rel="nofollow">Перейти на сайт</a>';
			$content .= SetAtribs($element, $partner);
		}

		$result = strtr(LoadTemplate('partners'), array(
			'<%CONTENT%>'		=> $content,
		));
		return $result;
	}
}

?>