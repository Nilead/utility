<?php

namespace Nilead\Utility;

use Symfony\Component\Inflector\Inflector;

/**
 * Class StringUtility
 *
 * @package Nilead\UtilityBundle\Utility
 */
class StringUtility
{

    /**
     * Convert to string that is delimited by "_" from camel case
     *
     * Usage:
     * <code>
     * $result = String::fromCamelCase($string);
     * </code>
     *
     * @param string $string
     *
     * @return string
     */
    public static function fromCamelCase($string)
    {
        return preg_replace_callback(
            '/(^|[a-z])([A-Z])/',
            function ($match) {
                return strtolower(strlen($match[1]) ? "$match[1]_$match[2]" : "$match[2]");
            },
            $string
        );
    }

    /**
     * Convert a string that is delimited by "_", "-" or space to camel case
     *
     * Usage:
     * <code>
     * $result = String::toCamelCase($string);
     * </code>
     *
     * @param string $string
     * @param bool $capitaliseFirstChar
     *
     * @return string
     */
    public static function toCamelCase($string, $capitaliseFirstChar = false)
    {
        $string = str_replace(['-', '_'], ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);

        if (!$capitaliseFirstChar) {
            return lcfirst($string);
        }

        return $string;
    }

    /**
     * Converts a word into the format for a Doctrine table name. Converts 'ModelName' to 'model_name'.
     * @param string $word
     * @return string
     */
    public static function tableize(string $word) : string
    {
        $tableized = preg_replace('~(?<=\\w)([A-Z])~u', '_$1', $word);

        if (null === $tableized) {
            throw new \RuntimeException(sprintf(
                'preg_replace returned null for value "%s"',
                $word
            ));
        }

        return mb_strtolower($tableized);
    }

    /**
     * @param string $string
     * @param string $replacement
     *
     * @return string
     * echo normal_chars('�lix----_�xel!?!?'); // Alix Axel
     * echo normal_chars('����������'); // aeiouAEIOU
     * echo normal_chars('������ܟ��'); // uyAEIOUYaA
     */
    public static function normalizeCharacters($string, $replacement = ' ')
    {
        $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
        $string = preg_replace(
            '~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
            '$1',
            $string
        );
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        $string = preg_replace(array('~[^0-9a-z]~i', '~[ -]+~'), $replacement, $string);

        return trim($string, ' -');
    }

    /**
     * Remove all characters except letters, numbers, and spaces.
     *
     * @param string $string
     *
     * @return string
     */
    public static function stripNonAlphaNumeric($string)
    {
        return preg_replace('/[^a-z0-9]/i', '', $string);
    }

    /**
     * Match string with filters
     *
     * @param string $subject
     *
     * @return mixed
     */
    public static function matchFilters($subject)
    {
        preg_match_all(
            "#\{\{[\s|\t]{0,}[\'|\"]{0,}([^\s\t\'\"]*)[\'|\"]{0,}[\s|\t]{0,}\|[\s|\t]{0,}(\w*)[\s|\t]{0,}\}\}#si",
            $subject,
            $matches,
            PREG_SET_ORDER
        );

        return $matches;
    }

    /**
     * Transform two or more spaces into just one space.
     *
     * @param string $string
     *
     * @return string
     */
    public static function stripExcessWhitespace($string)
    {
        return preg_replace('/  +/', ' ', $string);
    }

    /**
     * Calculate cache path
     *
     * @param string $name
     * @param string $cacheFolder
     * @param int $useSubfolder
     *
     * @return string
     */
    public static function calculatePath($name, $cacheFolder, $useSubfolder = 0)
    {
        if ($useSubfolder > 0) {
            $name = self::stripNonAlphaNumeric(strtolower($name));
            $path = substr($name, 0, $useSubfolder);
            $cacheFolder .= chunk_split($path, 1, '/');
        }

        $cacheFolder = rtrim($cacheFolder, '/');

        return $cacheFolder;
    }

    /**
     * Deep str_replace
     *
     * @param $search
     * @param $replace
     * @param $subject
     *
     * @return array|mixed
     */
    public static function strReplaceDeep($search, $replace, $subject)
    {
        if (\is_array($subject)) {
            foreach ($subject as &$oneSubject) {
                $oneSubject = self::strReplaceDeep($search, $replace, $oneSubject);
            }
            unset($oneSubject);

            return $subject;
        }

        return str_replace($search, $replace, $subject);
    }

    /**
     * A simple string replace that replace only the first occurrence, does not support array
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     *
     * @return string
     */
    public static function strReplaceFirst($search, $replace, $subject)
    {
        if (false !== ($pos = strpos($subject, $search))) {
            $beforeStr = substr($subject, 0, $pos);
            $afterStr = substr($subject, $pos + strlen($search));

            return $beforeStr . $replace . $afterStr;
        }

        return $subject;
    }

    /**
     * Generates a random string
     *
     * @param int $size
     *
     * @return string
     */
    public static function frand($size = 24)
    {
        $n = floor($size / 4);

        if (0 === $n) {
            $n = 1;
        }

        $params = ['N' . $n];

        while ($n > 0) {
            $params[] = mt_rand();
            $n--;
        }

        return base64_encode(pack(...$params));
    }


