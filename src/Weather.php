<?php

/*
 * This file is part of the byy/weather.
 *
 * (c) benny.peng <benny.peng@v2sh.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Byy\Weather;

use GuzzleHttp\Client;
use Byy\Weather\Exceptions\HttpException;
use Byy\Weather\Exceptions\InvalidArgumentException;

class Weather
{
    protected $key;

    protected $guzzleOptions = [];

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    public function getWeather($city,$type='base',$format='json')
    {$url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        // 对 $format 和 $type 参数进行检查，不在范围内跑出异常
        if (!\in_array(\strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: '.$format);
        }

        if (!\in_array(\strtolower($type), ['base', 'all'])) {
            throw new InvalidArgumentException('Invalid type value(base/all): '.$type);
        }

        // 封装 query 参数， 并对空值进行过滤
        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => $format,
            'extensions' => $type,
        ]);

        try {
            // 调用 getHttpClient 获取实例， 并调用该实例的 get 方法
            $response = $this->getHttpClient()
                ->get($url, ['query' => $query])
                ->getBody()
                ->getContents();

            // 返回值根据 $format 返回不同的格式
            return 'json' === $format ? \json_decode($response, true) : $response;
        } catch (\Exception $e) {
            // 当调用出现异常时捕获并抛出，消息为捕获到的异常消息
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
