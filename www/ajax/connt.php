<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<? $APPLICATION->IncludeComponent(
						"luxol:iblock.element.add.cont",
						"",
						Array(
							"CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
							"CUSTOM_TITLE_DATE_ACTIVE_TO" => "",
							"CUSTOM_TITLE_DETAIL_PICTURE" => "",
							"CUSTOM_TITLE_DETAIL_TEXT" => "",
							"CUSTOM_TITLE_IBLOCK_SECTION" => "",
							"CUSTOM_TITLE_NAME" => "",
							"CUSTOM_TITLE_PREVIEW_PICTURE" => "",
							"CUSTOM_TITLE_PREVIEW_TEXT" => "",
							"CUSTOM_TITLE_TAGS" => "",
							"DEFAULT_INPUT_SIZE" => "30",
							"DETAIL_TEXT_USE_HTML_EDITOR" => "N",
							"ELEMENT_ASSOC" => "CREATED_BY",
							"GROUPS" => array("2"),
							"IBLOCK_ID" => "3",
							"IBLOCK_TYPE" => "",
							"LEVEL_LAST" => "Y",
							"LIST_URL" => "",
							"MAX_FILE_SIZE" => "0",
							"MAX_LEVELS" => "100000",
							"MAX_USER_ENTRIES" => "100000",
							"PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
							"PROPERTY_CODES" => array("8","NAME","PREVIEW_TEXT", "13"),
							"PROPERTY_CODES_REQUIRED" => array(),
							"RESIZE_IMAGES" => "N",
							"SEF_MODE" => "N",
							"STATUS" => "ANY",
							"STATUS_NEW" => "N",
							"USER_MESSAGE_ADD" => "",
							"USER_MESSAGE_EDIT" => "",
							"USE_CAPTCHA" => "N"
						)
					);?>

<? /*<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
	<script type="text/javascript">
	$('#zayvka2').html('');
	var verifyCallback = function(response) {
	alert(response);
	};
	var widgetId1;
	var onloadCallback = function() {
	    widgetId1 = grecaptcha.render(document.getElementById('zayvka2'), {
	        'sitekey' : '<?=RE_SITE_KEY?>'
	    });
	};
	</script>	*/ ?>
	