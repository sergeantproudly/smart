<?php

krnLoadLib('settings');

class search extends krn_abstract {
	
	public function __construct() {
		parent::__construct();
		$this->page = $this->db->getRow('SELECT Id, Title, Header, Content, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code="search"');
		
		if ($_POST['keyword']) {
			$_SESSION['Search']['Target'] = $_POST['keyword'];
			__Redirect('/search');
		} elseif ($_GET['keyword']) {
			$_SESSION['Search']['Target'] = base64_decode($_GET['keyword']);
			__Redirect('/search');
		} elseif($_SESSION['Search']['Target']) {
			$this->search_target = $_SESSION['Search']['Target'];
			$this->pageTitle = 'Результаты поиска: ' . $this->search_target;
		}else{
			$this->pageTitle = $this->page['Title'];
		}			
	}
	
	function GetResult(){
		$Blocks = krnLoadModuleByName('blocks');
		
		$result = strtr(krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_page'), array(
			'<%META_KEYWORDS%>'		=> $this->new['SeoKeywords'] ?: $this->page['SeoKeywords'],
    		'<%META_DESCRIPTION%>'	=> $this->new['SeoDescription'] ?: $this->page['SeoDescription'],
    		'<%PAGE_TITLE%>'		=> $this->new['SeoTitle'] ?: $this->pageTitle,
    		'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
    		'<%TITLE%>'				=> $this->pageTitle ?: $this->page['Header'] ?: $this->page['Title'],
			'<%CONTENT%>'			=> $this->search_target ? $this->GetSearchResultsList() : $this->GetSearchStart(),
		));
		
		$this->blocks = $Blocks->GetPageBlocks($this->page['Id']);
		foreach ($this->blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
		return $result;
	}
	
	function GetSearchStart() {
		$result = LoadTemplate('search');
		$result = strtr($result,array(
			'<%CONTENT%>'	=> 'Для старта поиска введите <span class="dashed" onclick="gei(\'search_target_fld\').focus();">поисковое слово</span>',
			'<%TARGET%>'	=> ''
		));
		return $result;
	}
	
	function GetSearchResultsList() {
		$result = LoadTemplate('search');
		$element = LoadTemplate('search_el');
		$content = '';
		
		$prep_target = '%' . $this->search_target . '%';
		
		/** Поиск по статике */
		$matches = $this->db->query('SELECT Code, Title, Content FROM static_pages WHERE Title LIKE ?s OR Content LIKE ?s AND Code<>"search" AND Code<>"auth" AND Code<>"map"', $prep_target, $prep_target);
		foreach ($matches as $match){
			$type = 'Информационная страница';
			if ($match['Code'] == 'services') $type='Список услуг';
			
			$content .= strtr($element, array(
				'<%TYPE%>'			=> $type,
				'<%HREF%>'			=> $match['Code'],
				'<%TITLE%>'			=> $match['Title'],
				'<%DESCRIPTION%>'	=> $match['Content']?'<div class="desc">'.trimText(strip_tags($match['Content']),700).'</div>':''
			));
		}
		
		/** Поиск по новостям */
		$matches=$this->db->query('SELECT Id, Code, Title, Description AS Text FROM news WHERE Title LIKE ?s OR Description LIKE ?s',$prep_target,$prep_target);
		foreach($matches as $match){
			$content.=strtr($element,array(
				'<%TYPE%>'			=> 'Новость',
				'<%HREF%>'			=> 'news/'.$match['Code'],
				'<%TITLE%>'			=> $match['Title'],
				'<%DESCRIPTION%>'	=> $match['Text']?'<div class="desc">'.trimText(strip_tags($match['Text']),700).'</div>':''
			));
		}
		
		$result=strtr($result,array(
			'<%TITLE%>'		=> 'Результаты поиска',
			'<%CONTENT%>'	=> $content?$content:'<div class="marg-st">По вашему запросу ничего не найдено</div>',
			'<%TARGET%>'	=> $this->search_target
		));
		return $result;
	}
	
}

?>