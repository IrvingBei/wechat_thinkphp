<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: src/ApiCore/Testing/mocks.proto

namespace Google\ApiCore\Testing;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>google.apicore.testing.MockRequestBody</code>
 */
class MockRequestBody extends \Google\Protobuf\Internal\Message
{
    /**
     * The value of the uninterpreted option, in whatever type the tokenizer
     * identified it as during parsing. Exactly one of these should be set.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     */
    private $name = '';
    /**
     * Generated from protobuf field <code>uint64 number = 2;</code>
     */
    private $number = 0;
    /**
     * Generated from protobuf field <code>repeated string repeated_field = 3;</code>
     */
    private $repeated_field;
    /**
     * Generated from protobuf field <code>.google.apicore.testing.MockRequestBody nested_message = 4;</code>
     */
    private $nested_message = null;

    public function __construct() {
        \GPBMetadata\ApiCore\Testing\Mocks::initOnce();
        parent::__construct();
    }

    /**
     * The value of the uninterpreted option, in whatever type the tokenizer
     * identified it as during parsing. Exactly one of these should be set.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The value of the uninterpreted option, in whatever type the tokenizer
     * identified it as during parsing. Exactly one of these should be set.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>uint64 number = 2;</code>
     * @return int|string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Generated from protobuf field <code>uint64 number = 2;</code>
     * @param int|string $var
     * @return $this
     */
    public function setNumber($var)
    {
        GPBUtil::checkUint64($var);
        $this->number = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>repeated string repeated_field = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getRepeatedField()
    {
        return $this->repeated_field;
    }

    /**
     * Generated from protobuf field <code>repeated string repeated_field = 3;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setRepeatedField($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->repeated_field = $arr;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.google.apicore.testing.MockRequestBody nested_message = 4;</code>
     * @return \Google\ApiCore\Testing\MockRequestBody
     */
    public function getNestedMessage()
    {
        return $this->nested_message;
    }

    /**
     * Generated from protobuf field <code>.google.apicore.testing.MockRequestBody nested_message = 4;</code>
     * @param \Google\ApiCore\Testing\MockRequestBody $var
     * @return $this
     */
    public function setNestedMessage($var)
    {
        GPBUtil::checkMessage($var, \Google\ApiCore\Testing\MockRequestBody::class);
        $this->nested_message = $var;

        return $this;
    }

}

