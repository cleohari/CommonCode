<?php
class SyntaxTest extends PHPUnit\Framework\TestCase
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
                if($rc !== 0)
                {
                    $output = print_r($output, false);
                }
                else
                {
                    $output = '';
                }
                $this->assertEquals(0, $rc, $output);
            }
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
