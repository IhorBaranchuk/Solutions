<?php


namespace Helpers;


use Bitrix\Highloadblock\HighloadBlockTable as HL;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Loader;

class HighloadBlockHelper
{

    private static $storage;

    /**
     * @param $entityName
     * @param bool $silent
     * @return DataManager|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getHLEntityDataClass($entityName, $silent = false)
    {
        if (!Loader::includeModule('highloadblock'))
        {

            if ($silent === true)
            {
                return null;
            }

            throw new \Exception('Unable to load highloadblock module');
        }

        if (isset(self::$storage[$entityName]) && self::$storage[$entityName] instanceof DataManager)
        {
            return self::$storage[$entityName];
        }

        $result = HL::getList(
            array(
                'filter' => array('=NAME' => $entityName),
                'select' => array('ID'),
            )
        );

        if ($row = $result->fetch())
        {
            $hlblockId = $row["ID"];
            $hlblock = HL::getById($hlblockId)->fetch();
            $entity = HL::compileEntity($hlblock);
        }
        else
        {
            if ($silent === true)
            {
                return null;
            }

            throw new \Exception(printf('Highloadblock with entity name %s not found', $entityName));
        }

        self::$storage[$entityName] = $entity->getDataClass();
        return self::$storage[$entityName];
    }

    public static function getEntityId($entityName, $silent = false)
    {
        if (!Loader::includeModule('highloadblock'))
        {

            if ($silent === true)
            {
                return null;
            }

            throw new \Exception('Unable to load highloadblock module');
        }

        $result = HL::resolveHighloadblock($entityName)['ID'];

        if ($result === null && $silent !== true)
        {
            throw new \Exception(printf('Highloadblock with entity name %s not found', $entityName));
        }

        return $result;
    }

    static function findInHL(string $entityName, $filter)
    {
        $entity = self::getHLEntityDataClass($entityName);

        return $entity::getList([
            'filter' => $filter
        ])->fetchAll();
    }

}