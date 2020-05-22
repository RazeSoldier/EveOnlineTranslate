<?php

use RazeSoldier\EveTranslate\ItemTranslate;
use PHPUnit\Framework\TestCase;

class ItemTranslateTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @throws \RazeSoldier\EveTranslate\TranslateException
     */
    public function testTranslate($expected, $itemName)
    {
        $this->assertEquals($expected, ItemTranslate::translate($itemName, 'zh'));
    }

    public function dataProvider()
    {
        return [
            ['缪宁级', 'Muninn'],
            ['渎圣级蓝图', 'Sacrilege Blueprint'],
            ['毒蜥级', 'Gila'],
        ];
    }
}
