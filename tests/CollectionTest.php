<?php

namespace Overtrue\Pinyin\Tests;

use JsonException;
use Overtrue\Pinyin\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * 测试构造函数
     */
    public function test_constructor()
    {
        $collection = new Collection;
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals([], $collection->all());

        $items = ['a', 'b', 'c'];
        $collection = new Collection($items);
        $this->assertEquals($items, $collection->all());
    }

    /**
     * 测试all方法
     */
    public function test_all()
    {
        $items = ['你好', '世界'];
        $collection = new Collection($items);
        $this->assertEquals($items, $collection->all());
    }

    /**
     * 测试toArray方法
     */
    public function test_to_array()
    {
        $items = ['你好', '世界'];
        $collection = new Collection($items);
        $this->assertEquals($items, $collection->toArray());
        $this->assertEquals($collection->all(), $collection->toArray());
    }

    /**
     * 测试join方法
     */
    public function test_join()
    {
        $items = ['你好', '世界'];
        $collection = new Collection($items);

        // 默认分隔符
        $this->assertEquals('你好 世界', $collection->join());

        // 自定义分隔符
        $this->assertEquals('你好-世界', $collection->join('-'));
        $this->assertEquals('你好_世界', $collection->join('_'));
        $this->assertEquals('你好世界', $collection->join(''));
    }

    /**
     * 测试join方法处理数组项
     */
    public function test_join_with_array_items()
    {
        $items = ['你好', ['zhong', 'guo'], '世界'];
        $collection = new Collection($items);

        $this->assertEquals('你好 [zhong, guo] 世界', $collection->join());
        $this->assertEquals('你好-[zhong, guo]-世界', $collection->join('-'));
    }

    /**
     * 测试map方法
     */
    public function test_map()
    {
        $items = ['你好', '世界'];
        $collection = new Collection($items);

        $mapped = $collection->map(function ($item) {
            return strtoupper($item);
        });

        $this->assertInstanceOf(Collection::class, $mapped);
        $this->assertNotSame($collection, $mapped); // 应该是新实例
        $this->assertEquals(['你好', '世界'], $collection->all()); // 原集合不变
        $this->assertEquals(['你好', '世界'], $mapped->all()); // 中文字符串转大写不变
    }

    /**
     * 测试map方法处理复杂数据
     */
    public function test_map_with_complex_data()
    {
        $items = [1, 2, 3];
        $collection = new Collection($items);

        $mapped = $collection->map(function ($item) {
            return $item * 2;
        });

        $this->assertEquals([2, 4, 6], $mapped->all());
    }

    /**
     * 测试toJson方法
     */
    public function test_to_json()
    {
        $items = ['你好', '世界'];
        $collection = new Collection($items);

        $json = $collection->toJson();
        $this->assertEquals('["\u4f60\u597d","\u4e16\u754c"]', $json);

        // 测试JSON选项
        $jsonPretty = $collection->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString('你好', $jsonPretty);
        $this->assertStringContainsString('世界', $jsonPretty);
    }

    /**
     * 测试toJson方法处理复杂数据
     */
    public function test_to_json_with_complex_data()
    {
        $items = ['你好', ['zhong', 'guo'], 123];
        $collection = new Collection($items);

        $json = $collection->toJson();
        $decoded = json_decode($json, true);
        $this->assertEquals($items, $decoded);
    }

    /**
     * 测试__toString方法
     */
    public function test_to_string()
    {
        $items = ['你好', '世界'];
        $collection = new Collection($items);

        $this->assertEquals('你好 世界', (string) $collection);
        $this->assertEquals($collection->join(), (string) $collection);
    }

    /**
     * 测试ArrayAccess接口 - offsetExists
     */
    public function test_offset_exists()
    {
        $items = ['你好', '世界'];
        $collection = new Collection($items);

        $this->assertTrue(isset($collection[0]));
        $this->assertTrue(isset($collection[1]));
        $this->assertFalse(isset($collection[2]));
        $this->assertFalse(isset($collection['invalid']));
    }

    /**
     * 测试ArrayAccess接口 - offsetGet
     */
    public function test_offset_get()
    {
        $items = ['你好', '世界'];
        $collection = new Collection($items);

        $this->assertEquals('你好', $collection[0]);
        $this->assertEquals('世界', $collection[1]);
        $this->assertNull($collection[2]);
        $this->assertNull($collection['invalid']);
    }

    /**
     * 测试ArrayAccess接口 - offsetSet
     */
    public function test_offset_set()
    {
        $collection = new Collection;

        // 设置指定索引
        $collection[0] = '你好';
        $collection[1] = '世界';
        $this->assertEquals(['你好', '世界'], $collection->all());

        // 追加元素（null索引）
        $collection[] = '中国';
        $this->assertEquals(['你好', '世界', '中国'], $collection->all());

        // 修改现有元素
        $collection[1] = '地球';
        $this->assertEquals(['你好', '地球', '中国'], $collection->all());
    }

    /**
     * 测试ArrayAccess接口 - offsetUnset
     */
    public function test_offset_unset()
    {
        $items = ['你好', '世界', '中国'];
        $collection = new Collection($items);

        unset($collection[1]);
        $this->assertEquals([0 => '你好', 2 => '中国'], $collection->all());
        $this->assertFalse(isset($collection[1]));

        unset($collection[0]);
        $this->assertEquals([2 => '中国'], $collection->all());
    }

    /**
     * 测试JsonSerializable接口
     */
    public function test_json_serialize()
    {
        $items = ['你好', '世界'];
        $collection = new Collection($items);

        $serialized = $collection->jsonSerialize();
        $this->assertEquals($items, $serialized);

        // 测试json_encode直接使用
        $json = json_encode($collection);
        $this->assertEquals('["\u4f60\u597d","\u4e16\u754c"]', $json);
    }

    /**
     * 测试空集合
     */
    public function test_empty_collection()
    {
        $collection = new Collection;

        $this->assertEquals([], $collection->all());
        $this->assertEquals('', $collection->join());
        $this->assertEquals('', (string) $collection);
        $this->assertEquals('[]', $collection->toJson());
        $this->assertFalse(isset($collection[0]));
        $this->assertNull($collection[0]);
    }

    /**
     * 测试链式调用
     */
    public function test_method_chaining()
    {
        $items = [1, 2, 3, 4, 5];
        $collection = new Collection($items);

        $result = $collection
            ->map(function ($item) {
                return $item * 2;
            })
            ->map(function ($item) {
                return $item + 1;
            });

        $this->assertEquals([3, 5, 7, 9, 11], $result->all());
        $this->assertEquals('3 5 7 9 11', $result->join());
    }

    /**
     * 测试复杂数据结构
     */
    public function test_complex_data_structures()
    {
        $items = [
            'name' => '张三',
            'pinyin' => ['zhang', 'san'],
            'age' => 25,
            'hobbies' => ['读书', '游泳'],
        ];
        $collection = new Collection($items);

        $this->assertEquals('张三', $collection['name']);
        $this->assertEquals(['zhang', 'san'], $collection['pinyin']);
        $this->assertEquals(25, $collection['age']);
        $this->assertEquals(['读书', '游泳'], $collection['hobbies']);

        // 测试JSON序列化
        $json = $collection->toJson();
        $decoded = json_decode($json, true);
        $this->assertEquals($items, $decoded);
    }

    /**
     * 测试Unicode字符处理
     */
    public function test_unicode_handling()
    {
        $items = ['你好', '世界', '🌍', '测试'];
        $collection = new Collection($items);

        $this->assertEquals('你好 世界 🌍 测试', $collection->join());
        $this->assertEquals('你好-世界-🌍-测试', $collection->join('-'));

        $json = $collection->toJson();
        $decoded = json_decode($json, true);
        $this->assertEquals($items, $decoded);
    }

    /**
     * 测试性能 - 大量数据
     */
    public function test_large_dataset_performance()
    {
        $items = range(1, 1000);
        $collection = new Collection($items);

        $start = microtime(true);
        $result = $collection->map(function ($item) {
            return $item * 2;
        });
        $time = microtime(true) - $start;

        $this->assertLessThan(0.1, $time, 'Map operation should be fast');
        $this->assertEquals(2000, $result[999]); // 最后一个元素应该是 1000 * 2
    }

    public function test_to_json_throws_on_invalid_utf8(): void
    {
        $this->expectException(JsonException::class);

        (new Collection(["\xFF"]))->toJson();
    }
}
