<?php


namespace Helpers;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;

class IBlockHelper
{

    public static function getElementIblockId($elementId)
    {
        Loader::includeModule('iblock');

        return \CIBlockElement::GetIBlockByID($elementId);
    }

    public static function getElementXmlId(int $elementId)
    {
        return ElementTable::getRowById($elementId)['XML_ID'];
    }

    static function getElementTopSectionName($elementId)
    {
        $elementData = \CIBlockElement::GetByID($elementId)->Fetch();

        $res = \CIBlockSection::GetNavChain(
            $elementData['IBLOCK_ID'],
            $elementData['IBLOCK_SECTION_ID'],
            array('NAME')
        );

        return $res->Fetch()['NAME'];
    }

    static function getSectionIdByXmlId($sectionXmlid)
    {
        $res = \CIBlockSection::GetList(
            array('ID' => 'ASC'),
            array('XML_ID' => $sectionXmlid),
            false,
            array('ID'),
            false
        );

        return $res->Fetch()['ID'];
    }

    static function getSectionIdNavChainArray($sectionId)
    {
        $chain = null;
        $iblockId = \CIBlockSection::GetByID($sectionId)->Fetch()['IBLOCK_ID'];
        $res = \CIBlockSection::GetNavChain($iblockId, $sectionId);

        while ($row = $res->Fetch())
        {
            $chain[] = [
                'id' => $row['id'],
                'xml_id' => $row['xml_id'],
            ];
        }

        return $chain;
    }

    static function getSectionSubsections($sectionId)
    {
        $section = \CIBlockSection::GetByID($sectionId)->Fetch();
        if (!$section)
        {
            return null;
        }

        $res = \CIBlockSection::GetList(
            array('LEFT_MARGIN' => 'ASC'),
            array(
                'IBLOCK_ID' => $section['IBLOCK_ID'],
                '>LEFT_MARGIN' => $section['LEFT_MARGIN'],
                '<RIGHT_MARGIN' => $section['RIGHT_MARGIN'],
            ),
            false,
            array('ID', 'XML_ID'),
            false
        );

        $result = null;
        while ($row = $res->Fetch())
        {
            $result[] = [
                'id' => $row['ID'],
                'xml_id' => $row['XML_ID'],
            ];
        }
        return $result;
    }
}