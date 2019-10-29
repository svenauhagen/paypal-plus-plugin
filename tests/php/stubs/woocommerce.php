<?php

abstract class WC_Abstract_Order
{
    public function get_id() {}
}

class WC_Order extends WC_Abstract_Order
{
    public function get_order_key() {}
}
