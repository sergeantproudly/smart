<?php

krnLoadLib('define');
krnLoadLib('settings');

class blocks extends krn_abstract{
	
	private $page_id;
	private $blocks_sequence = array();
	private $blocks_info = array();
	private $forms_info = array();
	private $rel_codes_methods = array(
	);
	private $flag = false;

	public function __construct($pageId = false) {
		parent::__construct();

		global $Params;
		$this->page_id = $pageId ?: $Params['Site']['Page']['Id'];
		
		$query = 'SELECT p2b.*, b.Code '
				.'FROM `rel_pages_blocks` AS p2b '
				.'LEFT JOIN `blocks` AS b ON p2b.BlockId = b.Id '
				.'LEFT JOIN `static_pages` AS s ON p2b.PageId = s.Id '
				.'WHERE s.Id = ?s '
				.'ORDER BY IF(p2b.`Order`,-30/p2b.`Order`,0)';
		$blocks = $this->db->getAll($query, $this->page_id);
		foreach ($blocks as $block) {
			$this->blocks_sequence[] = $block['Code'];
			$this->blocks_info[$block['Code']][] = $block;
		}
		
		$forms = $this->db->getAll('SELECT * FROM `forms`');
		foreach ($forms as $form) {
			$this->forms_info[$form['Code']] = $form;
		}
	}
	
	public function GetResult() {}

	public function GetPageBlocks($data = array()) {
		$html = [];
		$counter = [];
		foreach ($this->blocks_sequence as $code) {
			if (!isset($counter[$code])) $counter[$code] = 0;

			if (isset($this->rel_codes_methods[$code])) {
				$func = $this->rel_codes_methods[$code];
				$code_param = $code;
			} else {
				$func = 'Block';
				foreach (explode('_', $code) as $fragments) {
					$func .= ucfirst($fragments);
				}
			}

			$info['Index'] = $counter[$code];
			$info['Code'] = isset($code_param) ? $code_param : $code;
			if (method_exists($this, $func)) {
				$html[] = $this->$func($info, $data);
			} else {
				$html[] = $this->BlockText($info, $data);
			}

			$counter[$code]++;
		}
		return $html;
	}

	public function GetBlockParams($blockCode, $index = 0) {
		$params = [];
		foreach (explode(';', $this->blocks_info[$blockCode][$index]['Params']) as $line) {
			list($param, $value) = explode(':', $line, 2);
			$params[ucfirst(trim($param))] = trim($value);
		}
		return $params;
	}
	
	/** Блок - Текстовый */
	public function BlockText($data = array()) {
		$code = $data['Code'];
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);

