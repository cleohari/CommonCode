<?php
class SyntaxTest extends PHPUnit_Framework_TestCase
{
    public function testIsValidSyntax()
    {
        $dirs = glob('*', GLOB_ONLYDIR);
        $dirs[] = '.';
        foreach($dirs as $dir)
        {
            $files = glob($dir.'/*.php');
            foreach($files as $file)
            {
                $output = false;
                $rc = 0;
                $res = exec('php -l '.$file, $output, $rc);
                $this->assertEquals(0, $rc, $output);
            }
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
