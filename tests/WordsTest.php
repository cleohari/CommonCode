<?php
class WordsTest extends PHPUnit_Framework_TestCase
{
    public function testWords()
    {
        require('static.words.php');
        $count = count($words);
        $this->assertGreaterThanOrEqual(4096, $count); 
        for($i = 0; $i < 10; $i++)
        {
            $index = rand(0, $count-1);
            $this->assertGreaterThanOrEqual(1, strlen($words[$index]));
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
