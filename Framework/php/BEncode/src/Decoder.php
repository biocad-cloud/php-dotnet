<?php
/**
 * Rych Bencode
 *
 * Bencode serializer for PHP 5.3+.
 *
 * @package   Rych\Bencode
 * @copyright Copyright (c) 2014, Ryan Chouinard
 * @author    Ryan Chouinard <rchouinard@gmail.com>
 * @license   MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\Bencode;

/**
 * Bencode decoder class
 *
 * Decodes bencode encoded strings.
 */
class Decoder
{

    /**
     * The encoded source string
     *
     * @var string
     */
    private $source;

    /**
     * The length of the encoded source string
     *
     * @var integer
     */
    private $sourceLength;

    /**
     * The return type for the decoded value
     *
     * @var \Rych\Bencode::TYPE_ARRAY|\Rych\Bencode::TYPE_OBJECT
     */
    private $decodeType;

    /**
     * The current offset of the parser.
     *
     * @var integer
     */
    private $offset = 0;

    /**
     * Decoder constructor
     *
     * @param  string  $source The bencode encoded source.
     * @param  string  $decodeType Flag used to indicate whether the decoded
     *   value should be returned as an object or an array.
     * @return void
     */
    private function __construct($source, $decodeType)
    {
        $this->source = $source;
        $this->sourceLength = \strlen($this->source);
        $this->decodeType = \in_array($decodeType, array(\Rych\Bencode::TYPE_ARRAY, \Rych\Bencode::TYPE_OBJECT))
            ? $decodeType
            : \Rych\Bencode::TYPE_ARRAY;
    }

    /**
     * Decode a bencode encoded string
     *
     * @param  string $source The string to decode.
     * @param  string $decodeType Flag used to indicate whether the decoded
     *   value should be returned as an object or an array.
     * @return mixed   Returns the appropriate data type for the decoded data.
     * @throws \Exception
     */
    public static function decode($source, $decodeType = \Rych\Bencode::TYPE_ARRAY)
    {
        if (!\is_string($source)) {
            throw new \Exception("Argument expected to be a string; Got " . \gettype($source));
        }

        $decoder = new self($source, $decodeType);
        $decoded = $decoder->doDecode();

        if ($decoder->offset != $decoder->sourceLength) {
            throw new \Exception("Found multiple entities outside list or dict definitions");
        }

        return $decoded;
    }

    /**
     * Iterate over encoded entities in the source string and decode them
     *
     * @return mixed   Returns the decoded value.
     * @throws \Exception
     */
    private function doDecode()
    {
        switch ($this->getChar()) {

            case "i":
                ++$this->offset;
                return $this->decodeInteger();

            case "l":
                ++$this->offset;
                return $this->decodeList();

            case "d":
                ++$this->offset;
                return $this->decodeDict();

            default:
                if (ctype_digit($this->getChar())) {
                    return $this->decodeString();
                }

        }

        throw new \Exception("Unknown entity found at offset $this->offset");
    }

    /**
     * Decode a bencode encoded integer
     *
     * @return integer Returns the decoded integer.
     * @throws \Exception
     */
    private function decodeInteger()
    {
        $offsetOfE = strpos($this->source, "e", $this->offset);
        if (false === $offsetOfE) {
            throw new \Exception("Unterminated integer entity at offset $this->offset");
        }

        $currentOffset = $this->offset;
        if ("-" == $this->getChar($currentOffset)) {
            ++$currentOffset;
        }

        if ($offsetOfE === $currentOffset) {
            throw new \Exception("Empty integer entity at offset $this->offset");
        }

        while ($currentOffset < $offsetOfE) {
            if (!ctype_digit($this->getChar($currentOffset))) {
                throw new \Exception("Non-numeric character found in integer entity at offset $this->offset");
            }
            ++$currentOffset;
        }

        $value = substr($this->source, $this->offset, $offsetOfE - $this->offset);

        // One last check to make sure zero-padded integers don't slip by, as
        // they're not allowed per bencode specification.
        $absoluteValue = (string) abs($value);
        if (1 < strlen($absoluteValue) && "0" == $value[0]) {
            throw new \Exception("Illegal zero-padding found in integer entity at offset $this->offset");
        }

        $this->offset = $offsetOfE + 1;

        // The +0 auto-casts the chunk to either an integer or a float(in cases
        // where an integer would overrun the max limits of integer types)
        return $value + 0;
    }

    /**
     * Decode a bencode encoded string
     *
     * @return string  Returns the decoded string.
     * @throws \Exception
     */
    private function decodeString()
    {
        if ("0" === $this->getChar() && ":" != $this->getChar($this->offset + 1)) {
            throw new \Exception("Illegal zero-padding in string entity length declaration at offset $this->offset");
        }

        $offsetOfColon = strpos($this->source, ":", $this->offset);
        if (false === $offsetOfColon) {
            throw new \Exception("Unterminated string entity at offset $this->offset");
        }

        $contentLength = (int) substr($this->source, $this->offset, $offsetOfColon);
        if (($contentLength + $offsetOfColon + 1) > $this->sourceLength) {
            throw new \Exception("Unexpected end of string entity at offset $this->offset");
        }

        $value = substr($this->source, $offsetOfColon + 1, $contentLength);
        $this->offset = $offsetOfColon + $contentLength + 1;

        return $value;
    }

    /**
     * Decode a bencode encoded list
     *
     * @return array   Returns the decoded array.
     * @throws \Exception
     */
    private function decodeList()
    {
        $list = array();
        $terminated = false;
        $listOffset = $this->offset;

        while (false !== $this->getChar()) {
            if ("e" == $this->getChar()) {
                $terminated = true;
                break;
            }

            $list[] = $this->doDecode();
        }

        if (!$terminated && false === $this->getChar()) {
            throw new \Exception("Unterminated list definition at offset $listOffset");
        }

        $this->offset++;

        return $list;
    }

    /**
     * Decode a bencode encoded dictionary
     *
     * @return array   Returns the decoded array.
     * @throws \Exception
     */
    private function decodeDict()
    {
        $dict = array();
        $terminated = false;
        $dictOffset = $this->offset;

        while (false !== $this->getChar()) {
            if ("e" == $this->getChar()) {
                $terminated = true;
                break;
            }

            $keyOffset = $this->offset;
            if (!ctype_digit($this->getChar())) {
                throw new \Exception("Invalid dictionary key at offset $keyOffset");
            }

            $key = $this->decodeString();
            if (isset ($dict[$key])) {
                throw new \Exception("Duplicate dictionary key at offset $keyOffset");
            }

            $dict[$key] = $this->doDecode();
        }

        if (!$terminated && false === $this->getChar()) {
            throw new \Exception("Unterminated dictionary definition at offset $dictOffset");
        }

        $this->offset++;

        return $dict;
    }

    /**
     * Fetch the character at the specified source offset
     *
     * If offset is not provided, the current offset is used.
     *
     * @param  integer $offset The offset to retrieve from the source string.
     * @return string|false Returns the character found at the specified
     *   offset. If the specified offset is out of range, FALSE is returned.
     */
    private function getChar($offset = null)
    {
        if (null === $offset) {
            $offset = $this->offset;
        }

        if (empty ($this->source) || $this->offset >= $this->sourceLength) {
            return false;
        }

        return $this->source[$offset];
    }

}
