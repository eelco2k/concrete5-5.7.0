<?
defined('C5_EXECUTE') or die("Access Denied.");
?>

<?
$pk = PermissionKey::getByID($_REQUEST['pkID']);
$pk->setPermissionObject($pagetype);
	
?>

<? Loader::element("permission/detail", array('permissionKey' => $pk)); ?>

<script type="text/javascript">
var ccm_permissionDialogURL = '<?=REL_DIR_FILES_TOOLS_REQUIRED?>/permissions/dialogs/page_type?ptID=<?=$pagetype->getPageTypeID()?>'; 
</script>