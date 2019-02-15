<?php # -*- coding: utf-8 -*-

namespace PHPSTORM_META {

    override(new \WCPayPalPlus\Service\Container,
        map([
            '' => '@',
        ])
    );

    override(\WCPayPalPlus\Service\Container::get(0),
        map([
            '' => '@',
        ])
    );

    override(\WCPayPalPlus\Service\Container::offsetGet(0),
        map([
            '' => '@',
        ])
    );

    override(\WCPayPalPlus\resolve(0),
        map([
            null => \WCPayPalPlus\Service\Container::class,
            '' => '@',
        ])
    );

    override(\Mockery::mock(0),
        map([
            '' => '@',
        ])
    );
}
