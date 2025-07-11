<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;

// Подключаем базовый класс, хотя он больше не нужен для вызова методов,
// но оставляем для ясности и возможных проверок `is_subclass_of`
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/classes/general/prop_html.php';

class CIBlockPropertyHtmlText extends CIBlockPropertyHTML
{
    const VALUE_TYPE_TEXT = 'TEXT';
    const VALUE_TYPE_HTML = 'HTML';

    /**
     * @return array
     */
    public static function GetDescription()
    {

        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'CustomHtmlText',
            'DESCRIPTION' => 'Пользовательское свойство HTML/текст',
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
            'GetPublicEditHTML' => [__CLASS__, 'GetPublicEditHTML'],
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
            'GetLength' => [__CLASS__, 'GetLength'],
            'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
            'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
            'GetUIFilterProperty' => [__CLASS__, 'GetUIFilterProperty'],
        ];
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if (!is_array($value["VALUE"]))
            $value = static::ConvertFromDB($arProperty, $value);
        $ar = $value['VALUE'] ?? '';
        if (!empty($ar) && is_array($ar))
        {
            if (isset($strHTMLControlName['MODE']) && $strHTMLControlName['MODE'] == 'CSV_EXPORT')
                return '['.$ar["TYPE"].']'.$ar["TEXT"];
            elseif (isset($strHTMLControlName['MODE']) && $strHTMLControlName['MODE'] == 'SIMPLE_TEXT')
                return ($ar["TYPE"] === self::VALUE_TYPE_HTML ? strip_tags($ar["TEXT"]) : $ar["TEXT"]);
            else
                return FormatText($ar["TEXT"], $ar["TYPE"]);
        }

