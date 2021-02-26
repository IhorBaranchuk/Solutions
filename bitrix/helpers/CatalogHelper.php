<?php

namespace Helpers;

use Bitrix\Catalog\GroupTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

class CatalogHelper
{

    /*
     * получение наименьшей цены для пользователя, с учетом групп
     */
    public static function getProductOptimalPriceForUser($productId, $userId)
    {
        Loader::includeModule('catalog');

        $arPrice = \CCatalogProduct::GetOptimalPrice($productId, 1, \CUser::GetUserGroup($userId), 'N');
        $db = Application::getConnection();
        $priceTypeName = $db->query("SELECT NAME FROM b_catalog_group WHERE ID={$arPrice['RESULT_PRICE']['PRICE_TYPE_ID']}")->fetch()['NAME'];

        return [
            'PRICE' => $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'],
            'CURRENCY' => $arPrice['RESULT_PRICE']['CURRENCY'],
            'NAME' => $priceTypeName,
            'GROUP_ID' => $arPrice['RESULT_PRICE']['PRICE_TYPE_ID']
        ];
    }

    public static function getProductOptimalPriceListForUser($productIds, $userId)
    {
        Loader::includeModule('catalog');

        $products = [];
        foreach ($productIds as $productId)
        {
            $products[$productId] = [];
        }

        $res = \CCatalogProduct::GetOptimalPriceList($products, \CUser::GetUserGroup($userId));

        $priceList = [];

        foreach ($res as $productId => $prices)
        {
            $price = $prices[0];
            $priceList[$productId] = [
                'PRICE' => $price['RESULT_PRICE']['DISCOUNT_PRICE'],
                'CURRENCY' => $price['RESULT_PRICE']['CURRENCY'],
                'GROUP_ID' => $price['RESULT_PRICE']['PRICE_TYPE_ID']
            ];
        }

        $res = GroupTable::getList([
            'filter' => ['ID' => array_column($priceList, 'GROUP_ID')],
            'select' => ['ID', 'NAME']
        ]);

        $groups = [];
        while ($row = $res->fetch())
        {
            $groups[$row['ID']] = $row['NAME'];
        }

        foreach ($priceList as $productId => &$price)
        {
            $price['NAME'] = $groups[$price['GROUP_ID']];
        }
        unset($price);

        return $priceList;
    }

    public static function getProductRests($productId, $storeCode)
    {

        Loader::includeModule('catalog');

        $stores = [];
        $res = \CCatalogStore::GetList(
            array('ID' => 'ASC'),
            array(),
            false,
            false,
            array('ID', 'CODE')

        );

        while ($row = $res->fetch())
        {

            $stores[$row['CODE']] = $row['ID'];
        }

        $amount = (int)\CCatalogStoreProduct::GetList(
            array(),
            array('PRODUCT_ID' => (int)$productId, 'STORE_ID' => $stores[$storeCode]),
            false,
            false,
            array()
        )->Fetch()['AMOUNT'];

        return $amount;
    }


    public static function getProductAllowedPricesForUser($productId, $userId)
    {
        Loader::includeModule('catalog');

        $userGroups = \Cuser::GetUserGroup($userId);

        $priceGroups = array();
        $prideGroupsRes = \CCatalogGroup::GetList();
        while ($row = $prideGroupsRes->Fetch())
        {
            $priceGroups[$row['ID']] = $row;
        }

        $prideGroupsRes = \CCatalogGroup::GetGroupsList(array('GROUP_ID' => $userGroups, 'CAN_BUY' => 'Y'));
        $allowedPriceGroups = array();
        while ($row = $prideGroupsRes->Fetch())
        {
            $allowedPriceGroups[] = $row['CATALOG_GROUP_ID'];
        }

        $pricesRes = \CPrice::GetListEx(
            array('ID' => 'ASC'),
            array('PRODUCT_ID' => $productId, 'CATALOG_GROUP_ID' => $allowedPriceGroups),
            false,
            false,
            array()
        );
        $result = array();
        while ($row = $pricesRes->Fetch())
        {
            $result[] = [
                'PRICE' => $row['PRICE'],
                'CURRENCY' => $row['CURRENCY'],
                'NAME' => $priceGroups[$row['CATALOG_GROUP_ID']]['NAME'],
                'GROUP_ID' => $row['CATALOG_GROUP_ID']
            ];
        }

        return $result;

    }

    public static function getAllowedPriceTypesForUser($userId)
    {
        Loader::includeModule('catalog');

        $userGroups = \Cuser::GetUserGroup($userId);

        $priceGroups = array();
        $priceGroupsRes = \CCatalogGroup::GetList();
        while ($row = $priceGroupsRes->Fetch())
        {
            $priceGroups[$row['ID']] = $row;
        }

        $priceGroupsRes = \CCatalogGroup::GetGroupsList(array('GROUP_ID' => $userGroups, 'CAN_BUY' => 'Y'));

        $allowedPriceGroups = array();
        while ($row = $priceGroupsRes->Fetch())
        {
            $allowedPriceGroups[$priceGroups[$row['CATALOG_GROUP_ID']]['NAME']] = $priceGroups[$row['CATALOG_GROUP_ID']];
        }

        return $allowedPriceGroups;
    }

    public static function getPriceTypeIdByName(string $name)
    {
        $db = Application::getConnection();

        try
        {
            return $db->query("SELECT ID FROM b_catalog_group WHERE NAME={$db->getSqlHelper()->forSql($name)}")->fetch()['ID'];
        } catch (\Exception $e)
        {
            return null;
        }
    }

    public static function getStoreIdByCode(string $code)
    {
        return StoreTable::getRow(['filter' => ['CODE' => $code], 'select' => ['ID']])['ID'];
    }
}