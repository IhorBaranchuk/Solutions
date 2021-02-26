<?php


namespace Helpers;


use Bitrix\Iblock\SectionTable;

class UserHelper
{
    public static function findByXMLID($userXmlid)
    {
        $by = 'ID';
        $order = 'asc';

        $res = \CUser::GetList(
            $by,
            $order,
            array('XML_ID' => $userXmlid),
            array('FIELDS' => array('XML_ID', 'ID'))
        );

        if ($row = $res->Fetch()){
            return intval($row['ID']);
        }

        return null;
    }

    public static function findById($userId)
    {
        $connection = \Bitrix\Main\Application::getConnection();

        $userId = (int)$userId;
        $query = "
            SELECT ID 
            FROM b_user 
            WHERE ID ='{$userId}'  AND ACTIVE='Y' ORDER BY ID";

        $res = $connection->query($query);
        if ($row = $res->Fetch()) {
            return (int)$row['ID'];
        }

        return null;
    }

    /**
     * @param $userId
     * @return array
     */
    public static  function getUserGroupNames($userId)
    {
        $result = array();

        $res = \Bitrix\Main\UserGroupTable::getList(
            array(
                'filter' => array('USER_ID' => intval($userId)),
                'select' => array('GROUP_ID','GROUP_NAME'=>'GROUP.NAME')
            )
        );

        while ($row = $res->fetch()) {
            $result[$row['GROUP_ID']] = $row['GROUP_NAME'];
        }

        return $result;
    }

    static function getUserInfo($userId,$fields = [])
    {
        $userData = \CUser::getById(intval($userId))->Fetch();

        if(!empty($fields) && is_array($fields)) {
            return array_intersect_key($userData,array_flip($fields));
        }

        return $userData;
    }

    public static function getField($userId,$field)
    {
        return \CUser::getById((int)$userId)->Fetch()[$field];
    }

    public static function getUserFullName(int $userId)
    {
        $userData = \CUser::getById($userId)->Fetch();

        return $userData['NAME'] . ($userData['LAST_NAME'] ? ' ' . $userData['LAST_NAME'] : '');
    }
}