<?
namespace Concrete\Attribute\SocialLinks;
use Loader;
use \Concrete\Core\Foundation\Object;
use \Concrete\Core\Attribute\Controller as AttributeTypeController;

class Controller extends AttributeTypeController  {

	
	public function getServices() {
		$services = array(
			array('facebook', 'Facebook', t('http://facebook.com/username')),
			array('twitter', 'Twitter', t('http://twitter.com/username')),
			array('pinterest', 'Pinterest', t('http://pinterest.com/username')),
			array('youtube', 'Youtube', t('Youtube channel or profile URL.')),
			array('google-plus', 'Google Plus', t('Google Plus profile URL.')),
			array('flickr', 'Flickr', t('Enter your Flickr Profile URL.')),
			array('myspace', 'MySpace'),
			array('wthree', 'Other')
		);
		return $services;	
	}
	
	public function saveForm($data) {
		if (!is_array($data['service'])) {
			$data['service'] = array();
		}
		if (!is_array($data['serviceInfo'])) {
			$data['serviceInfo'] = array();
		}
		for ($i = 0; $i < count($data['service']); $i++) {
			$values[$data['service'][$i]] = $data['serviceInfo'][$i];
		}
		$this->saveValue($values);
	}
	
	public function saveValue($values) {
		$db = Loader::db();
		$db->Execute('delete from atSocialLinks where avID = ?', array($this->getAttributeValueID()));
		foreach($values as $service => $serviceInfo) {
			if ($serviceInfo) {
				$service = Loader::helper('text')->entities($service);
				$serviceInfo = Loader::helper('text')->entities($serviceInfo);			
				$db->Execute('insert into atSocialLinks (avID, service, serviceInfo) values (?, ?, ?)', array($this->getAttributeValueID(), $service, $serviceInfo));
			}
		}
	}
	
	public function getValue() {
		$db = Loader::db();
		$services = array();
		$r = $db->Execute('select service, serviceInfo from atSocialLinks where avID = ? order by avsID asc', array($this->getAttributeValueID()));
		while ($row = $r->FetchRow()) {
			$services[$row['service']] = $row['serviceInfo'];
		}
		return $services;
	}
	
	protected function getServiceLink($service, $serviceInfo) {
		/*
		$link = '';
		$services = $this->getServices();
		foreach($services as $s) {
			if ($s[0] == $service) {
				$link = $s[3];
			}
		}
		$link .= $serviceInfo;
		return $link;
		*/

		$link = $serviceInfo;
		if (strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0) {
			return $link;
		} else {
			return 'http://' . $link;
		}
	}

	public function deleteKey() {
		$db = Loader::db();
		$arr = $this->attributeKey->getAttributeValueIDList();
		foreach($arr as $id) {
			$db->Execute('delete from atSocialLinks where avID = ?', array($id));
		}
	}

	public function deleteValue() {
		$db = Loader::db();
		$db->Execute('delete from atSocialLinks where avID = ?', array($this->getAttributeValueID()));
	}
	
	protected function getServiceName($service, $serviceInfo) {
		$services = $this->getServices();
		foreach($services as $s) {
			if ($s[0] == $service) {
				if ($service == 'wthree') {
					return $serviceInfo;
				}
				return $s[1];
			}
		}
	}
	
	public function getDisplayValue() {
		$html = '';
		$services = $this->getValue();
		if (count($services) > 0) {
			$env = Environment::get();
			$url = $env->getURL(DIRNAME_ATTRIBUTES . '/social_links/view.css');
			$this->addHeaderItem(Loader::helper('html')->css($url));
			$html .=  '<div class="ccm-social-link-attribute-display">';
			foreach($services as $service => $serviceInfo) {
				$html .= '<div class="ccm-social-link-service">';
				$icon = $service;
				$html .=  '<div class="ccm-social-link-service-icon"><a href="' . $this->getServiceLink($service, $serviceInfo) . '"><img src="' . ASSETS_URL_IMAGES . '/icons/social/' . $service . '.png" width="16" height="16" /></a></div>';
				$html .=  '<div class="ccm-social-link-service-info"><a href="' . $this->getServiceLink($service, $serviceInfo) . '">' . $this->getServiceName($service, $serviceInfo) . '</a></div>';
				$html .=  '</div>';
			}
			$html .=  '</div>';		
		}
		return $html;
	}
	
	public function form() {
		if ($this->isPost()) {
			$data['service'] = $this->post('service');			
			$data['serviceInfo'] = $this->post('serviceInfo');			
		} else {
			$d = $this->getValue();
			foreach($d as $k => $v) {
				$data['service'][] = $k;
				$data['serviceInfo'][] = $v;
			}
		}
		if (!is_array($data['service'])) {
			$data['service'][] = 'facebook';
			$data['serviceInfo'][] = '';
		}
		$this->set('data', $data);
		$this->set('services', $this->getServices());
	}
}