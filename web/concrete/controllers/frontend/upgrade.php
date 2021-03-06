<?php
namespace Concrete\Controller\Frontend;

ini_set('display_errors', 1);
if (!ini_get('safe_mode')) {
	set_time_limit(0);
	ini_set("max_execution_time", 0);
}

date_default_timezone_set(@date_default_timezone_get());

use Controller;
class Upgrade extends Controller {

	private $notes = array();
	private $upgrades = array();
	private $site_version = null;
	public $upgrade_db = true;
	
	protected $viewPath = '/frontend/upgrade';
	
	public function on_start() {
		$this->secCheck();
		// if you just reverted, but didn't manually clear out your files - cache would be a prob here.
		Cache::disableLocalCache();
		$this->site_version = Config::get('SITE_APP_VERSION');
		Database::ensureEncoding();
	}

	public function secCheck() {
		$fh = Loader::helper('file');
		$updates = $fh->getDirectoryContents(DIR_CORE_UPDATES);
		foreach($updates as $upd) {
			if (is_dir(DIR_CORE_UPDATES . '/' . $upd) && is_writable(DIR_CORE_UPDATES . '/' . $upd)) {
				if (file_exists(DIR_CORE_UPDATES . '/' . $upd . '/' . DISPATCHER_FILENAME) && is_writable(DIR_CORE_UPDATES . '/' . $upd . '/' . DISPATCHER_FILENAME)) {
					unlink(DIR_CORE_UPDATES . '/' . $upd . '/' . DISPATCHER_FILENAME);
				}
				if (!file_exists(DIR_CORE_UPDATES . '/' . $upd . '/index.html')) {
					touch(DIR_CORE_UPDATES . '/' . $upd . '/index.html');
				}
			}
		}
	}
	
	public function view() {
		if ($this->get('force') == 1 || $this->get('source') == 'dashboard_update') {
			$this->do_upgrade();
		} else {	
			$sav = $this->site_version;
	
			if (!$sav) {
				$message = t('Unable to determine your current version of concrete5. Upgrading cannot continue.');
			} else 	if (version_compare($sav, APP_VERSION, '>')) {
				$message = t('Upgrading from <b>%s</b>', $sav) . '<br/>';
				$message .= t('Upgrading to <b>%s</b>', APP_VERSION) . '<br/><br/>';
				$message .= t('Your current website uses a version of concrete5 greater than this one. You cannot upgrade.');
				$this->set('status', $message);
			} else if (version_compare($sav, APP_VERSION, '=')) {
				$this->set('status', t('Your site is already up to date! The current version of concrete5 is <b>%s</b>. You should remove this file for security.', APP_VERSION));
			} else {
				if ($this->post('do_upgrade')) {
					$this->do_upgrade();
				} elseif(version_compare($sav, '5.4.2.2', '<')) {
					$this->set('hide_force',true);
					$this->set('status',t('You must first upgrade your site to version 5.4.2.2'));
				} else {
					// do the upgrade
					$this->set_upgrades();
					$allnotes = array();
					foreach($this->upgrades as $ugh) {
						if (method_exists($ugh, 'notes')) {
							$notes = $ugh->notes();
							if ($notes != '') {
								if (is_array($notes)) {
									$allnotes = array_merge($allnotes, $notes);
								} else {
									$allnotes[] = $notes;
								}
							}
						}
					}
					
					$message = '';
					$message .= t('Upgrading from <b>%s</b>', $sav) . '<br/>';
					$message .= t('Upgrading to <b>%s</b>', APP_VERSION) . '<br/><br/>';
	
					if (count($allnotes) > 0) { 
						$message .= '<ul>';
						foreach($allnotes as $n) {
							$message .= '<li>' . $n . '</li>';
						}
						$message .= '</ul><br/>';
					}
					
					$this->set('do_upgrade', true);			
					$this->set('status', $message);
				}
			}
		}		
	}
	
	private function set_upgrades() {
		$this->upgrades = Loader::helper('concrete/upgrade')->getList($this->site_version);
	}

	public function refresh_schema() {
		if ($this->upgrade_db) {
			$installDirectory = DIR_BASE_CORE . '/config';
			$file = $installDirectory . '/db.xml';
			if (!file_exists($file)) {
				throw new Exception(t('Unable to locate database import file.'));
			}		
			$err = Package::installDB($file);
			
			// now we refresh the block schema
			$btl = new BlockTypeList();
			$btArray = $btl->getInstalledList();
			foreach($btArray as $bt) {
				$bt->refresh();
			}
			$this->upgrade_db = false;
		}
	}
	
	private function do_upgrade() {
		$runMessages = array();
		$prepareMessages = array();
		try {
			Cache::flush();
			$this->set_upgrades();

			foreach($this->upgrades as $ugh) {
				if (method_exists($ugh, 'prepare')) {
					$prepareMessages[] =$ugh->prepare();
				}

				if (isset($ugh->dbRefreshTables) && count($ugh->dbRefreshTables) > 0) {
					Loader::helper('concrete/upgrade')->refreshDatabaseTables($ugh->dbRefreshTables);
				}

				if (method_exists($ugh, 'run')) {
					$runMessages[] = $ugh->run();
				}

			}
			
			$message = '';
			if(is_array($prepareMessages) && count($prepareMessages)) {
				foreach($prepareMessages as $m) {
					if(is_array($m)) {
						$message .= implode("<br/>",$m);
					}	
				}
			}
			
			if(is_array($runMessages) && count($runMessages)) {
				foreach($runMessages as $m) {
					if(is_array($m)) {
						$message .= implode("<br/>",$m);
					}	
				}
				
				if(strlen($message)) {
					$this->set('had_failures',true);
				}
			
			}			
			$upgrade = true;
		} catch(Exception $e) {
			$upgrade = false;
			$message .= '<div class="alert-message block-message error"><p>' . t('An Unexpected Error occurred while upgrading: %s', $e->getTraceAsString()) . '</p></div>';
		}
		
		if ($upgrade) {
			$completeMessage .= '<div class="alert-message block-message success"><p>' . t('Upgrade to <b>%s</b> complete!', APP_VERSION) . '</p></div>';
			Config::save('SITE_APP_VERSION', APP_VERSION);
		}
		$this->set('completeMessage',$completeMessage);	
		$this->set('status', $message);
	}
}
	
