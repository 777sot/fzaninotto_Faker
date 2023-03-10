<?php

namespace Faker\Provider;

class Internet extends Base
{
    protected static $freeEmailDomain = ['gmail.com', 'yahoo.com', 'hotmail.com'];
    protected static $tld = ['com', 'com', 'com', 'com', 'com', 'com', 'biz', 'info', 'net', 'org'];

    protected static $userNameFormats = [
        '{{lastName}}.{{firstName}}',
        '{{firstName}}.{{lastName}}',
        '{{firstName}}##',
        '?{{lastName}}',
    ];
    protected static $emailFormats = [
        '{{userName}}@{{domainName}}',
        '{{userName}}@{{freeEmailDomain}}',
    ];
    protected static $urlFormats = [
        'http://www.{{domainName}}/',
        'http://{{domainName}}/',
        'http://www.{{domainName}}/{{slug}}',
        'http://www.{{domainName}}/{{slug}}',
        'https://www.{{domainName}}/{{slug}}',
        'http://www.{{domainName}}/{{slug}}.html',
        'http://{{domainName}}/{{slug}}',
        'http://{{domainName}}/{{slug}}',
        'http://{{domainName}}/{{slug}}.html',
        'https://{{domainName}}/{{slug}}.html',
    ];

    /**
     * @example 'jdoe@acme.biz'
     */
    public function email()
    {
        $format = static::randomElement(static::$emailFormats);

        return $this->generator->parse($format);
    }

    /**
     * @example 'jdoe@example.com'
     */
    final public function safeEmail()
    {
        return preg_replace('/\s/u', '', $this->userName() . '@' . static::safeEmailDomain());
    }

    /**
     * @example 'jdoe@gmail.com'
     */
    public function freeEmail()
    {
        return preg_replace('/\s/u', '', $this->userName() . '@' . static::freeEmailDomain());
    }

    /**
     * @example 'jdoe@dawson.com'
     */
    public function companyEmail()
    {
        return preg_replace('/\s/u', '', $this->userName() . '@' . $this->domainName());
    }

    /**
     * @example 'gmail.com'
     */
    public static function freeEmailDomain()
    {
        return static::randomElement(static::$freeEmailDomain);
    }

    /**
     * @example 'example.org'
     */
    final public static function safeEmailDomain()
    {
        $domains = [
            'example.com',
            'example.org',
            'example.net'
        ];

        return static::randomElement($domains);
    }
    /**
     * @example 'jdoe'
     */
    public function userName()
    {
        $format = static::randomElement(static::$userNameFormats);
        $username = static::bothify($this->generator->parse($format));

        $username = strtolower(static::transliterate($username));

        // check if transliterate() didn't support the language and removed all letters
        if (trim($username, '._') === '') {
            throw new \Exception('userName failed with the selected locale. Try a different locale or activate the "intl" PHP extension.');
        }

        // clean possible trailing dots from first/last names
        $username = str_replace('..', '.', $username);
        $username = rtrim($username, '.');

        return $username;
    }
    /**
     * @example 'fY4??HdZv68'
     */
    public function password($minLength = 6, $maxLength = 20)
    {
        $pattern = str_repeat('*', $this->numberBetween($minLength, $maxLength));

        return $this->asciify($pattern);
    }

    /**
     * @example 'tiramisu.com'
     */
    public function domainName()
    {
        return $this->domainWord() . '.' . $this->tld();
    }

    /**
     * @example 'faber'
     */
    public function domainWord()
    {
        $lastName = $this->generator->format('lastName');

        $lastName = strtolower(static::transliterate($lastName));

        // check if transliterate() didn't support the language and removed all letters
        if (trim($lastName, '._') === '') {
            throw new \Exception('domainWord failed with the selected locale. Try a different locale or activate the "intl" PHP extension.');
        }

        // clean possible trailing dot from last name
        $lastName = rtrim($lastName, '.');

        return $lastName;
    }

    /**
     * @example 'com'
     */
    public function tld()
    {
        return static::randomElement(static::$tld);
    }

    /**
     * @example 'http://www.runolfsdottir.com/'
     */
    public function url()
    {
        $format = static::randomElement(static::$urlFormats);

        return $this->generator->parse($format);
    }

