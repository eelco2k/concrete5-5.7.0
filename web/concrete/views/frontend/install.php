<? 
defined('C5_EXECUTE') or die("Access Denied."); ?> 

<style type="text/css">@import "<?=ASSETS_URL_CSS?>/ccm.install.css";</style>
<script type="text/javascript" src="<?=ASSETS_URL_JAVASCRIPT?>/bootstrap.js"></script>
<script type="text/javascript" src="<?=ASSETS_URL_JAVASCRIPT?>/jquery.cookie.js"></script>
<script type="text/javascript">
$(function() {
	$(".launch-tooltip").tooltip({
		placement: 'bottom'
	});
});
</script>

<? 

$introMsg = t('To install concrete5, please fill out the form below.');

if (isset($successMessage)) { ?>

<script type="text/javascript">
$(function() {
	
<? for ($i = 1; $i <= count($installRoutines); $i++) {
	$routine = $installRoutines[$i-1]; ?>

	ccm_installRoutine<?=$i?> = function() {
		<? if ($routine->getText() != '') { ?>
			$("#install-progress-summary").html('<?=addslashes($routine->getText())?>');
		<? } ?>
		$.ajax('<?=$view->url("/install", "run_routine", $installPackage, $routine->getMethod())?>', {
			dataType: 'json',
			error: function(r) {
				$("#install-progress-wrapper").hide();
				$("#install-progress-errors").append('<div class="alert alert-danger">' + r.responseText + '</div>');
				$("#install-progress-error-wrapper").fadeIn(300);
			},
			success: function(r) {
				if (r.error) {
					$("#install-progress-wrapper").hide();
					$("#install-progress-errors").append('<div class="alert alert-danger">' + r.message + '</div>');
					$("#install-progress-error-wrapper").fadeIn(300);
				} else {
					$('#install-progress-bar div.progress-bar').css('width', '<?=$routine->getProgress()?>%');
					<? if ($i < count($installRoutines)) { ?>
						ccm_installRoutine<?=$i+1?>();
					<? } else { ?>
						$("#install-progress-wrapper").fadeOut(300, function() {
							$("#success-message").fadeIn(300);
						});
					<? } ?>
				}
			}
		});
	}
	
<? } ?>

	ccm_installRoutine1();

});

</script>

<div class="row">
<div class="col-sm-10 col-sm-offset-1">
<div class="page-header">
<h1><?=t('Install concrete5')?></h1>
<p><?=t('Version %s', APP_VERSION)?></p>
</div>
</div>
</div>


<div class="row">
<div class="col-sm-10 col-sm-offset-1">

<div id="success-message">
<?=$successMessage?>
<br/><br/>
<div class="well">
<input type="button" class="btn btn-large btn-primary" onclick="window.location.href='<?=DIR_REL?>/'" value="<?=t('Continue to your site')?>" />
</div>
</div>

<div id="install-progress-wrapper">
<div class="alert alert-info">
<div id="install-progress-summary">
<?=t('Beginning Installation')?>
</div>
</div>

<div id="install-progress-bar">
<div class="progress progress-striped active">
<div class="progress-bar" style="width: 0%;"></div>
</div>
</div>

</div>

<div id="install-progress-error-wrapper">
<div id="install-progress-errors"></div>
<div id="install-progress-back">
<input type="button" class="btn" onclick="window.location.href='<?=$view->url('/install')?>'" value="<?=t('Back')?>" />
</div>
</div>
</div>
</div>

<? } else if ($this->controller->getTask() == 'setup' || $this->controller->getTask() == 'configure') { ?>

<script type="text/javascript">
$(function() {
	$("#sample-content-selector td").click(function() {
		$(this).parent().find('input[type=radio]').prop('checked', true);
		$(this).parent().parent().find('tr').removeClass();
		$(this).parent().addClass('package-selected');
	});
});
</script>

<div class="row">
<div class="col-md-10 col-md-offset-1">

<div class="page-header">
<h1><?=t('Install concrete5')?></h1>
<p><?=t('Version %s', APP_VERSION)?></p>
</div>

</div>
</div>


<form action="<?=$view->url('/install', 'configure')?>" method="post" class="form-horizontal">

<div class="row">
<div class="col-md-5 col-md-offset-1">

	<input type="hidden" name="locale" value="<?=$locale?>" />
	
	<fieldset>
		<legend><?=t('Site Information')?></legend>
		<div class="form-group">
		<label for="SITE" class="control-label col-md-4"><?=t('Site Name')?>:</label>
		<div class="col-md-8">
			<?=$form->text('SITE', array('class' => ''))?>
		</div>
		</div>			
	</fieldset>
	
	<fieldset>
		<legend><?=t('Administrator Information')?></legend>
		<div class="form-group">
		<label for="uEmail" class="control-label col-md-4"><?=t('Email Address')?>:</label>
		<div class="col-md-8">
		<?=$form->email('uEmail', array('class' => ''))?>
		</div>
		</div>
		<div class="form-group">
		<label for="uPassword" class="control-label col-md-4"><?=t('Password')?>:</label>
		<div class="col-md-8">
		<?=$form->password('uPassword', array('class' => ''))?>
		</div>
		</div>
		<div class="form-group">
		<label for="uPasswordConfirm" class="control-label col-md-4"><?=t('Confirm Password')?>:</label>
		<div class="col-md-8">
			<?=$form->password('uPasswordConfirm', array('class' => ''))?>
		</div>
		</div>
		
	</fieldset>

</div>
<div class="col-sm-5">

	<fieldset>
		<legend><?=t('Database Information')?></legend>

	<div class="form-group">
	<label class="control-label col-md-4" for="DB_SERVER"><?=t('Server')?>:</label>
	<div class="col-md-8">
	<?=$form->text('DB_SERVER', array('class' => ''))?>
	</div>
	</div>

	<div class="form-group">
	<label class="control-label col-md-4" for="DB_USERNAME"><?=t('MySQL Username')?>:</label>
	<div class="col-md-8">
		<?=$form->text('DB_USERNAME', array('class' => ''))?>
	</div>
	</div>

	<div class="form-group">
	<label class="control-label col-md-4" for="DB_PASSWORD"><?=t('MySQL Password')?>:</label>
	<div class="col-md-8">
		<?=$form->password('DB_PASSWORD', array('class' => ''))?>
	</div>
	</div>

	<div class="form-group">
	<label class="control-label col-md-4" for="DB_DATABASE"><?=t('Database Name')?>:</label>
	<div class="col-md-8">
		<?=$form->text('DB_DATABASE', array('class' => ''))?>
	</div>
	</div>
	</fieldset>
</div>
</div>

<div class="row">
<div class="col-sm-10 col-sm-offset-1">

<h3><?=t('Sample Content')?></h3>

		
		<?
		$uh = Loader::helper('concrete/urls');
		?>
		
		<table class="table table-striped" id="sample-content-selector">
		<tbody>
		<? 
		$availableSampleContent = StartingPointPackage::getAvailableList();
		foreach($availableSampleContent as $spl) { 
			$pkgHandle = $spl->getPackageHandle();
		?>

		<tr class="<? if ($this->post('SAMPLE_CONTENT') == $pkgHandle || (!$this->post('SAMPLE_CONTENT') && $pkgHandle == 'standard') || count($availableSampleContent) == 1) { ?>package-selected<? } ?>">
			<td><?=$form->radio('SAMPLE_CONTENT', $pkgHandle, ($pkgHandle == 'standard' || count($availableSampleContent) == 1))?></td>
			<td class="sample-content-thumbnail"><img src="<?=$uh->getPackageIconURL($spl)?>" width="97" height="97" alt="<?=$spl->getPackageName()?>" /></td>
			<td class="sample-content-description"><h4><?=$spl->getPackageName()?></h4><p><?=$spl->getPackageDescription()?></td>
		</tr>
		
		<? } ?>
		
		</tbody>
		</table>
		<br/>
		<? if (!StartingPointPackage::hasCustomList()) { ?>
			<div class="alert alert-info"><?=t('concrete5 veterans can choose "Empty Site," but otherwise we recommend starting with some sample content.')?></div>
		<? } ?>

	
</div>
</div>

<div class="row">
<div class="col-sm-10 col-sm-offset-1">

<div class="well">
	<button class="btn btn-large btn-primary" type="submit"><?=t('Install concrete5')?> <i class="icon-thumbs-up icon-white"></i></button>
</div>

</div>
</div>

</form>


<? } else if (isset($locale) || count($locales) == 0) { ?>

<script type="text/javascript">

$(function() {
	$("#install-errors").hide();
});

<? if ($this->controller->passedRequiredItems()) { ?>
	var showFormOnTestCompletion = true;
<? } else { ?>
	var showFormOnTestCompletion = false;
<? } ?>


$(function() {
	$(".ccm-test-js img").hide();
	$("#ccm-test-js-success").show();
	if ($.cookie('CONCRETE5_INSTALL_TEST')) {
		$("#ccm-test-cookies-enabled-loading").attr('src', '<?=ASSETS_URL_IMAGES?>/icons/success.png');
	} else {
		$("#ccm-test-cookies-enabled-loading").attr('src', '<?=ASSETS_URL_IMAGES?>/icons/error.png');
		$("#ccm-test-cookies-enabled-tooltip").show();
		$("#install-errors").show();
		showFormOnTestCompletion = false;
	}
	$("#ccm-test-request-loading").ajaxError(function(event, request, settings) {
		$(this).attr('src', '<?=ASSETS_URL_IMAGES?>/icons/error.png');
		$("#ccm-test-request-tooltip").show();
		showFormOnTestCompletion = false;
	});
	$.getJSON('<?=$view->url("/install", "test_url", "20", "20")?>', function(json) {
		// test url takes two numbers and adds them together. Basically we just need to make sure that
		// our url() syntax works - we do this by sending a test url call to the server when we're certain 
		// of what the output will be
		if (json.response == 40) {
			$("#ccm-test-request-loading").attr('src', '<?=ASSETS_URL_IMAGES?>/icons/success.png');
			if (showFormOnTestCompletion) {
				$("#install-success").show();
			} else {
				$("#install-errors").show();
			}
		} else {
			$("#ccm-test-request-loading").attr('src', '<?=ASSETS_URL_IMAGES?>/icons/error.png');
			$("#ccm-test-request-tooltip").show();
			$("#install-errors").show();
		}
	});
	
});
</script>

<div class="row">

<div class="col-sm-10 col-sm-offset-1">
<div class="page-header">
	<h1><?=t('Install concrete5')?></h1>
	<p><?=t('Version %s', APP_VERSION)?></p>
</div>

<h3><?=t('Testing Required Items')?></h3>
</div>
</div>

<div class="row">
<div class="col-sm-5 col-sm-offset-1">

<table class="table table-striped requirements-table">
<tbody>
<tr>
	<td class="ccm-test-phpversion"><? if ($phpVtest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/success.png" /><? } else { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/error.png" /><? } ?></td>
	<td width="100%"><?=t(/*i18n: %s is the php version*/'PHP %s', $phpVmin)?></td>
	<td><? if (!$phpVtest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/tooltip.png" class="launch-tooltip" title="<?=t('concrete5 requires at least PHP %s', $phpVmin)?>" /><? } ?></td>
</tr>
<tr>
	<td class="ccm-test-js"><img id="ccm-test-js-success" src="<?=ASSETS_URL_IMAGES?>/icons/success.png" style="display: none" />
	<img src="<?=ASSETS_URL_IMAGES?>/icons/error.png" /></td>
	<td width="100%"><?=t('JavaScript Enabled')?></td>
	<td class="ccm-test-js"><img src="<?=ASSETS_URL_IMAGES?>/icons/tooltip.png" class="launch-tooltip" title="<?=t('Please enable JavaScript in your browser.')?>" /></td>
</tr>
<tr>
	<td><? if ($mysqlTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/success.png" /><? } else { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/error.png" /><? } ?></td>
	<td width="100%"><?=t('MySQL Available')?>
	</td>
	<td><? if (!$mysqlTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/tooltip.png" class="launch-tooltip" title="<?=$this->controller->getDBErrorMsg()?>" /><? } ?></td>
</tr>
<tr>
	<td><img id="ccm-test-request-loading"  src="<?=ASSETS_URL_IMAGES?>/dashboard/sitemap/loading.gif" /></td>
	<td width="100%"><?=t('Supports concrete5 request URLs')?>
	</td>
	<td><img id="ccm-test-request-tooltip" src="<?=ASSETS_URL_IMAGES?>/icons/tooltip.png" class="launch-tooltip" title="<?=t('concrete5 cannot parse the PATH_INFO or ORIG_PATH_INFO information provided by your server.')?>" /></td>
</tr>
<tr>
	<td><? if ($jsonTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/success.png" /><? } else { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/error.png" /><? } ?></td>
	<td width="100%"><?=t('JSON Extension Enabled')?>
	</td>
	<td><? if (!$jsonTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/tooltip.png" class="launch-tooltip" title="<?=t('You must enable PHP\'s JSON support. This should be enabled by default in PHP 5.2 and above.')?>" /><? } ?></td>
</tr>

</table>

</div>
<div class="col-sm-5">

<table class="table table-striped requirements-table">

<tr>
	<td><? if ($imageTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/success.png" /><? } else { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/error.png" /><? } ?></td>
	<td width="100%"><?=t('Image Manipulation Available')?>
	</td>
	<td><? if (!$imageTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/tooltip.png" class="launch-tooltip" title="<?=t('concrete5 requires GD library 2.0.1 or greater')?>" /><? } ?></td>
</tr>
<tr>
	<td><? if ($xmlTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/success.png" /><? } else { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/error.png" /><? } ?></td>
	<td width="100%"><?=t('XML Support')?>
	</td>
	<td><? if (!$xmlTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/tooltip.png" class="launch-tooltip" title="<?=t('concrete5 requires PHP XML Parser and SimpleXML extensions')?>" /><? } ?></td>
</tr>
<tr>
	<td><? if ($fileWriteTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/success.png" /><? } else { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/error.png" /><? } ?></td>
	<td width="100%"><?=t('Writable Files and Configuration Directories')?>
	</td>
	<td><? if (!$fileWriteTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/tooltip.png" class="launch-tooltip" title="<?=t('The config/, packages/ and files/ directories must be writable by your web server.')?>" /><? } ?></td>
</tr>
<tr>
	<td><img id="ccm-test-cookies-enabled-loading"  src="<?=ASSETS_URL_IMAGES?>/dashboard/sitemap/loading.gif" /></td>
	<td width="100%"><?=t('Cookies Enabled')?>
	</td>
	<td><img id="ccm-test-cookies-enabled-tooltip" src="<?=ASSETS_URL_IMAGES?>/icons/tooltip.png" class="launch-tooltip" title="<?=t('Cookies must be enabled in your browser to install concrete5.')?>" /></td>
</tr>

</tbody>
</table>

</div>
</div>


<div class="row">
<div class="col-sm-10 col-sm-offset-1">

<h3><?=t('Testing Optional Items')?></h3>

</div>
</div>

<div class="row">
<div class="col-sm-5 col-sm-offset-1">

<table class="table table-striped requirements-table">
<tbody>
<tr>
	<td><? if ($remoteFileUploadTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/success.png" /><? } else { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/warning.png" /><? } ?></td>
	<td width="100%"><?=t('Remote File Importing Available')?>
	</td>
	<td><? if (!$remoteFileUploadTest) { ?><img src="<?=ASSETS_URL_IMAGES?>/icons/tooltip.png" class="launch-tooltip" title="<?=t('Remote file importing through the file manager requires the iconv PHP extension.')?>" /><? } ?></td>
</tr>
</table>

</div>
</div>

<div class="row">
<div class="col-sm-10 col-sm-offset-1">
<div class="well" id="install-success">
	<form method="post" action="<?=$view->url('/install','setup')?>">
	<input type="hidden" name="locale" value="<?=$locale?>" />
	<a class="btn btn-large btn-primary" href="javascript:void(0)" onclick="$(this).parent().submit()"><?=t('Continue to Installation')?> <i class="icon-arrow-right icon-white"></i></a>
	</form>
</div>

<div class="alert alert-error" id="install-errors">
	<p><?=t('There are problems with your installation environment. Please correct them and click the button below to re-run the pre-installation tests.')?></p>
	<div class="block-actions">
	<form method="post" action="<?=$view->url('/install')?>">
	<input type="hidden" name="locale" value="<?=$locale?>" />
	<a class="btn" href="javascript:void(0)" onclick="$(this).parent().submit()"><?=t('Run Tests')?> <i class="icon-refresh"></i></a>
	</form>
	</div>	
</div>

<div class="alert alert-info">
<?=t('Having trouble? Check the <a href="%s">installation help forums</a>, or <a href="%s">have us host a copy</a> for you.', 'http://www.concrete5.org/community/forums/installation', 'http://www.concrete5.org/services/hosting')?>
</div>
</div>
</div>

<? } else { ?>

<div class="row">
<div class="col-sm-8 col-sm-offset-2">
<div class="page-header">
	<h1><?=t('Install concrete5')?></h1>
	<p><?=t('Version %s', APP_VERSION)?></p>
</div>
</div>
</div>

<div class="row">
<div class="col-sm-8 col-sm-offset-2">

<div id="ccm-install-intro">

<form method="post" class="form-horizontal" action="<?=$view->url('/install', 'select_language')?>">
<fieldset>
	<div class="form-group">
	<label for="locale" class="control-label col-sm-3"><?=t('Language')?></label>
	<div class="col-sm-7">
		<?=$form->select('locale', $locales, 'en_US'); ?>
	</div>
	</div>
	<div class="form-group col-sm-10">
		<button type="submit" class="btn btn-primary pull-right"><?=t('Choose Language')?></button>
	</div>

</fieldset>
</form>

</div>
</div>
</div>

<? } ?>