<?php

namespace Helpers;

class UserFieldHelper
{
    static function setEntityFields($entity, $entity_id, $ufData)
    {
        $userTypeManager = \Bitrix\Main\Application::getUserTypeManager();

        if (!$userTypeManager)
        {
            $userTypeManager = new \CUserTypeManager();
        }

        return $userTypeManager->Update(
            $entity,
            $entity_id,
            $ufData
        );
    }

    static function getEntityFields($entity, $entity_id)
    {
        $userTypeManager = \Bitrix\Main\Application::getUserTypeManager();

        if (!$userTypeManager)
        {
            $userTypeManager = new \CUserTypeManager();
        }

        return $userTypeManager->GetUserFields(
            $entity,
            $entity_id,
            'ru'
        );
    }

    static function getFieldValue($entity, $entity_id, $field)
    {
        $userTypeManager = \Bitrix\Main\Application::getUserTypeManager();

        if (!$userTypeManager)
        {
            $userTypeManager = new \CUserTypeManager();
        }

        return $userTypeManager->GetUserFields(
            $entity,
            $entity_id
        )[$field]['VALUE'];
    }

    static function getFieldEnum($fieldName, $entity, $order = array('ID' => 'XML_ID'))
    {
        $key = key($order);
        $val = current($order);

        if (empty($key) || !in_array($key, array('ID', 'XML_ID', 'VALUE')))
        {
            $key = 'ID';
        }
        if (empty($val) || !in_array($val, array('ID', 'XML_ID', 'VALUE')))
        {
            $val = 'XML_ID';
        }

        if (empty($order))
        {
            $order = array('ID' => 'XML_ID');
        }
        $userFieldId = \CUserTypeEntity::GetList(array(), array('ENTITY_ID' => $entity, 'FIELD_NAME' => $fieldName))->Fetch()['ID'];
        $values = array();
        $userFieldEnum = new \CUserFieldEnum;
        $res = $userFieldEnum->GetList(array(), array("USER_FIELD_ID" => $userFieldId));

        while ($row = $res->GetNext())
        {
            $values[$row[$key]] = $row[$val];
        }

        return $values;
    }

    static function getEntityFieldsCompact($entity, $entity_id, $order = ['FIELD_NAME' => 'VALUE'], $lang = 'ru')
    {
        $userTypeManager = \Bitrix\Main\Application::getUserTypeManager();

        if (!$userTypeManager)
        {
            $userTypeManager = new \CUserTypeManager();
        }

        $fields = $userTypeManager->GetUserFields(
            $entity,
            $entity_id,
            $lang
        );

        $key = key($order);
        $value = $order[$key];
        $result = [];
        foreach ($fields as $field)
        {
            $result[$field[$key]] = $field[$value];
        }

        return $result;
    }
}