    /**
     * @example 'aut-repellat-commodi-vel-itaque-nihil-id-saepe-nostrum'
     */
    public function slug($nbWords = 6, $variableNbWords = true)
    {
        if ($nbWords <= 0) {
            return '';
        }
        if ($variableNbWords) {
            $nbWords = (int) ($nbWords * mt_rand(60, 140) / 100) + 1;
        }
        $words = $this->generator->words($nbWords);

        return join('-', $words);
    }

    /**
     * @example '237.149.115.38'
     */
    public function ipv4()
    {
        return long2ip(mt_rand(0, 1) == 0 ? mt_rand(-2147483648, -2) : mt_rand(16777216, 2147483647));
    }

    /**
     * @example '35cd:186d:3e23:2986:ef9f:5b41:42a4:e6f1'
     */
    public function ipv6()
    {
        $res = [];
        for ($i = 0; $i < 8; $i++) {
            $res [] = dechex(mt_rand(0, "65535"));
        }

        return join(':', $res);
    }

    /**
     * @example '10.1.1.17'
     */
    public static function localIpv4()
    {
        if (static::numberBetween(0, 1) === 0) {
            // 10.x.x.x range
            return long2ip(static::numberBetween(ip2long("10.0.0.0"), ip2long("10.255.255.255")));
        }

        // 192.168.x.x range
        return long2ip(static::numberBetween(ip2long("192.168.0.0"), ip2long("192.168.255.255")));
    }

    /**
     * @example '32:F1:39:2F:D6:18'
     */
    public static function macAddress()
    {
        for ($i = 0; $i < 6; $i++) {
            $mac[] = sprintf('%02X', static::numberBetween(0, 0xff));
        }
        $mac = implode(':', $mac);

        return $mac;
    }

    protected static function transliterate($string)
    {
        if (0 === preg_match('/[^A-Za-z0-9_.]/', $string)) {
            return $string;
        }

        $transId = 'Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC;';
        if (class_exists('Transliterator', false) && $transliterator = \Transliterator::create($transId)) {
            $transString = $transliterator->transliterate($string);
        } else {
            $transString = static::toAscii($string);
        }

        return preg_replace('/[^A-Za-z0-9_.]/u', '', $transString);
    }

