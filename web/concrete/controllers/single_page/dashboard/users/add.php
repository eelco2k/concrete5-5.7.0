<?
namespace Concrete\Controller\SinglePage\Dashboard\Users;
use \Concrete\Core\Page\Controller\DashboardPageController;
use Loader;
use UserInfo;
use PermissionKey;
use Permissions;
use UserAttributeKey;
use Group;
use Localization;
use GroupList;

class Add extends DashboardPageController {

	public function view() {

		$loc = Localization::getInstance();
		$locales = Localization::getAvailableInterfaceLanguageDescriptions($loc->activeLocale());
		$attribs = UserAttributeKey::getRegistrationList();
		$assignment = PermissionKey::getByHandle('edit_user_properties')->getMyAssignment();
		$gl = new GroupList();
		$gl->setItemsPerPage(10000);
		$gArray = $gl->getPage();

		$this->set('form',Loader::helper('form'));
		$this->set('valt',Loader::helper('validation/token'));
		$this->set('valc',Loader::helper('concrete/validation'));
		$this->set('ih',Loader::helper('concrete/ui'));
		$this->set('av',Loader::helper('concrete/avatar'));
		$this->set('dtt',Loader::helper('form/date_time'));
		$this->set('gArray', $gArray);
		$this->set('assignment', $assignment);
		$this->set('locales', $locales);
		$this->set('attribs', $attribs);
	}

	public function submit(){
		$assignment = PermissionKey::getByHandle('edit_user_properties')->getMyAssignment();
		$vals = Loader::helper('validation/strings');
		$valt = Loader::helper('validation/token');
		$valc = Loader::helper('concrete/validation');
		
		
		$username = trim($_POST['uName']);
		$username = preg_replace("/\s+/", " ", $username);
		$_POST['uName'] = $username;	
		
		$password = $_POST['uPassword'];
		
		if (!$vals->email($_POST['uEmail'])) {
			$this->error->add(t('Invalid email address provided.'));
		} else if (!$valc->isUniqueEmail($_POST['uEmail'])) {
			$this->error->add(t("The email address '%s' is already in use. Please choose another.",$_POST['uEmail']));
		}
		
		if (strlen($username) < USER_USERNAME_MINIMUM) {
			$this->error->add(t('A username must be at least %s characters long.',USER_USERNAME_MINIMUM));
		}
	
		if (strlen($username) > USER_USERNAME_MAXIMUM) {
			$this->error->add(t('A username cannot be more than %s characters long.',USER_USERNAME_MAXIMUM));
		}
	
		if (strlen($username) >= USER_USERNAME_MINIMUM && !$valc->username($username)) {
			if(USER_USERNAME_ALLOW_SPACES) {
				$this->error->add(t('A username may only contain letters, numbers, spaces, dots (not at the beginning/end), underscores (not at the beginning/end).'));
			} else {
				$this->error->add(t('A username may only contain letters numbers, dots (not at the beginning/end), underscores (not at the beginning/end).'));
			}
		}
	
		if (!$valc->isUniqueUsername($username)) {
			$this->error->add(t("The username '%s' already exists. Please choose another",$username));
		}		
	
		if ($username == USER_SUPER) {
			$this->error->add(t('Invalid Username'));
		}
	
		
		if ((strlen($password) < USER_PASSWORD_MINIMUM) || (strlen($password) > USER_PASSWORD_MAXIMUM)) {
			$this->error->add(t('A password must be between %s and %s characters',USER_PASSWORD_MINIMUM,USER_PASSWORD_MAXIMUM));
		}
			
		if (strlen($password) >= USER_PASSWORD_MINIMUM && !$valc->password($password)) {
			$this->error->add(t('A password may not contain ", \', >, <, or any spaces.'));
		}
	
		if (!$valt->validate('submit')) {
			$this->error->add($valt->getErrorMessage());
		}
	
		$aks = UserAttributeKey::getRegistrationList();
	
		foreach($aks as $uak) {
			if ($uak->isAttributeKeyRequiredOnRegister()) {
				$e1 = $uak->validateAttributeForm();
				if ($e1 == false) {
					$this->error->add(t('The field "%s" is required', $uak->getAttributeKeyDisplayName()));
				} else if ($e1 instanceof \Concrete\Core\Error\Error) {
					$this->error->add( $e1->getList() );
				}
			}
		}
		
		if (!$this->error->has()) {
			// do the registration
			$data = array('uName' => $username, 'uPassword' => $password, 'uEmail' => $_POST['uEmail'], 'uDefaultLanguage' => $_POST['uDefaultLanguage']);
			$uo = UserInfo::add($data);
			
			if (is_object($uo)) {
				
				$av = Loader::helper('concrete/avatar'); 
				if ($assignment->allowEditAvatar()) {
					if (is_uploaded_file($_FILES['uAvatar']['tmp_name'])) {
						$uHasAvatar = $av->updateUserAvatar($_FILES['uAvatar']['tmp_name'], $uo->getUserID());
					}
				}
				
				foreach($aks as $uak) {
					if (in_array($uak->getAttributeKeyID(), $assignment->getAttributesAllowedArray())) { 
						$uak->saveAttributeForm($uo);
					}
				}

				$gIDs = array();
				if (is_array($_POST['gID'])) {
					foreach($_POST['gID'] as $gID) {
						$gx = Group::getByID($gID);
						$gxp = new Permissions($gx);
						if ($gxp->canAssignGroup()) {
							$gIDs[] = $gID;
						}
					}
				}

				$uo->updateGroups($gIDs);
				$uID = $uo->getUserID();
				$this->redirect('/dashboard/users/search', 'view', $uID, 'created');
			} else {
				$this->error->add(t('An error occurred while trying to create the account.'));
				$this->set('error',$this->error);
			}
			
		} else {
			$this->view();
		}
	}
}
