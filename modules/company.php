<?php

krnLoadLib('define');
krnLoadLib('settings');

class company extends krn_abstract{	

	function __construct(){
		global $_LEVEL;
		parent::__construct();

		$this->page = $this->db->getRow('SELECT Id, Title, Header, Code, Content, SeoTitle, SeoKeywords, SeoDescription, TemplateCode FROM static_pages WHERE Code = ?s', 'company');

		// выбран подраздел
		if ($this->folderCode = $_LEVEL[2]) {
			$this->folder = $this->db->getRow('SELECT Id, Title, Header, Code, Content, SeoTitle, SeoKeywords, SeoDescription, TemplateCode FROM static_pages WHERE Code = ?s', $this->folderCode);

			if (!$this->folder) {
				$this->notFound = true;
			}

			// выбран подраздел подраздела
			if ($this->subFolderCode = $_LEVEL[3]) {
				if ($this->folderCode == 'media') {
					$this->subFolder = $this->db->getRow('SELECT Title, Code, SeoTitle, SeoKeywords, SeoDescription FROM media_galleries WHERE Code = ?s', $this->subFolderCode);
					$this->subFolder['Header'] = $this->subFolder['Title'];
					$this->subFolder['TemplateCode'] = $this->folder['TemplateCode'];
					$this->pageTitle = $this->subFolder['SeoTitle'] ?: $this->subFolder['Title'];
					$this->breadCrumbs = GetBreadCrumbs(array(
						'Главная' => '/',
						'О компании' => '/company/',
						$this->folder['Title'] => '/company/' . $this->folderCode . '/'),
						$this->pageTitle
					);
				}

			} else {
				$this->pageTitle = $this->folder['SeoTitle'] ?: $this->folder['Title'];
				$this->breadCrumbs = GetBreadCrumbs(array(
					'Главная' => '/',
					'О компании' => '/company/'),
					$this->pageTitle
				);
			}

		} else {
			$this->pageTitle = $this->page['Header'] ?: $this->page['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				'Главная' => '/'),
				'О компании');
		}
	}	

