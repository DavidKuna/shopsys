<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\ShopBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Model\ShopInfo\ShopInfoSettingFacade;

class SettingValueShopInfoDataFixture extends AbstractReferenceFixture
{
    const SETTING_VALUES = [
        ShopInfoSettingFacade::SHOP_INFO_PHONE_NUMBER => '+420123456789',
        ShopInfoSettingFacade::SHOP_INFO_PHONE_HOURS => '(po-pá, 10:00 - 16:00)',
        ShopInfoSettingFacade::SHOP_INFO_EMAIL => 'no-reply@shopsys.cz',
    ];

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function load(ObjectManager $manager)
    {
        $setting = $this->get('shopsys.shop.component.setting');
        /* @var $setting \Shopsys\ShopBundle\Component\Setting\Setting */

        // Any previously executed data fixture using Setting (even transitively) would fill the Setting cache.
        // As EM identity map is cleared after each fixture we should clear the Setting cache before editing the values.
        $setting->clearCache();

        foreach (self::SETTING_VALUES as $key => $value) {
            $setting->setForDomain($key, $value, Domain::FIRST_DOMAIN_ID);
        }
    }
}