    protected static function toAscii($string)
    {
        static $arrayFrom, $arrayTo;

        if (empty($arrayFrom)) {
            $transliterationTable = [
                '??' => 'I', '??' => 'O', '??' => 'O', '??' => 'U', '??' => 'a', '??' => 'a',
                '??' => 'i', '??' => 'o', '??' => 'o', '??' => 'u', '??' => 's', '??' => 's',
                '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A',
                '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'C', '??' => 'C',
                '??' => 'C', '??' => 'C', '??' => 'C', '??' => 'D', '??' => 'D', '??' => 'E',
                '??' => 'E', '??' => 'E', '??' => 'E', '??' => 'E', '??' => 'E', '??' => 'E',
                '??' => 'E', '??' => 'E', '??' => 'G', '??' => 'G', '??' => 'G', '??' => 'G',
                '??' => 'H', '??' => 'H', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'I',
                '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'J',
                '??' => 'K', '??' => 'K', '??' => 'K', '??' => 'K', '??' => 'K', '??' => 'L',
                '??' => 'N', '??' => 'N', '??' => 'N', '??' => 'N', '??' => 'N', '??' => 'O',
                '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O',
                '??' => 'O', '??' => 'R', '??' => 'R', '??' => 'R', '??' => 'S', '??' => 'S',
                '??' => 'S', '??' => 'S', '??' => 'S', '??' => 'T', '??' => 'T', '??' => 'T',
                '??' => 'T', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'U',
                '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'W', '??' => 'Y',
                '??' => 'Y', '??' => 'Y', '??' => 'Z', '??' => 'Z', '??' => 'Z', '??' => 'a',
                '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a',
                '??' => 'a', '??' => 'c', '??' => 'c', '??' => 'c', '??' => 'c', '??' => 'c',
                '??' => 'd', '??' => 'd', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e',
                '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'f',
                '??' => 'g', '??' => 'g', '??' => 'g', '??' => 'g', '??' => 'h', '??' => 'h',
                '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i',
                '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'j', '??' => 'k', '??' => 'k',
                '??' => 'l', '??' => 'l', '??' => 'l', '??' => 'l', '??' => 'l', '??' => 'n',
                '??' => 'n', '??' => 'n', '??' => 'n', '??' => 'n', '??' => 'n', '??' => 'o',
                '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o',
                '??' => 'o', '??' => 'r', '??' => 'r', '??' => 'r', '??' => 's', '??' => 's',
                '??' => 't', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u',
                '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'w', '??' => 'y',
                '??' => 'y', '??' => 'y', '??' => 'z', '??' => 'z', '??' => 'z', '??' => 'A',
                '??' => 'A', '???' => 'A', '???' => 'A', '???' => 'A', '???' => 'A', '???' => 'A',
                '???' => 'A', '???' => 'A', '???' => 'A', '???' => 'A', '???' => 'A', '???' => 'A',
                '???' => 'A', '???' => 'A', '???' => 'A', '???' => 'A', '???' => 'A', '???' => 'A',
                '???' => 'A', '???' => 'A', '???' => 'A', '??' => 'B', '??' => 'G', '??' => 'D',
                '??' => 'E', '??' => 'E', '???' => 'E', '???' => 'E', '???' => 'E', '???' => 'E',
                '???' => 'E', '???' => 'E', '???' => 'E', '??' => 'Z', '??' => 'I', '??' => 'I',
                '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I',
                '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I',
                '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I',
                '??' => 'T', '??' => 'I', '??' => 'I', '??' => 'I', '???' => 'I', '???' => 'I',
                '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I', '???' => 'I',
                '???' => 'I', '???' => 'I', '???' => 'I', '??' => 'K', '??' => 'L', '??' => 'M',
                '??' => 'N', '??' => 'K', '??' => 'O', '??' => 'O', '???' => 'O', '???' => 'O',
                '???' => 'O', '???' => 'O', '???' => 'O', '???' => 'O', '???' => 'O', '??' => 'P',
                '??' => 'R', '???' => 'R', '??' => 'S', '??' => 'T', '??' => 'Y', '??' => 'Y',
                '??' => 'Y', '???' => 'Y', '???' => 'Y', '???' => 'Y', '???' => 'Y', '???' => 'Y',
                '???' => 'Y', '???' => 'Y', '??' => 'F', '??' => 'X', '??' => 'P', '??' => 'O',
                '??' => 'O', '???' => 'O', '???' => 'O', '???' => 'O', '???' => 'O', '???' => 'O',
                '???' => 'O', '???' => 'O', '???' => 'O', '???' => 'O', '???' => 'O', '???' => 'O',
                '???' => 'O', '???' => 'O', '???' => 'O', '???' => 'O', '???' => 'O', '???' => 'O',
                '???' => 'O', '??' => 'a', '??' => 'a', '???' => 'a', '???' => 'a', '???' => 'a',
                '???' => 'a', '???' => 'a', '???' => 'a', '???' => 'a', '???' => 'a', '???' => 'a',
                '???' => 'a', '???' => 'a', '???' => 'a', '???' => 'a', '???' => 'a', '???' => 'a',
                '???' => 'a', '???' => 'a', '???' => 'a', '???' => 'a', '???' => 'a', '???' => 'a',
                '???' => 'a', '???' => 'a', '???' => 'a', '??' => 'b', '??' => 'g', '??' => 'd',
                '??' => 'e', '??' => 'e', '???' => 'e', '???' => 'e', '???' => 'e', '???' => 'e',
                '???' => 'e', '???' => 'e', '???' => 'e', '??' => 'z', '??' => 'i', '??' => 'i',
                '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i',
                '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i',
                '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i',
                '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '??' => 't', '??' => 'i',
                '??' => 'i', '??' => 'i', '??' => 'i', '???' => 'i', '???' => 'i', '???' => 'i',
                '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i',
                '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '???' => 'i', '??' => 'k',
                '??' => 'l', '??' => 'm', '??' => 'n', '??' => 'k', '??' => 'o', '??' => 'o',
                '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o',
                '???' => 'o', '??' => 'p', '??' => 'r', '???' => 'r', '???' => 'r', '??' => 's',
                '??' => 's', '??' => 't', '??' => 'y', '??' => 'y', '??' => 'y', '??' => 'y',
                '???' => 'y', '???' => 'y', '???' => 'y', '???' => 'y', '???' => 'y', '???' => 'y',
                '???' => 'y', '???' => 'y', '???' => 'y', '???' => 'y', '???' => 'y', '???' => 'y',
                '???' => 'y', '???' => 'y', '??' => 'f', '??' => 'x', '??' => 'p', '??' => 'o',
                '??' => 'o', '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o',
                '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o',
                '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o',
                '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o', '???' => 'o', '??' => 'A',
                '??' => 'B', '??' => 'V', '??' => 'G', '??' => 'D', '??' => 'E', '??' => 'E',
                '??' => 'Z', '??' => 'Z', '??' => 'I', '??' => 'I', '??' => 'K', '??' => 'L',
                '??' => 'M', '??' => 'N', '??' => 'O', '??' => 'P', '??' => 'R', '??' => 'S',
                '??' => 'T', '??' => 'U', '??' => 'F', '??' => 'K', '??' => 'T', '??' => 'C',
                '??' => 'S', '??' => 'S', '??' => 'Y', '??' => 'E', '??' => 'Y', '??' => 'Y',
                '??' => 'A', '??' => 'B', '??' => 'V', '??' => 'G', '??' => 'D', '??' => 'E',
                '??' => 'E', '??' => 'Z', '??' => 'Z', '??' => 'I', '??' => 'I', '??' => 'K',
                '??' => 'L', '??' => 'M', '??' => 'N', '??' => 'O', '??' => 'P', '??' => 'R',
                '??' => 'S', '??' => 'T', '??' => 'U', '??' => 'F', '??' => 'K', '??' => 'T',
                '??' => 'C', '??' => 'S', '??' => 'S', '??' => 'Y', '??' => 'E', '??' => 'Y',
                '??' => 'Y', '??' => 'd', '??' => 'D', '??' => 't', '??' => 'T', '???' => 'a',
                '???' => 'b', '???' => 'g', '???' => 'd', '???' => 'e', '???' => 'v', '???' => 'z',
                '???' => 't', '???' => 'i', '???' => 'k', '???' => 'l', '???' => 'm', '???' => 'n',
                '???' => 'o', '???' => 'p', '???' => 'z', '???' => 'r', '???' => 's', '???' => 't',
                '???' => 'u', '???' => 'p', '???' => 'k', '???' => 'g', '???' => 'q', '???' => 's',
                '???' => 'c', '???' => 't', '???' => 'd', '???' => 't', '???' => 'c', '???' => 'k',
                '???' => 'j', '???' => 'h', '??' => 't', '??' => "'", '??' => '', '???' => 'h',
                '???' => "'", '???' => "'", '???' => 'u', '/' => '', '???' => 'e', '???' => 'a',
                '???' => 'i', '???' => 'a', '???' => 'e', '???' => 'i', '???' => 'o', '???' => 'e',
                '??' => 'o', '???' => 'a', '???' => 'a', '??' => 'u', '???' => 'a', '???' => 'a',
                '???' => 'd', '???' => 'H', '???' => 'D', '??' => 's', '??' => 't', '???' => 'o',
                '???' => 'a', '??' => 's', "'" => '', '????' => 'u', '??' => 'a', '??' => 'b',
                '??' => 'g', '??' => 'd', '??' => 'e', '??' => 'z', '??' => 'e', '??' => 'y',
                '??' => 't', '??' => 'zh', '??' => 'i', '??' => 'l', '??' => 'kh', '??' => 'ts',
                '??' => 'k', '??' => 'h', '??' => 'dz', '??' => 'gh', '??' => 'ch', '??' => 'm',
                '??' => 'y', '??' => 'n', '??' => 'sh', '??' => 'o', '??' => 'ch', '??' => 'p',
                '??' => 'j', '??' => 'r', '??' => 's', '??' => 'v', '??' => 't', '??' => 'r',
                '??' => 'ts', '??' => 'p', '??' => 'q', '??' => 'ev', '??' => 'o', '??' => 'f',
            ];
            $arrayFrom = array_keys($transliterationTable);
            $arrayTo = array_values($transliterationTable);
        }

        return str_replace($arrayFrom, $arrayTo, $string);
    }
}
