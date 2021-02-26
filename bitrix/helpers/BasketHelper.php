<?php

namespace Helpers;

use Bitrix\Main\Loader;

class BasketHelper
{
    /**
     * Считает сумму корзины для пользователя
     * учитывает ценовые группы, в которых пользователь
     * состоит
     *
     * @param $userId
     * @param $basket \Bitrix\Sale\BasketBase $basket
     * @return float
     */
    static function calculateBasketTotalForUser($basket, $userId)
    {
        $basketTotal = 0;

        if ($basket)
        {
            /**@var \Bitrix\Sale\BasketItem $item */
            foreach ($basket->getBasketItems() as $item)
            {

                $minimalPrice = CatalogHelper::getProductOptimalPriceForUser($item->getProductId(), $userId);

                $discountRatio = $item->getDiscountPrice() / $item->getBasePrice();

                $basketTotal += $minimalPrice['PRICE'] * (1 - $discountRatio) * $item->getQuantity();
            }
        }
        return round($basketTotal, 2);
    }

    static function checkRests($basket, $storeCode)
    {
        Loader::includeModule('catalog');
        /** @var $basket \Bitrix\Sale\BasketBase $basket */

        $result = [];

        /** @var $item \Bitrix\Sale\BasketItem $basket */
        foreach ($basket as $item)
        {
            $basketQuantity = $item->getQuantity();
            $catalogQuantity = CatalogHelper::getProductRests($item->getProductId(), $storeCode);

            if ($catalogQuantity < $basketQuantity)
            {
                $result[$item->getId()] = [
                    'product_id' => $item->getProductId(),
                    'product_name' => $item->getField('NAME'),
                    'needed_quantity' => $basketQuantity,
                    'available_quantity' => $catalogQuantity,
                ];
            }
        }

        return $result;
    }
}