        return '';
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if(!is_array($value["VALUE"]))
            $value = static::ConvertFromDB($arProperty, $value);
        $ar = $value["VALUE"];
        if($ar)
            return htmlspecialcharsEx($ar["TYPE"].":".$ar["TEXT"]);
        else
            return "&nbsp;";
    }

    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        if (!Loader::includeModule("fileman"))
            return Loc::getMessage("IBLOCK_PROP_HTML_NOFILEMAN_ERROR");

        if (!is_array($value["VALUE"]))
            $value = static::ConvertFromDB($arProperty, $value);

        if (isset($strHTMLControlName["MODE"]) && $strHTMLControlName["MODE"]=="SIMPLE")
        {
            return '<input type="hidden" name="'.$strHTMLControlName["VALUE"].'[TYPE]" value="html">'
                .'<textarea cols="60" rows="10" name="'.$strHTMLControlName["VALUE"].'[TEXT]" style="width:100%">'.htmlspecialcharsEx($value["VALUE"]["TEXT"]).'</textarea>';
        }

        $id = preg_replace("/[^a-z0-9]/i", '', $strHTMLControlName['VALUE']);

        ob_start();
        echo '<input type="hidden" name="'.$strHTMLControlName["VALUE"].'[TYPE]" value="html">';
        $LHE = new CHTMLEditor;
        $LHE->Show(array(
            'name' => $strHTMLControlName["VALUE"].'[TEXT]',
            'id' => $id,
            'inputName' => $strHTMLControlName["VALUE"].'[TEXT]',
            'content' => $value['VALUE']['TEXT'] ?? '',
            'width' => '100%',
            'minBodyWidth' => 350,
            'normalBodyWidth' => 555,
            'height' => '200',
            'bAllowPhp' => false,
            'limitPhpAccess' => false,
            'autoResize' => true,
            'autoResizeOffset' => 40,
            'useFileDialogs' => false,
            'saveOnBlur' => true,
            'showTaskbars' => false,
            'showNodeNavi' => false,
            'askBeforeUnloadPage' => true,
            'bbCode' => false,
            'actionUrl' => '/bitrix/tools/html_editor_action.php',
            'siteId' => SITE_ID,
            'setFocusAfterShow' => false,
            'controlsMap' => array(
                array('id' => 'Bold', 'compact' => true, 'sort' => 80),
                array('id' => 'Italic', 'compact' => true, 'sort' => 90),
                array('id' => 'Underline', 'compact' => true, 'sort' => 100),
                array('id' => 'Strikeout', 'compact' => true, 'sort' => 110),
                array('id' => 'RemoveFormat', 'compact' => true, 'sort' => 120),
                array('id' => 'Color', 'compact' => true, 'sort' => 130),
                array('id' => 'FontSelector', 'compact' => false, 'sort' => 135),
                array('id' => 'FontSize', 'compact' => false, 'sort' => 140),
                array('separator' => true, 'compact' => false, 'sort' => 145),
                array('id' => 'OrderedList', 'compact' => true, 'sort' => 150),
                array('id' => 'UnorderedList', 'compact' => true, 'sort' => 160),
                array('id' => 'AlignList', 'compact' => false, 'sort' => 190),
                array('separator' => true, 'compact' => false, 'sort' => 200),
                array('id' => 'InsertLink', 'compact' => true, 'sort' => 210),
                array('id' => 'InsertImage', 'compact' => false, 'sort' => 220),
                array('id' => 'InsertVideo', 'compact' => true, 'sort' => 230),
                array('id' => 'InsertTable', 'compact' => false, 'sort' => 250),
                array('separator' => true, 'compact' => false, 'sort' => 290),
                array('id' => 'Fullscreen', 'compact' => false, 'sort' => 310),
                array('id' => 'More', 'compact' => true, 'sort' => 400)
            ),
        ));
        $s = ob_get_contents();
        ob_end_clean();
        return  $s;
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        if (!is_array($arProperty))
        {
            $arProperty = [];
        }
        $arProperty['WITH_DESCRIPTION'] = ($arProperty['WITH_DESCRIPTION'] ?? 'N') === 'Y' ? 'Y' : 'N';

        if (!is_array($strHTMLControlName))
        {
            $strHTMLControlName = [];
        }

        $strHTMLControlName['VALUE'] ??= '';
        $strHTMLControlName['DESCRIPTION'] ??= '';
        if (!is_string($strHTMLControlName['DESCRIPTION']))
        {
            $strHTMLControlName['DESCRIPTION'] = '';
        }

        $strHTMLControlName["VALUE"] = htmlspecialcharsEx($strHTMLControlName["VALUE"]);
        if (!is_array($value["VALUE"]))
        {
            $value = static::ConvertFromDB($arProperty, $value);
        }
        $ar = $value["VALUE"] ?? self::getEmptyValue();

        if (mb_strtolower($ar["TYPE"]) != "text")
            $ar["TYPE"] = "html";
        else
            $ar["TYPE"] = "text";

        $settings = static::PrepareSettings($arProperty);

        ob_start();
        ?><table width="100%">
        <?

        if (
            isset($strHTMLControlName['MODE'])
            && $strHTMLControlName['MODE'] === 'FORM_FILL'
            && Main\Config\Option::get('iblock', 'use_htmledit') === "Y"
            && Loader::includeModule('fileman')
        ):
            ?>
            <?php
            if (
                $arProperty['WITH_DESCRIPTION'] === 'Y'
                && $strHTMLControlName['DESCRIPTION'] !== ''
            ):
                ?>
                <tr>
                    <td>
                        <p style="text-align: center"><b>Вопрос:</b></p>
                        <span title="<?echo Loc::getMessage("IBLOCK_PROP_HTML_DESCRIPTION_TITLE")?>"><input type="text" name="<?=$strHTMLControlName["DESCRIPTION"]?>" value="<?=$value["DESCRIPTION"]?>" style="width: 100%" ></span>
                    </td>
                </tr>
            <?endif;?>
            <tr>
            <td colspan="2" align="center">
                <p><b>Ответ:</b></p>
                <input type="hidden" name="<?=$strHTMLControlName["VALUE"]?>" value="">
                <?
                $text_name = preg_replace("/([^a-z0-9])/is", "_", $strHTMLControlName["VALUE"]."[TEXT]");
                $text_type = preg_replace("/([^a-z0-9])/is", "_", $strHTMLControlName["VALUE"]."[TYPE]");
                CFileMan::AddHTMLEditorFrame(
                    $text_name,
                    htmlspecialcharsBx($ar["TEXT"]),
                    $text_type,
                    mb_strtolower($ar["TYPE"]),
                    $settings['height'],
                    "N",
                    0,
                    "",
                    ""
                );
                ?>
            </td>
            </tr>
        <?else:?>
            <tr>
                <td align="right"><?echo Loc::getMessage("IBLOCK_DESC_TYPE")?></td>
                <td align="left">
                    <input type="radio" name="<?=$strHTMLControlName["VALUE"]?>[TYPE]" id="<?=$strHTMLControlName["VALUE"]?>[TYPE][TEXT]" value="text" <?if($ar["TYPE"]!="html")echo " checked"?>>
                    <label for="<?=$strHTMLControlName["VALUE"]?>[TYPE][TEXT]"><?echo Loc::getMessage("IBLOCK_DESC_TYPE_TEXT")?></label> /
                    <input type="radio" name="<?=$strHTMLControlName["VALUE"]?>[TYPE]" id="<?=$strHTMLControlName["VALUE"]?>[TYPE][HTML]" value="html"<?if($ar["TYPE"]=="html")echo " checked"?>>
                    <label for="<?=$strHTMLControlName["VALUE"]?>[TYPE][HTML]"><?echo Loc::getMessage("IBLOCK_DESC_TYPE_HTML")?></label>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center"><textarea cols="60" rows="10" name="<?=$strHTMLControlName["VALUE"]?>[TEXT]" style="width:100%"><?=htmlspecialcharsEx($ar["TEXT"])?></textarea></td>
            </tr>
        <?endif; ?>
        </table>
        <?
        $return = ob_get_contents();
        ob_end_clean();

        return  $return;
    }

    public static function ConvertToDB($arProperty, $value)
    {
        global $DB;

        $return = false;

        if (!is_array($value))
        {
            $value = static::getValueFromString($value, true);
        }
        elseif (isset($value['VALUE']) && !is_array($value['VALUE']))
        {
            $value['VALUE'] = static::getValueFromString($value['VALUE'], false);
        }
        $defaultValue = isset($value['DEFAULT_VALUE']) && $value['DEFAULT_VALUE'] === true;

        if(
            is_array($value)
            && array_key_exists('VALUE', $value)
        )
        {
            if (
                isset($value['VALUE']['TEXT'])
                && (trim($value['VALUE']['TEXT']) !== '' || $defaultValue)
            )
            {
                $value['VALUE'] = static::CheckArray($value['VALUE'], $defaultValue);
                if ($value['VALUE'] !== false)
                {
                    $return = [
                        'VALUE' => serialize($value['VALUE']),
                    ];
                }
            }
        }

        if (
            is_array($return)
            && isset($value['DESCRIPTION'])
        )
        {
            $return['DESCRIPTION'] = trim((string)$value['DESCRIPTION']);
        }

        return $return;
    }

    public static function ConvertFromDB($arProperty, $value)
    {
        $return = false;
        if (!is_array($value['VALUE']))
        {
            $value['VALUE'] = (string)$value['VALUE'];
            if ($value['VALUE'] !== '')
            {
                if (
                    CheckSerializedData($value['VALUE'])
                    && str_contains($value['VALUE'], 'TEXT')
                    && str_contains($value['VALUE'], 'TYPE')
                )
                {
                    $return = [
                        'VALUE' => unserialize($value['VALUE'], ['allowed_classes' => false]),
                    ];
                    if ($return['VALUE'] === false)
                    {
                        $return = [
                            'VALUE' => [
                                'TEXT' => $value['VALUE'],
                                'TYPE' => self::VALUE_TYPE_TEXT,
                            ]
                        ];
                    }
                }
                else
                {
                    $return = [
                        'VALUE' => [
                            'TEXT' => $value['VALUE'],
                            'TYPE' => self::VALUE_TYPE_TEXT,
                        ]
                    ];
                }
            }
            if (isset($value['DESCRIPTION']))
            {
                $value['DESCRIPTION'] = (string)$value['DESCRIPTION'];
                if ($value['DESCRIPTION'] !== '')
                {
                    if (!is_array($return))
                    {
                        $return = [
                            "VALUE" => null,
                        ];
                    }
                    $return["DESCRIPTION"] = trim($value["DESCRIPTION"]);
                }
            }
        }

        return $return;
    }

    /**
     * Check value.
     *
     * @param bool|array $arFields			Current value.
     * @param bool $defaultValue			Is default value.
     * @return array|bool
     */
    public static function CheckArray($arFields = false, $defaultValue = false)
    {
        $defaultValue = ($defaultValue === true);
        if (!is_array($arFields))
        {
            $return = false;
            if (is_string($arFields) && $arFields !== '' && CheckSerializedData($arFields))
            {
                $return = unserialize($arFields, ['allowed_classes' => false]);
            }
        }
        else
        {
            $return = $arFields;
        }

        if ($return)
        {
            if (array_key_exists('TEXT', $return) && ((trim((string)$return['TEXT']) !== '') || $defaultValue))
            {
                $return['TYPE'] = mb_strtoupper($return['TYPE']);
                if (($return['TYPE'] !== self::VALUE_TYPE_TEXT) && ($return['TYPE'] !== self::VALUE_TYPE_HTML))
                {
                    $return['TYPE'] = self::VALUE_TYPE_HTML;
                }
            }
            else
            {
                $return = false;
            }
        }

        return $return;
    }

    public static function GetLength($arProperty, $value)
    {
        if (is_array($value) && isset($value['VALUE']['TEXT']))
        {
            return mb_strlen(trim((string)$value['VALUE']['TEXT']));
        }
        else
        {
            return 0;
        }
    }

    public static function PrepareSettings($arProperty)
    {
        $height = 0;
        if (isset($arProperty['USER_TYPE_SETTINGS']['height']))
        {
            $height = (int)$arProperty['USER_TYPE_SETTINGS']['height'];
        }
        if ($height <= 0)
        {
            $height = 200;
        }

        return [
            'height' =>  $height,
        ];
    }

    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $arPropertyFields = [
            'HIDE' => [
                'ROW_COUNT',
                'COL_COUNT',
                'MULTIPLE',
            ],
        ];

        $settings = static::PrepareSettings($arProperty);

        $height = $settings['height'];

        return '
		<tr valign="top">
			<td>'.Loc::getMessage("IBLOCK_PROP_HTML_SETTING_HEIGHT").':</td>
			<td><input type="text" size="5" name="'.$strHTMLControlName["NAME"].'[height]" value="'.$height.'">px</td>
		</tr>
		';
    }

    /**
     * @param array $property
     * @param array $strHTMLControlName
     * @param array &$fields
     * @return void
     */
    public static function GetUIFilterProperty($property, $strHTMLControlName, &$fields)
    {
        $fields['type'] = 'string';
        $fields['operators'] = array(
            'default' => '%'
        );
        $fields['filterable'] = '?';
    }

    protected static function getValueFromString($value, $getFull = false)
    {
        $getFull = ($getFull === true);
        $valueType = self::VALUE_TYPE_HTML;
        $value = (string)$value;
        if ($value !== '')
        {
            $prefix = mb_strtoupper(mb_substr($value, 0, 6));
            $isText = $prefix == '[TEXT]';
            if ($prefix == '[HTML]' || $isText)
            {
                if ($isText)
                    $valueType = self::VALUE_TYPE_TEXT;
                $value = mb_substr($value, 6);
            }
        }
        if ($getFull)
        {
            return array(
                'VALUE' => array(
                    'TEXT' => $value,
                    'TYPE' => $valueType
                )
            );
        }
        else
        {
            return array(
                'TEXT' => $value,
                'TYPE' => $valueType
            );
        }
    }

    private static function getEmptyValue(): array
    {
        return [
            'TEXT' => '',
            'TYPE' => self::VALUE_TYPE_TEXT,
        ];
    }
}
