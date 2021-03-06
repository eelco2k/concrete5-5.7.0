<? defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="row">
<div class="span8 offset2">

<h1 class="page-header"><?=t('User Avatar')?></h1>
<p><?php echo t('Change the picture attached to my posts.')?></p>

		<div id="profile-avatar"> 	
			<?php echo t('You need the Adobe Flash plugin installed on your computer to upload and crop your user profile picture.')?>
			<br /><br />
			<a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash">Download the Flash Player here</a>.
		</div>
		<? if ($profile->hasAvatar()) { ?>
			<br/><br/>
			<a href="<?php echo $view->action('delete')?>" class="btn btn-danger"><?php echo t('Remove your user avatar')?> <i class="icon-trash icon-white"></i></a>
		<? } ?>	
			
		<script type="text/javascript">
		ThumbnailBuilder_onSaveCompleted = function() { 
			window.location.href="<?php echo $view->url('/account/profile/avatar', 'saved')?>";
		};

		/* <?php /* flashvars - options for the avatar/thumb picker  
		upload -- whether to enable or disable the upload button (default is "true")
		webcam -- whether to enable or disable the webcam button (default is "true")
		format -- whether to use "jpg", "png" or "auto" when encoding (default is "auto")
		imagePath -- which image should be preloaded into the editor
		overlayPath -- an optional image to provide chrome over the top of thumbnails
		redirectPath -- an optional path to redirect to once an image has been saved
		savePath -- an optional path for saving images ($_POST["thumbnail"], base64) instead of saving locally
		rounding -- an optional amount of pixel rounding on thumbnail edges (will output transparent corners)
		width -- the width of the thumbnail
		quality -- the quality of the output image when using the JPEG encoder (default is 80)
		height -- the height of the thumbnail
		backgroundColor -- the color to use when tinting the background of the editor (default is 0xFFFFFF)
		tint -- the amount of strength to apply when tinting the background of the editor (default is 0)
		*/ ?> */
		
		$(function(){   
			var params = { 
				bgcolor: "#ffffff",
				wmode:  "transparent",
				quality:  "high" 
			};
			var flashvars = {
				width: '<?php echo AVATAR_WIDTH?>',
				height: '<?php echo AVATAR_HEIGHT?>',
				image: '<?php echo $av->getImagePath($profile)?>',
				save: "<?php echo $view->url($c->getCollectionPath(), 'save_thumb')?>"    
			};
			swfobject.embedSWF ("<?php echo ASSETS_URL_JAVASCRIPT?>/thumbnail_editor_3.swf", "profile-avatar", "500", "400", "10,0,0,0", "includes/expressInstall.swf", flashvars, params);
	
			
	   });
		</script>

</div>
</div>