    /**
     * Generates a random string
     *
     * @param int $size
     * @param string $chars
     *
     * @return string
     */
    public static function rand($size = 10, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $string = '';

        $length = \strlen($chars) - 1;

        for ($i = 0; $i < $size; $i++) {
            $string .= $chars{mt_rand(0, $length)};
        }

        return $string;
    }

    /**
     * @param string $string
     * @param string $delimiter
     * @param bool $removeDuplicate
     *
     * @return mixed
     */
    public static function slugify($string, $delimiter = '-', $removeDuplicate = false)
    {
        $rule = 'NFD; [:Nonspacing Mark:] Remove; NFC';
        $transliterator = \Transliterator::create($rule);
        $string = $transliterator->transliterate($string);

        $result = preg_replace(
            '/[^a-z0-9]/',
            $delimiter,
            strtolower(trim(strip_tags($string)))
        );

        return $removeDuplicate ? preg_replace('/' . $delimiter . '+/', $delimiter, $result) : $result;
    }

    public static function convertViToEn($string)
    {
        $string = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $string);
        $string = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $string);
        $string = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $string);
        $string = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $string);
        $string = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $string);
        $string = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $string);
        $string = preg_replace("/(đ)/", 'd', $string);
        $string = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $string);
        $string = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $string);
        $string = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $string);
        $string = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $string);
        $string = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $string);
        $string = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $string);
        $string = preg_replace("/(Đ)/", 'D', $string);

        //$string = str_replace(" ", "-", str_replace("&*#39;","",$string));

        return $string;
    }

    /**
     * RemoveDuplicatedLinesByString
     * This function removes all duplicated lines of the given string.
     *
     * @param string|array $lines
     * @param bool $ignoreCase
     * @param string $newLine
     *
     * @return    string
     */
    public static function removeDuplicatedLines($lines, $ignoreCase = false, $newLine = PHP_EOL)
    {
        if (!is_array($lines)) {
            $lines = explode($newLine, $lines);
        }

        $lineArray = [];

        $duplicates = 0;

        // Go trough all lines of the given file
        for ($line = 0, $lineMax = \count($lines); $line < $lineMax; $line++) {

            // Trim whitespace for the current line
            $currentLine = trim($lines[$line]);

            // Skip empty lines
            if ('' === $currentLine) {
                continue;
            }

            // Use the line contents as array key
            $lineKey = $currentLine;

            if ($ignoreCase) {
                $lineKey = strtolower($lineKey);
            }

            // Check if the array key already exists,
            // if not add it otherwise increase the counter
            if (!isset($lineArray[$lineKey])) {
                $lineArray[$lineKey] = $currentLine;
            } else {
                $duplicates++;
            }
        }

        // Return how many lines got removed
        return implode($newLine, array_values($lineArray));
    }

    /**
     * @param $url
     * $url = 'http://search.google.com/dhasjkdas/sadsdds/sdda/sdads.html';
     * parse_url($url, PHP_URL_HOST); // will return 'search.google.com'
     *
     * $result = $extract->parse($url);
     * $result->getFullHost(); // will return 'search.google.com'
     * $result->getRegistrableDomain(); // will return 'google.com'
     *
     * @return mixed
     */
    public static function extractDomain($url)
    {
        $extract = new \LayerShifter\TLDExtract\Extract();

        $result = $extract->parse($url);

        return $result->getRegistrableDomain();
    }

    /**
     * Converts a word into the format for a Doctrine class name. Converts 'table_name' to 'TableName'.
     * @param string $word
     * @return string
     */
    public static function classify(string $word) : string
    {
        return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
    }

    /**
     * @param string $class
     *
     * @return string
     */
    public static function CSSClassify(string $class)
    {
        $class = preg_replace("/[^[:alnum:][:space:]]/u", ' ', $class);
        $class = strtolower(preg_replace('~(?<=\\w)([A-Z])~', '-$1', $class));
        $class = lcfirst(str_replace(' ', '-', $class));

        return $class;
    }

    /**
     * @param $value
     * @param bool $default
     * @return bool
     */
    public static function boolVal($value, $default = true)
    {
        if (\is_bool($value)) {
            return $value;
        }

        if (\is_string($value)) {
            return !('0' === $value || 'false' === strtolower($value));
        }

        if (is_numeric($value)) {
            return !($value <= 0);
        }

        return $default;
    }

    /**
     * @param string $plural
     * @return array|string
     */
    public static function singularize(string $plural) :string
    {
        $singular = Inflector::singularize($plural);

        if (\is_array($singular)) {
            $singular = end($singular);
        }

        return $singular;
    }

    /**
     * @param string $singular
     * @return array|string
     */
    public static function pluralize(string $singular) :string
    {
        $plural = Inflector::pluralize($singular);

        if (\is_array($plural)) {
            $plural = end($plural);
        }

        if (str_ends_with($plural, 'ss')) {
            $plural = $singular;
        } elseif ($plural === $singular) {
            $plural .= 's';
        }

        return $plural;
    }
}
