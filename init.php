require_once __DIR__ . '/include/user_type/iblock_prop_htmltext.php';
AddEventHandler('iblock', 'OnIBlockPropertyBuildList', ['CIBlockPropertyHtmlText', 'getDescription']);
