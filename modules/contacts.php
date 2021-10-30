<?php

krnLoadLib('define');
krnLoadLib('settings');

class contacts extends krn_abstract{	

	function __construct() {
		parent::__construct();

		$this->page = $this->db->getRow('SELECT Title, Header, Code, Content, SeoTitle, SeoKeywords, SeoDescription, TemplateCode FROM static_pages WHERE Code = ?s', 'contacts');

		$this->pageTitle = $this->page['Header'] ?: $this->page['Title'];
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

		$contact = $this->db->getRow('SELECT * FROM contacts ORDER BY IF(`Order`, -100/`Order`, 0) ASC LIMIT 0, 1');			
		$tel = $contact['Tel1'];
		$tellink = preg_replace('/[^\d\+]/', '', $tel);
		$tel2 = $contact['Tel2'];
		$tellink2 = preg_replace('/[^\d\+]/', '', $tel2);
		$email = $contact['Email1'];
		$email2 = $contact['Email2'];
		$address = $contact['Address'];
		
		$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_static');
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->page['SeoKeywords'],
			'<%META_DESCRIPTION%>'	=> $this->page['SeoDescription'],
			'<%LAYOUTCLASS%>'		=> '',
	    	'<%PAGE_TITLE%>'		=> $this->page['SeoTitle'] ?: $this->pageTitle,
	    	'<%BREAD_CRUMBS%>'		=> '',
	    	'<%BREAD_CRUMBS2%>'		=> $this->breadCrumbs,
	    	'<%TITLE%>'				=> $this->pageTitle,
	    	'<%TEXT%>'				=> $this->page['Content'],
	    	'<%ADDRESS%>'			=> $address,
			'<%TEL%>'				=> $tel,
			'<%TELLINK%>'			=> $tellink,
			'<%TEL2%>'				=> $tel2,
			'<%TELLINK2%>'			=> $tellink2,
			'<%EMAIL%>'				=> $email,
			'<%EMAIL2%>'			=> $email2,
			'<%BLOCK_FEEDBACK%>'	=> $this->blocks->BlockFeedback()
		));
				
		return $result;
	}
}

?>