		$result = LoadTemplate($code ? 'bl_'.$code : 'bl_text');
		$result = strtr($result, array(
			'<%CLASS%>'		=> $params['Class'] ?: '',
			'<%HEADER%>'	=> $this->blocks_info[$code][$index]['Header'] ? '<h2>'.$this->blocks_info[$code][$index]['Header'].'</h2>' : '',
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'] ? '<h2>'.$this->blocks_info[$code][$index]['Header'].'</h2>' : '',
			'<%CONTENT%>'	=> $this->blocks_info[$code][$index]['Content'],
			'<%HR%>'		=> $params['Hr'] == 1 ? '<hr class="hr">' : '',
		));
		if ($params['ExcludedClass']) $result = str_replace($params['ExcludedClass'], '', $result);
		return $result;
	}

	/** Блок - Форма */
	public function BlockForm($data = array()) {
		$code = $data['Code'];
		$index = $data['Index'];
		$result = LoadTemplate($code);
		$result = strtr($result, array(
			'<%TITLE%>'	=> $this->forms_info[$code][$index]['Title'],
			'<%TEXT%>'	=> $this->forms_info[$code][$index]['Text'],
			'<%CODE%>'	=> $this->forms_info[$code][$index]['Code']
		));
		return $result;
	}

	/** Блок - Новости */
	public function BlockNews($data = array()) {
		$code = 'news';
		$index = $data['Index'];

		$element = LoadTemplate('bl_news_el');
		$content = '';
		$news = $this->db->getAll('SELECT Title, Date, Code, Announce AS Text FROM news ORDER BY Date DESC LIMIT 0, ?i', 3);
		$counter = 1;
		foreach ($news as $new) {
			$counter += 2;
			$content .= strtr($element, [
				'<%TITLE%>'		=> $new['Title'],
				'<%LINK%>'		=> '/articles/' . $new['Code'] . '/',
				'<%DATE%>'		=> ModifiedDate($new['Date']),
				'<%TEXT%>'		=> nl2br($new['Text']),
				'<%I%>'			=> $counter
			]);
		}

		$result = LoadTemplate('bl_news');
		$result = $content ? strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	/** Блок - Контакты */
	public function BlockContacts($data = array()) {
		$code = 'contacts';

		$contact = $this->db->getRow('SELECT * FROM contacts ORDER BY IF(`Order`, -100/`Order`, 0) ASC LIMIT 0, 1');			
		$tel = $contact['Tel1'];
		$tellink = preg_replace('/[^\d\+]/', '', $tel);
		$tel2 = $contact['Tel2'];
		$tellink2 = preg_replace('/[^\d\+]/', '', $tel2);
		$email = $contact['Email1'];
		$email2 = $contact['Email2'];
		$address = $contact['Address'];

		$result = LoadTemplate('bl_contacts');
		$result = strtr($result, array(
			'<%TITLE%>'			=> 'Контакты',
			'<%FULLADDRESS%>'	=> $address,
			'<%TEL%>'			=> $tel,
			'<%TELLINK%>'		=> $tellink,
			'<%TEL2%>'			=> $tel2,
			'<%TELLINK2%>'		=> $tellink2,
			'<%EMAIL%>'			=> $email,
			'<%EMAIL2%>'		=> $email2,
			'<%COORDS%>'		=> $contact['MapCoords'],
		));
		return $result;
	}

	/** Блок - Услуги (крупно) */
	public function BlockServicesChess($data = array()) {
		$code = 'services_chess';
		$index = $data['Index'];

		$element = LoadTemplate('bl_services_chess_el');
		$elementVideo = LoadTemplate('bl_services_chess_video_el');
		$content = '';
		$services = $this->db->getAll('SELECT Id, Title, Code, Image FROM services ORDER BY IF(`Order`, -100/`Order`, 0) ASC');
		$counter = 0;
		foreach ($services as $service) {
			$counter = $counter == 0 ? 2 : 0;
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
				//$Site->addScript('https://www.youtube.com/iframe_api');
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

	/** Блок - Услуги (списком) */
	public function BlockServices($data = array()) {
		$code = 'services';
		$index = $data['Index'];

		$element = LoadTemplate('bl_services_el');
		$content = '';
		$services = $this->db->getAll('SELECT Title, Code, Image778_532 AS Image FROM services WHERE Id <> ?i ORDER BY IF(`Order`, -100/`Order`, 0) ASC', VIDEO_SERVICE_ID);
		foreach ($services as $service) {
			$content .= strtr($element, [
				'<%TITLE%>'		=> $service['Title'],
				'<%ALT%>'		=> htmlspecialchars($service['Title'], ENT_QUOTES),
				'<%LINK%>'		=> '/services/' . $service['Code'] . '/',
				'<%IMAGE%>'		=> $service['Image'],
			]);
		}

		$result = LoadTemplate('bl_services');
		$result = $content ? strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	/** Блок - Статистика компании */
	public function BlockStatsFull($data = array()) {
		$code = 'stats_full';
		$index = $data['Index'];

		$element = LoadTemplate('bl_stats_full_el');
		$elementLinked = LoadTemplate('bl_stats_full_linked_el');
		$content = '';
		$stats = $this->db->getAll('SELECT * FROM statistics WHERE OnMain = 0 ORDER BY IF (`Order`, -100/`Order`, 0) ASC');
		foreach ($stats as $stat) {
			$content .= SetAtribs($stat['Link'] ? $elementLinked : $element, $stat);
		}

		$result = LoadTemplate('bl_stats_full');
		$result = $content ? strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	/** Блок - Форма обратной связи */
	public function BlockFeedback($data = array()) {
		$code = 'feedback';
		$result = LoadTemplate('bl_feedback');
		$result = strtr($result, array(
			'<%TITLE%>'	=> $this->forms_info[$code]['Title'],
			'<%TEXT%>'	=> strip_tags($this->forms_info[$code]['Text']),
			'<%CODE%>'	=> $code
		));
		return $result;
	}
}
?>