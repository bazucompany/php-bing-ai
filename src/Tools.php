<?php

namespace MaximeRenou\BingAI;

class Tools
{
    public static $debug = false;

    public static $useProxy = false;

    private static $proxy = null;

    public static function setProxy($ip, $port, $username, $password)
    {
        self::$useProxy = true;

        self::$proxy = [
            'ip'       => $ip,
            'port'     => $port,
            'username' => $username,
            'password' => $password,
        ];
    }
    public static function generateUUID()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function debug($message, $data = null)
    {
        if (self::$debug)
            echo "[DEBUG] $message\n";
    }

    public static function request($url, $headers = [], $data = null, $return_request = false)
    {
        $request = curl_init();

        if (self::$useProxy && !is_null(self::$proxy)) {
            // Set proxy options
            curl_setopt($request, CURLOPT_PROXY, self::$proxy['ip']);      // Proxy address
            curl_setopt($request, CURLOPT_PROXYPORT, self::$proxy['port']);         // Proxy port
            curl_setopt($request, CURLOPT_PROXYUSERPWD, self::$proxy['username'] . ':'.self::$proxy['password']); // Proxy username:password
            curl_setopt($request, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);  // Use HTTP proxy
        }

        // Bypass SSL certificate verification
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);  // 0 to not check names
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);  // 0 to not check certificate

        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);

        if (! is_null($data)) {
            curl_setopt($request, CURLOPT_POST, 1);
            curl_setopt($request, CURLOPT_POSTFIELDS, $data);
        }

        $data = curl_exec($request);

        $url = curl_getinfo($request, CURLINFO_EFFECTIVE_URL);
        curl_close($request);

        if ($return_request) {
            return [$data, $request, $url];
        }

        return $data;
    }
}
