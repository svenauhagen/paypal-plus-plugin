<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Api\ErrorData;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;

/**
 * Class ApiErrorDataExtractor
 * @package WCPayPalPlus\Api
 */
class ApiErrorDataExtractor
{
    /**
     * Extract Error Data by Json String
     *
     * @param $json
     * @return ErrorData
     */
    public function extractByJson($json)
    {
        assert(is_string($json));

        $data = $this->decodeJson($json);

        if ($data === null) {
            return new NullErrorData();
        }

        // I've not used the rest param because I don't know which data can be in the json and which not
        $name = isset($data->name) ? $data->name : '';
        $details = isset($data->details) ? $data->details : '';
        $message = isset($data->message) ? $data->message : '';
        $debugId = isset($data->debugId) ? $data->debugId : '';

        $details = $this->extractDetails($details);

        return new PayPalErrorData($name, $details, $message, $debugId);
    }

    /**
     * Extract Data By Exception
     *
     * @param PayPalConnectionException $exc
     * @return ErrorData
     */
    public function extractByException(PayPalConnectionException $exc)
    {
        $errorDataJson = $exc->getData();
        $errorData = $this->extractByJson($errorDataJson);

        return $errorData;
    }

    /**
     * Extract Details object by Json String
     *
     * @param $jsonDetails
     * @return array
     */
    private function extractDetails($jsonDetails)
    {
        $details = [];

        if (!$this->isJsonString($jsonDetails)) {
            return [];
        }

        $decodedDetails = $this->decodeJson($jsonDetails);

        foreach ($decodedDetails as $detail) {
            $details[] = new Detail($detail->field, $detail->issue);
        }

        return $details;
    }

    /**
     * @param $json
     * @return bool
     */
    private function isJsonString($json)
    {
        if (!is_string($json) || !trim($json)) {
            return false;
        }

        return (
            $json === '""'
            || $json === '[]'
            || $json === '{}'
            || $json[0] === '"'
            || $json[0] === '['
            || $json[0] === '{'
        );
    }

    /**
     * @param $json
     * @return array|mixed|object|null
     */
    private function decodeJson($json)
    {
        assert(is_string($json));

        if (!$this->isJsonString($json)) {
            return null;
        }

        $data = json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }
}
