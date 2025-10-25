<?php


namespace App;


class Request
{
    private array $params = [
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_FORBID_REUSE => true,
        CURLOPT_HEADER => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.2924.87 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
            'Connection: keep-alive',
            'Cache-Control: max-age=0',
            'Upgrade-Insecure-Requests: 1'
        ],
    ];

    public function __construct(array $params = [])
    {
        if (!empty($params)) {
            $this->params = $params;
        }
    }

    public static function init(array $params = []): self
    {
        return new self($params);
    }

    public function timeout(int $sec = 5): self
    {
        $this->params[CURLOPT_TIMEOUT] = $sec;
        return $this;
    }

    public function proxy(string $ip, int $port, int $type = CURLPROXY_HTTP): self
    {
        $this->params[CURLOPT_PROXY] = implode(':', [$ip,$port]);
        $this->params[CURLOPT_PROXYTYPE] = $type;
        return $this;
    }

    public function nobody(bool $bool): self
    {
        $this->params[CURLOPT_NOBODY] = $bool;
        return $this;
    }

    public function url(string $url): self
    {
        $this->params[CURLOPT_URL] = $url;
        return $this;
    }

    public function send(): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, $this->params);

        $time = microtime(true);

        $data = curl_exec($ch);

        $error = '';
        if (curl_errno($ch)) {
            $error = curl_error($ch);
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            'code' => $code,
            'error' => $error,
            'body' => $data,
            'time' => microtime(true) - $time
        ];
    }
}