	function GetResult() {
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

		global $_LEVEL;
		$page = $this->subFolder ?: $this->folder ?: $this->page;
		
		$result = krnLoadPageByTemplate($page['TemplateCode'] ?: 'base_static');
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $page['SeoKeywords'],
			'<%META_DESCRIPTION%>'	=> $page['SeoDescription'],
	    	'<%PAGE_TITLE%>'		=> $page['SeoTitle'] ?: $this->pageTitle,
	    	'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
	    	'<%TITLE%>'				=> $page['Header'],
			'<%TEXT%>'				=> $page['Content'] ? '<div class="text content wow fadeInUpSmall fast animated delay-02s">' . $page['Content'] . '</div>' : '',
			'<%CONTENT%>'			=> ''
		));

		if ($page['Code'] == 'company' && !$this->folder['Code']) {
			krnLoadLib('modal');
			krnLoadLib('youtube');

			global $Site;
			//$Site->addScript('https://www.youtube.com/iframe_api');
			$youtube = new Youtube();
			$modalVideo = new Modal('video', ['VideoId' => '0']);
			$Site->addModal($modalVideo->getModal());

			$result = strtr($result, array(
				'<%CODE%>'	=> $youtube->GetCodeFromSource($this->settings->GetSetting('YoutubeCode'))
			));
		}

		if ($this->folder['Code'] == 'certs') {
			$result = strtr($result, array(
				'<%CERTS%>'	=> $this->GetCerts()
			));
		} elseif ($this->folder['Code'] == 'direction') {
			$result = strtr($result, array(
				'<%TEAM%>'	=> $this->GetTeam()
			));
		} elseif ($this->folder['Code'] == 'vacancies') {
			$result = strtr($result, array(
				'<%VACANCIES%>'	=> $this->GetVacancies()
			));
		} elseif ($this->folder['Code'] == 'media') {
			if ($this->galleryCode = $_LEVEL[3]) {
				$result = strtr($result, array(
					'<%GALLERIES%>'	=> $this->GetMediaGallery()
				));
			} else {
				$result = strtr($result, array(
					'<%GALLERIES%>'	=> $this->GetMediaGalleries()
				));
			}
		}

		$Blocks = krnLoadModuleByName('blocks', $page['Id']);
		$blocks = $Blocks->GetPageBlocks();

		foreach ($blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
				
		return $result;
	}

	public function GetCerts() {
		$element = LoadTemplate('certs_el');
		$content = '';
		$certs = $this->db->getAll('SELECT Title, Image AS ImageFull, Image500_694 AS Image, File FROM certs ORDER BY IF(`Order`, -1000/`Order`, 0)');
		foreach ($certs as $cert) {
			$content .= strtr($element, [
				'<%TITLE%>'		=> $cert['Title'],
				'<%ALT%>'		=> htmlspecialchars($cert['Title'], ENT_QUOTES),
				'<%IMAGE%>'		=> $cert['Image'],
				'<%IMAGEFULL%>'	=> $cert['ImageFull'],
				'<%DOWNLOAD%>'	=> $cert['File'] ? '<a href="' . $cert['File'] . '" class="download">Скачать</a>' : ''
			]);
		}

		$result = LoadTemplate('certs');
		$result = $content ? strtr($result, array(
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	public function GetTeam() {
		$element = LoadTemplate('team_el');
		$content = '';
		$persons = $this->db->getAll('SELECT Name, Post, Image446_446 AS Image, Tel, Email FROM team ORDER BY IF(`Order`, -1000/`Order`, 0)');
		foreach ($persons as $person) {
			$content .= strtr($element, [
				'<%NAME%>'	=> $person['Name'],
				'<%ALT%>'	=> htmlspecialchars($person['Name'], ENT_QUOTES),
				'<%POST%>'	=> $person['Post'],
				'<%IMAGE%>'	=> $person['Image'] ?: '/assets/images/_team_person.jpg',
				'<%TEL%>'	=> $person['Tel'],
				'<%TELLINK%>' => preg_replace('/[^\d\+]/', '', $person['Tel']),
				'<%EMAIL%>'	=> $person['Email']
			]);
		}

		$result = LoadTemplate('team');
		$result = $content ? strtr($result, array(
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	public function GetVacancies() {
		$element = LoadTemplate('vacancies_el');
		$content = '';
		$persons = $this->db->getAll('SELECT * FROM vacancies ORDER BY IF(`Order`, -1000/`Order`, 0)');
		foreach ($persons as $person) {
			$person['Class'] = $person['OpenedDefault'] ? ' class="opened"' : '';
			$person['Toggler'] = $person['OpenedDefault'] ? 'Скрыть' : 'Подробнее';
			if ($person['Stage']) $person['Stage'] = '<li>Требуемый опыт работы: ' . $person['Stage'] . '</li>';
			if ($person['Employment']) $person['Employment'] = '<li>Занятость: ' . $person['Employment'] . '</li>';
			if ($person['Responsibilities']) $person['Text'] .= '<h4>Обязанности:</h4><div class="text">' . $person['Responsibilities'] . '</div>';
			if ($person['Requirements']) $person['Text'] .= '<h4>Требования:</h4><div class="text">' . $person['Requirements'] . '</div>';
			if ($person['Conditions']) $person['Text'] .= '<h4>Условия:</h4><div class="text">' . $person['Conditions'] . '</div>';
			$content .= SetAtribs($element, $person);
		}

		$result = LoadTemplate('vacancies');
		$result = $content ? strtr($result, array(
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	public function GetMediaGalleries() {
		$element = LoadTemplate('galleries_el');
		$content = '';
		$galleries = $this->db->getAll('SELECT Title, Date, Image896_548 AS Image, Code FROM media_galleries ORDER BY Date DESC');
		foreach ($galleries as $gallery) {
			$gallery['Alt'] = htmlspecialchars($gallery['Title'], ENT_QUOTES);
			$gallery['Link'] = '/' . $this->page['Code'] . '/' . $this->folder['Code'] . '/' . $gallery['Code'] . '/';
			$gallery['Date'] = ModifiedDate($gallery['Date']);
			//$gallery['Text'] = nl2br($gallery['Text']);
			$content .= SetAtribs($element, $gallery);
		}

		$result = LoadTemplate('galleries');
		$result = $content ? strtr($result, array(
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	public function GetMediaGallery() {
		krnLoadLib('modal');
		krnLoadLib('youtube');

		global $Site;
		//$Site->addScript('https://www.youtube.com/iframe_api');

		$elementPhoto = LoadTemplate('gallery_photos_el');
		$elementVideo = LoadTemplate('gallery_videos_el');
		$content = '';

		$photos = $this->db->getAll('SELECT Title, Image896_548 AS Image, Image AS ImageFull FROM media_photos WHERE GalleryId = (SELECT Id FROM media_galleries WHERE Code = ?s) ORDER BY IF(`Order`, -1000/`Order`, 0)', $this->galleryCode);
		foreach ($photos as $photo) {
			$photo['Alt'] = htmlspecialchars($photo['Title'], ENT_QUOTES);
			$content .= SetAtribs($elementPhoto, $photo);
		}

		$youtube = new Youtube();
		$videos = $this->db->getAll('SELECT Id, Title, Code AS SourceCode, Cover896_548 AS Cover FROM media_videos WHERE GalleryId = (SELECT Id FROM media_galleries WHERE Code = ?s) ORDER BY IF (`Order`, -1000/`Order`, 0)', $this->galleryCode);
		foreach ($videos as $video) {
			$video['Code'] = $youtube->GetCodeFromSource($video['SourceCode']);
			$video['Alt'] = htmlspecialchars($video['Title'], ENT_QUOTES);
			$content .= SetAtribs($elementVideo, $video);

			$modalVideo = new Modal('video', ['VideoId' => $video['Id']]);
			$Site->addModal($modalVideo->getModal());
		}

		$result = LoadTemplate('gallery');
		$result = $content ? strtr($result, array(
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}
}

?>