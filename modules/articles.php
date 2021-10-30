<?php

krnLoadLib('define');
krnLoadLib('settings');

class articles extends krn_abstract {

	private $newPhotos = [];

	function __construct() {
		global $_LEVEL;
		parent::__construct();
		
		$this->page = $this->db->getRow('SELECT Title, Header, Code, Content, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code = ?s', 'articles');
		
		if ($this->newCode = $_LEVEL[2]) {
			$this->new = $this->db->getRow('SELECT Id, Title, Code, Announce, Description AS Text, Date, Image AS ImageFull, Image1342_886 AS Image, SeoTitle, SeoKeywords, SeoDescription FROM news WHERE Code = ?s', $this->newCode);
			$this->pageTitle = $this->new['Title'];
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

		if ($this->newCode) {
			$result = strtr(krnLoadPageByTemplate('base_new'), array(
				'<%META_KEYWORDS%>'		=> $this->new['SeoKeywords'],
	    		'<%META_DESCRIPTION%>'	=> $this->new['SeoDescription'],
	    		'<%PAGE_TITLE%>'		=> $this->new['SeoTitle'] ?: $this->new['Title'],
	    		'<%BREAD_CRUMBS%>'		=> '',
	    		'<%BREAD_CRUMBS2%>'		=> $this->breadCrumbs,
	    		'<%DATE%>'				=> ModifiedDate($this->new['Date']),
	    		'<%TITLE%>'				=> $this->new['Title'],
	    		'<%ALT%>'				=> htmlspecialchars($this->new['Title'], ENT_QUOTES),
	    		'<%IMAGE%>'				=> $this->new['Image'],
	    		'<%IMAGEFULL%>'			=> $this->new['ImageFull'],
	    		'<%ANNOUNCE%>'			=> $this->new['Announce'],
	    		'<%TEXT%>'				=> $this->GetNewText(),
				'<%SHAREURL%>'			=> urlencode($this->settings->GetSetting('SiteProtocol') . $this->settings->GetSetting('SiteDomain') . '/articles/' . $this->new['Code']),
				'<%SHARETITLE%>'		=> htmlspecialchars($this->new['Title'], ENT_QUOTES),
				'<%SHAREIMAGE%>'		=> $this->new['Image'],
				'<%SHARETEXT%>'			=> htmlspecialchars(TrimText(strip_tags(str_replace('<br />', ' ', $this->new['Text'])), 300), ENT_QUOTES),
				'<%BLOCK_OTHER%>'		=> $this->BlockOtherNews()
			));

		} else {
			if ($_GET['page']) $this->pageIndex = $_GET['page'];

			$result = strtr(krnLoadPageByTemplate('base_static'), array(
				'<%META_KEYWORDS%>'		=> $this->new['SeoKeywords'] ?: $this->page['SeoKeywords'],
	    		'<%META_DESCRIPTION%>'	=> $this->new['SeoDescription'] ?: $this->page['SeoDescription'],
	    		'<%PAGE_TITLE%>'		=> $this->new['SeoTitle'] ?: $this->pageTitle,
	    		'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
	    		'<%TITLE%>'				=> $this->page['Header'] ?: $this->page['Title'],
	    		'<%TEXT%>'				=> $this->page['Content'] ? '<div class="text content content wow fadeInUpSmall fast animated delay-02s">' . $this->page['Content'] . '</div>' : '',
				'<%CONTENT%>'			=> $this->GetNewsList(),
			));
		}
		
		$this->blocks = $Blocks->GetPageBlocks($this->page['Id']);
		foreach ($this->blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
		return $result;
	}
	
	/** Список новостей */
	function GetNewsList() {
		$element = LoadTemplate('news_el');
		$content = '';

		$recsOnPage = $this->settings->GetSetting('NewsRecsOnPage', 18);
		$query = 'SELECT DISTINCT COUNT(Id) FROM news';
		$this->total = $this->db->getOne($query);

		$news = $this->db->getAll('SELECT Title, Date, Image896_548 AS Image, Announce AS Text, Code FROM news ORDER BY Date DESC LIMIT ?i, ?i', ($this->pageIndex-1)*$recsOnPage, $this->pageIndex == 1 ? $recsOnPage+1 : $recsOnPage);
		foreach ($news as $new) {
			$new['Alt'] = htmlspecialchars($new['Title'], ENT_QUOTES);
			$new['Link'] = '/' . $this->page['Code'] . '/' . $new['Code'] . '/';
			$new['Date'] = ModifiedDate($new['Date']);
			$new['Text'] = nl2br($new['Text']);
			$content .= SetAtribs($element, $new);
		}
		//$pagination = GetPagination($this->total, $recsOnPage, 6, $this->pageIndex);

		$result = strtr(LoadTemplate('news'), array(
			'<%TITLE%>'			=> $this->page['Header'] ?: $this->page['Title'],
			'<%CONTENT%>'		=> $content,
		));
		return $result;
	}

	public function GetNewPhotos() {
		$photos = $this->db->getAll('SELECT Title, Image AS ImageFull, Image1344_944 AS Image FROM news_photos WHERE NewId = ?i ORDER BY IF(`Order`, -100/`Order`, 0)', $this->new['Id']);
		foreach ($photos as &$photo) {
			$photo['Alt'] = htmlspecialchars($photo['Title'], ENT_QUOTES);
		}
		return $photos;
	}

	public function GetNewText() {
		$this->newPhotos = $this->GetNewPhotos();

		$element = LoadTemplate('new_photos_el');
		$result = LoadTemplate('new_photos');

		$text = $this->new['Text'];

		preg_match_all('/%([\d\s,]+)%/', $this->new['Text'], $matches);
		foreach (array_shift($matches) as $match) {
			$indexes = explode(',', str_replace('%', '', $match));
			$content = '';
			$class = '';

			if (count($indexes) == 1) $class = 'single';
			elseif (count($indexes) == 3) $class = 'triple';

			foreach ($indexes as $index) {
				$content .= SetAtribs($element, $this->newPhotos[trim($index) - 1]);
			}

			$text = str_replace($match, strtr($result, [
				'<%CLASS%>'		=> $class ? ' ' . $class : '',
				'<%CONTENT%>'	=> $content
			]), $text);
		}

		return $text;
	}
	
	/** Блок - Другие новости */
	function BlockOtherNews() {
		$element = LoadTemplate('news_el');
		$content = '';
		
		$recsInBlock = $this->settings->GetSetting('NewsRecsInBlock', 3);
		
		$query = 'SELECT Title, Date, Image896_548 AS Image, Announce AS Text, Code FROM news WHERE Id <> ?i ORDER BY Date DESC LIMIT ?i, ?i';
		$news = $this->db->getAll($query, $this->new['Id'], 0, $recsInBlock);
		foreach ($news as $new) {
			$new['Alt'] = htmlspecialchars($new['Title'], ENT_QUOTES);
			$new['Link'] = '/' . $this->page['Code'] . '/' . $new['Code'] . '/';
			$new['Date'] = ModifiedDate($new['Date']);
			$new['Text'] = nl2br($new['Text']);
			$content .= SetAtribs($element, $new);
		}
		
		$result = $content ? strtr(LoadTemplate('news_other'), [
			'<%TITLE%>'		=> 'Другие новости',
			'<%CONTENT%>'	=> $content,
		]) : '';
		return $result;
	}
}

?>