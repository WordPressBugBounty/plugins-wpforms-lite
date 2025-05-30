<?php

namespace WPForms\Vendor\Core\Tests;

use WPForms\Vendor\Core\Tests\Mocking\Other\Customer;
use WPForms\Vendor\Core\Tests\Mocking\Other\MockClass;
use WPForms\Vendor\Core\Tests\Mocking\Other\Order;
use WPForms\Vendor\Core\Tests\Mocking\Other\Person;
use WPForms\Vendor\Core\Tests\Mocking\Types\MockFileWrapper;
use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Core\Utils\DateHelper;
use WPForms\Vendor\Core\Utils\XmlDeserializer;
use WPForms\Vendor\Core\Utils\XmlSerializer;
use DateTime;
use Exception;
use InvalidArgumentException;
use WPForms\Vendor\PHPUnit\Framework\TestCase;
use stdClass;
class UtilsTest extends TestCase
{
    public function testXmlSerialization()
    {
        $xmlSerializer = new XMLSerializer(['formatOutput' => \true]);
        $res = $xmlSerializer->serialize('mockClass', new MockClass([34, 'asad']));
        $this->assertEquals("<?xml version=\"1.0\"?>\n" . "<mockClass attr=\"this is attribute\">\n" . "  <body>34</body>\n" . "  <body>asad</body>\n" . "  <new1>this is new</new1>\n" . "  <new2>\n" . "    <entry key=\"key1\">val1</entry>\n" . "    <entry key=\"key2\">val2</entry>\n" . "  </new2>\n" . "</mockClass>\n", $res);
        $xmlSerializer = new XMLSerializer([]);
        $res = $xmlSerializer->serialize('root', \true);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<root>true</root>\n", $res);
    }
    public function testXmlDeserialization()
    {
        $xmlDeSerializer = new XmlDeserializer();
        $input = "<?xml version=\"1.0\"?>\n<root>23</root>";
        $res = $xmlDeSerializer->deserialize($input, 'root', 'int');
        $this->assertEquals(23, $res);
        $res = $xmlDeSerializer->deserialize($input, 'root', '?int');
        $this->assertEquals(23, $res);
        $input = "<?xml version=\"1.0\"?>\n<root>true</root>";
        $res = $xmlDeSerializer->deserialize($input, 'root', 'bool');
        $this->assertEquals(\true, $res);
        $input = "<?xml version=\"1.0\"?>\n<root>false</root>";
        $res = $xmlDeSerializer->deserialize($input, 'root', 'bool');
        $this->assertEquals(\false, $res);
        $input = "<?xml version=\"1.0\"?>\n<root>2.3</root>";
        $res = $xmlDeSerializer->deserialize($input, 'root', 'float');
        $this->assertEquals(2.3, $res);
        $input = "<?xml version=\"1.0\"?>\n<root></root>";
        $res = $xmlDeSerializer->deserialize($input, 'abc', '?int');
        $this->assertNull($res);
        $res = $xmlDeSerializer->deserializeToArray($input, 'abc', 'item', '?float');
        $this->assertNull($res);
        $res = $xmlDeSerializer->deserializeToMap($input, 'abc', '?float');
        $this->assertNull($res);
    }
    public function testXmlDeserializationFailure1()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Required value not found at XML path "/root[1]" during deserialization.');
        $xmlDeSerializer = new XmlDeserializer();
        $input = "<?xml version=\"1.0\"?>\n<abc>23</abc>";
        $xmlDeSerializer->deserialize($input, 'root', 'int');
    }
    public function testXmlDeserializationFailureTypeBool()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Expected value of type "bool" but got value "2.3" at XML path ' . '"/root" during deserialization.');
        $xmlDeSerializer = new XmlDeserializer();
        $input = "<?xml version=\"1.0\"?>\n<root>2.3</root>";
        $xmlDeSerializer->deserialize($input, 'root', 'bool');
    }
    public function testXmlDeserializationFailureTypeInt()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Expected value of type "int" but got value ""asad"" at XML path ' . '"/root" during deserialization.');
        $xmlDeSerializer = new XmlDeserializer();
        $input = "<?xml version=\"1.0\"?>\n<root>\"asad\"</root>";
        $xmlDeSerializer->deserialize($input, 'root', 'int');
    }
    public function testXmlDeserializationFailureTypeFloat()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Expected value of type "float" but got value ""asad"" at XML path ' . '"/root" during deserialization.');
        $xmlDeSerializer = new XmlDeserializer();
        $input = "<?xml version=\"1.0\"?>\n<root>\"asad\"</root>";
        $xmlDeSerializer->deserialize($input, 'root', 'float');
    }
    public function testCoreHelperDeserialize()
    {
        $input = '{"key": "my value"}';
        $res = CoreHelper::deserialize($input);
        $this->assertIsArray($res);
        $this->assertEquals("my value", $res['key']);
    }
    public function testCoreHelperSerializeNull()
    {
        $this->assertEquals(null, CoreHelper::serialize(null));
    }
    public function testCoreHelperValidateUrl()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Url format.');
        CoreHelper::validateUrl('some/invalid/url/format');
    }
    public function testCoreHelperValidateUrlForwardSlashesFix()
    {
        $validated = CoreHelper::validateUrl('https://google.com');
        $this->assertEquals('https://google.com', $validated);
        $validated = CoreHelper::validateUrl('https://google.com/');
        $this->assertEquals('https://google.com', $validated);
        $validated = CoreHelper::validateUrl('https://google.com///apimatic///');
        $this->assertEquals('https://google.com/apimatic', $validated);
    }
    public function testCoreHelperCheckValueOrValuesInList()
    {
        $list = ['string', 'int', 'float', '1'];
        $this->assertTrue(CoreHelper::checkValueOrValuesInList(null, $list));
        $this->assertFalse(CoreHelper::checkValueOrValuesInList(1, $list));
        $this->assertTrue(CoreHelper::checkValueOrValuesInList('float', $list));
        $this->assertFalse(CoreHelper::checkValueOrValuesInList(['int', 'unknown'], $list));
        $this->assertTrue(CoreHelper::checkValueOrValuesInList(['float', 'int'], $list));
        $this->assertFalse(CoreHelper::checkValueOrValuesInList(['int', ['float', 'unknown']], $list));
        $this->assertTrue(CoreHelper::checkValueOrValuesInList(['float', ['int', 'string']], $list));
    }
    public function testCoreHelperClone()
    {
        $mockClass = new MockClass([]);
        $list = ['some string', 1, [\false, $mockClass]];
        $newList = $list;
        $this->assertEquals($list, $newList);
        $newList[2][1]->addAdditionalProperty('real', 214);
        $this->assertEquals($list, $newList);
        $clonedList = CoreHelper::clone($list);
        $this->assertEquals($list, $clonedList);
        $clonedList[2][1]->addAdditionalProperty('newValue', 12);
        $list[2][1]->addAdditionalProperty('real2', 14);
        $this->assertNotEquals($list, $clonedList);
        $this->assertEquals('["some string",1,[false,{"body":[],"real":214,"real2":14}]]', CoreHelper::serialize($list));
        $this->assertEquals('["some string",1,[false,{"body":[],"real":214,"newValue":12}]]', CoreHelper::serialize($clonedList));
    }
    public function testCoreHelperConvertToNullableString()
    {
        $this->assertEquals(null, CoreHelper::convertToNullableString(\false));
        $this->assertEquals("false", CoreHelper::convertToNullableString("false"));
    }
    public function testToStringWithInheritanceAndNesting()
    {
        $customer = new Customer();
        $customer->name = 'John Doe';
        $customer->email = 'john.doe@example.com';
        $customer->additionalProperties = ['age' => 21];
        $orderA = new Order();
        $orderA->orderId = 345;
        $orderB = new Order();
        $orderB->orderId = 567;
        $orderB->sender = $customer;
        $order = new Order();
        $order->orderId = 123;
        $order->similarOrders = [$orderA, $orderB];
        $order->sender = $customer;
        $order->total = 250.75;
        $order->delivered = \true;
        $expected = 'Order [orderId: 123, sender: Customer [email: john.doe@example.com, ' . 'name: John Doe, additionalProperties: [age: 21]], similarOrders: [Order [orderId: 345], ' . 'Order [orderId: 567, sender: Customer [email: john.doe@example.com, name: John Doe, ' . 'additionalProperties: [age: 21]]]], total: 250.75, delivered: true]';
        $this->assertEquals($expected, $order);
    }
    public function testToStringWithFileType()
    {
        $fileWrapper = MockFileWrapper::createFromPath('some\\path\\test.txt', 'text/plain', 'My Text');
        $person = new Person();
        $person->additionalProperties = ['file' => $fileWrapper];
        $expected = 'Person [additionalProperties: [file: MockFileWrapper [realFilePath: some\\path\\test.txt,' . ' mimeType: text/plain, filename: My Text]]]';
        $this->assertEquals($expected, $person);
    }
    public function testToStringWithStdClass()
    {
        $object = new stdClass();
        $object->name = "John";
        $object->age = 30;
        $person = new Person();
        $person->additionalProperties = ['stdClass' => $object, 'stdClassArray' => [$object, $object], 'stdClassMap' => ['keyA' => $object, 'keyB' => $object]];
        $expected = 'Person [additionalProperties: [stdClass: [name: John, age: 30], stdClassArray: ' . '[[name: John, age: 30], [name: John, age: 30]], stdClassMap: [keyA: [name: John, age: 30], ' . 'keyB: [name: John, age: 30]]]]';
        $this->assertEquals($expected, $person);
    }
    public function testToStringWithDateTime()
    {
        $date = DateHelper::fromSimpleDate('2024-01-17');
        $person = new Person();
        $person->additionalProperties = ['date' => $date, 'dateArray' => [$date, $date], 'dateMap' => ['keyA' => $date, 'keyB' => $date]];
        $expected = 'Person [additionalProperties: [date: 2024-01-17T00:00:00+00:00, dateArray: ' . '[2024-01-17T00:00:00+00:00, 2024-01-17T00:00:00+00:00], dateMap: [keyA: 2024-01-17T00:00:00+00:00,' . ' keyB: 2024-01-17T00:00:00+00:00]]]';
        $this->assertEquals($expected, $person);
    }
    public function testCoreHelperStringify()
    {
        $expectedStringNotation = 'Model [prop1: true, prop2: 90, prop3: my string 3, prop4: [23, 24.4]]';
        $this->assertEquals($expectedStringNotation, CoreHelper::stringify('Model', ['prop1' => \true, 'prop2' => 90, 'prop3' => 'my string 3', 'prop4' => [23, 24.4]]));
    }
    public function testCoreHelperStringifyWithProcessedProperties()
    {
        $expectedStringNotation = 'Model [prop1: true, prop2: 90, prop3: my string 1, ' . 'parentProp1: 1.1, parentProp2: some string, additionalProperties: ' . '[additional1: [A, B, false, true], additional2: other string, additional3: false]]';
        $processedProperties = CoreHelper::stringify('Parent', ['parentProp1' => 1.1, 'parentProp2' => 'some string', 'additionalProperties' => ['additional1' => ['A', 'B', \false, \true], 'additional2' => 'other string', 'additional3' => \false]]);
        $this->assertEquals($expectedStringNotation, CoreHelper::stringify('Model', ['prop1' => \true, 'prop2' => 90, 'prop3' => 'my string 1'], $processedProperties));
    }
    public function testIsNullOrEmpty()
    {
        $this->assertTrue(CoreHelper::isNullOrEmpty(0));
        $this->assertTrue(CoreHelper::isNullOrEmpty([]));
        $this->assertTrue(CoreHelper::isNullOrEmpty(''));
        $this->assertTrue(CoreHelper::isNullOrEmpty(null));
        $this->assertTrue(CoreHelper::isNullOrEmpty(\false));
        $this->assertFalse(CoreHelper::isNullOrEmpty('0'));
        $this->assertFalse(CoreHelper::isNullOrEmpty('some value'));
    }
    public function testOsInfo()
    {
        $expected = \PHP_OS_FAMILY . '-' . \php_uname('r');
        $this->assertEquals($expected, CoreHelper::getOsInfo());
    }
    public function testEmptyOsInfo()
    {
        $this->assertEquals('', CoreHelper::getOsInfo(''));
        $this->assertEquals('', CoreHelper::getOsInfo('Unknown'));
    }
    public function testDisabledOsVersion()
    {
        $this->assertEquals(\PHP_OS_FAMILY, CoreHelper::getOsInfo(\PHP_OS_FAMILY, 'unknown_func'));
    }
    public function testBasicAuthEncodedString()
    {
        $expected = 'Basic dXNlcm5hbWU6X1BhNTV3MHJk';
        $this->assertEquals($expected, CoreHelper::getBasicAuthEncodedString('username', '_Pa55w0rd'));
    }
    public function testEmptyBasicAuthEncodedString()
    {
        $this->assertEmpty(CoreHelper::getBasicAuthEncodedString('', '_Pa55w0rd'));
        $this->assertEmpty(CoreHelper::getBasicAuthEncodedString('username', ''));
        $this->assertEmpty(CoreHelper::getBasicAuthEncodedString('', ''));
    }
    public function testBearerAuthString()
    {
        $expected = 'Bearer my-token';
        $this->assertEquals($expected, CoreHelper::getBearerAuthString('my-token'));
    }
    public function testEmptyBearerAuthString()
    {
        $this->assertEmpty(CoreHelper::getBearerAuthString(''));
    }
    public function testFromSimpleDateFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect format.');
        DateHelper::fromSimpleDate('---');
    }
    public function testFromSimpleDateRequiredFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Date is null, empty or not in required format.');
        DateHelper::fromSimpleDateRequired(null);
    }
    public function testFromSimpleDateRequired()
    {
        $result = DateHelper::fromSimpleDateRequired('2021-10-01');
        $this->assertEquals('2021-10-01', DateHelper::toSimpleDate($result));
    }
    public function testFromRFC1123DateFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect format.');
        DateHelper::fromRfc1123DateTime('---');
    }
    public function testFromRFC1123DateRequiredFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime is null, empty or not in required format.');
        DateHelper::fromRfc1123DateTimeRequired(null);
    }
    public function testFromRFC1123DateRequired()
    {
        $result = DateHelper::fromRfc1123DateTimeRequired('Thu, 30 Sep 2021 00:00:00 GMT');
        $this->assertEquals('Thu, 30 Sep 2021 00:00:00 GMT', DateHelper::toRfc1123DateTime($result));
    }
    public function testFromRFC3339DateFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect format.');
        DateHelper::fromRfc3339DateTime('---');
    }
    public function testFromRFC3339DateRequiredFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime is null, empty or not in required format.');
        DateHelper::fromRfc3339DateTimeRequired(null);
    }
    public function testFromRFC3339DateRequired()
    {
        $result = DateHelper::fromRfc3339DateTimeRequired('2021-10-01T00:00:00+00:00');
        $this->assertEquals('2021-10-01T00:00:00+00:00', DateHelper::toRfc3339DateTime($result));
    }
    public function testFromUnixDateFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect format.');
        DateHelper::fromUnixTimestamp('-0-');
    }
    public function testFromUnixDateRequiredFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime is null, empty or not in required format.');
        DateHelper::fromUnixTimestampRequired(null);
    }
    public function testFromUnixDateRequired()
    {
        $result = DateHelper::fromUnixTimestampRequired('1633046400');
        $this->assertEquals(1633046400, DateHelper::toUnixTimestamp($result));
    }
    public function testFromSimpleDateString()
    {
        $this->assertNull(DateHelper::fromSimpleDateMapOfArray(null));
        $this->assertNull(DateHelper::fromSimpleDateArrayOfMap(null));
        $res = DateHelper::fromSimpleDateMapOfArray((object) ['A' => ['2021-10-01', '2021-09-30'], 'B' => [null, '2021-09-29'], 'C' => null]);
        $this->assertEquals(['A' => ['2021-10-01', '2021-09-30'], 'B' => [null, '2021-09-29'], 'C' => null], DateHelper::toSimpleDate2DArray($res));
        $res = DateHelper::fromSimpleDateArrayOfMap([(object) ['key1' => '2021-10-01', 'key2' => '2021-09-30'], (object) ['keyA' => null, 'keyB' => '2021-09-29'], null]);
        $this->assertEquals([['key1' => '2021-10-01', 'key2' => '2021-09-30'], ['keyA' => null, 'keyB' => '2021-09-29'], null], DateHelper::toSimpleDate2DArray($res));
    }
    public function testFromSimpleDateStringTimeInfo()
    {
        $date = DateHelper::fromSimpleDate('2024-01-16');
        $this->assertEquals(\strtotime('2024-01-16'), $date->getTimestamp());
    }
    public function testFromRFC1123DateString()
    {
        $this->assertNull(DateHelper::fromRfc1123DateTimeMapOfArray(null));
        $this->assertNull(DateHelper::fromRfc1123DateTimeArrayOfMap(null));
        $res = DateHelper::fromRfc1123DateTimeMapOfArray((object) ['A' => ['Fri, 01 Oct 2021 00:00:00 GMT', 'Thu, 30 Sep 2021 00:00:00 GMT'], 'B' => [null, 'Wed, 29 Sep 2021 00:00:00 GMT'], 'C' => null]);
        $this->assertEquals(['A' => [new DateTime("2021-09-31"), new DateTime("2021-09-30")], 'B' => [null, new DateTime("2021-09-29")], 'C' => null], $res);
        $res = DateHelper::fromRfc1123DateTimeArrayOfMap([(object) ['key1' => 'Fri, 01 Oct 2021 00:00:00 GMT', 'key2' => 'Thu, 30 Sep 2021 00:00:00 GMT'], (object) ['keyA' => null, 'keyB' => 'Wed, 29 Sep 2021 00:00:00 GMT'], null]);
        $this->assertEquals([['key1' => new DateTime("2021-09-31"), 'key2' => new DateTime("2021-09-30")], ['keyA' => null, 'keyB' => new DateTime("2021-09-29")], null], $res);
    }
    public function testAddingTimezoneInRFC3339DateString()
    {
        $date = DateHelper::fromRfc3339DateTime("2021-10-01T00:00:00");
        $this->assertEquals("2021-10-01T00:00:00+00:00", DateHelper::toRfc3339DateTime($date));
        $date = DateHelper::fromRfc3339DateTime("2021-10-01T00:00:00Z");
        $this->assertEquals("2021-10-01T00:00:00+00:00", DateHelper::toRfc3339DateTime($date));
        $date = DateHelper::fromRfc3339DateTime("2021-10-01T00:00:00+01:00");
        $this->assertEquals("2021-09-30T23:00:00+00:00", DateHelper::toRfc3339DateTime($date));
        $date = DateHelper::fromRfc3339DateTime("2021-10-01T00:00:00-01:00");
        $this->assertEquals("2021-10-01T01:00:00+00:00", DateHelper::toRfc3339DateTime($date));
    }
    public function testFromRFC3339DateString()
    {
        $this->assertNull(DateHelper::fromRfc3339DateTimeMapOfArray(null));
        $this->assertNull(DateHelper::fromRfc3339DateTimeArrayOfMap(null));
        $res = DateHelper::fromRfc3339DateTimeMapOfArray((object) ['A' => ['2021-10-01T00:00:00+00:00', '2021-09-30T00:00:00+00:00'], 'B' => [null, '2021-09-29T00:00:00+00:00'], 'C' => null]);
        $this->assertEquals(['A' => [new DateTime("2021-09-31"), new DateTime("2021-09-30")], 'B' => [null, new DateTime("2021-09-29")], 'C' => null], $res);
        $res = DateHelper::fromRfc3339DateTimeArrayOfMap([(object) ['key1' => '2021-10-01T00:00:00+00:00', 'key2' => '2021-09-30T00:00:00+00:00'], (object) ['keyA' => null, 'keyB' => '2021-09-29T00:00:00+00:00'], null]);
        $this->assertEquals([['key1' => new DateTime("2021-09-31"), 'key2' => new DateTime("2021-09-30")], ['keyA' => null, 'keyB' => new DateTime("2021-09-29")], null], $res);
        $this->assertEquals(new DateTime("2021-09-31"), DateHelper::fromRfc3339DateTime('2021-10-01T00:00:00'));
        $this->assertEquals(new DateTime("2021-09-31"), DateHelper::fromRfc3339DateTime('2021-10-01T00:00:00.000000'));
        $this->assertEquals(new DateTime("2021-09-31"), DateHelper::fromRfc3339DateTime('2021-10-01T00:00:00.000000000000'));
    }
    public function testFromUnixDateString()
    {
        $this->assertNull(DateHelper::fromUnixTimestampMapOfArray(null));
        $this->assertNull(DateHelper::fromUnixTimestampArrayOfMap(null));
        $res = DateHelper::fromUnixTimestampMapOfArray((object) ['A' => [1633046400, 1632960000], 'B' => [null, 1632873600], 'C' => null]);
        $this->assertEquals(['A' => [new DateTime("2021-09-31"), new DateTime("2021-09-30")], 'B' => [null, new DateTime("2021-09-29")], 'C' => null], $res);
        $res = DateHelper::fromUnixTimestampArrayOfMap([(object) ['key1' => 1633046400, 'key2' => 1632960000], (object) ['keyA' => null, 'keyB' => 1632873600], null]);
        $this->assertEquals([['key1' => new DateTime("2021-09-31"), 'key2' => new DateTime("2021-09-30")], ['keyA' => null, 'keyB' => new DateTime("2021-09-29")], null], $res);
    }
    public function testToDateStringConversions()
    {
        $input = ['A' => ['key1' => new DateTime("2021-09-31"), 'key2' => new DateTime("2021-09-30")], 'B' => ['keyA' => null, 'keyB' => new DateTime("2021-09-29")], 'C' => null];
        $this->assertNull(DateHelper::toSimpleDate2DArray(null));
        $res = DateHelper::toSimpleDate2DArray($input);
        $this->assertEquals(['A' => ['key1' => '2021-10-01', 'key2' => '2021-09-30'], 'B' => ['keyA' => null, 'keyB' => '2021-09-29'], 'C' => null], $res);
        $this->assertNull(DateHelper::toRfc1123DateTime2DArray(null));
        $res = DateHelper::toRfc1123DateTime2DArray($input);
        $this->assertEquals(['A' => ['key1' => 'Fri, 01 Oct 2021 00:00:00 GMT', 'key2' => 'Thu, 30 Sep 2021 00:00:00 GMT'], 'B' => ['keyA' => null, 'keyB' => 'Wed, 29 Sep 2021 00:00:00 GMT'], 'C' => null], $res);
        $this->assertNull(DateHelper::toRfc3339DateTime2DArray(null));
        $res = DateHelper::toRfc3339DateTime2DArray($input);
        $this->assertEquals(['A' => ['key1' => '2021-10-01T00:00:00+00:00', 'key2' => '2021-09-30T00:00:00+00:00'], 'B' => ['keyA' => null, 'keyB' => '2021-09-29T00:00:00+00:00'], 'C' => null], $res);
        $this->assertNull(DateHelper::toUnixTimestamp2DArray(null));
        $res = DateHelper::toUnixTimestamp2DArray($input);
        $this->assertEquals(['A' => ['key1' => 1633046400, 'key2' => 1632960000], 'B' => ['keyA' => null, 'keyB' => 1632873600], 'C' => null], $res);
    }
}
