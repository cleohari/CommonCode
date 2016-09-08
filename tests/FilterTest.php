<?php
require_once('Autoload.php');
class FilterTest extends PHPUnit_Framework_TestCase
{
    public function testOpParsing()
    {
        $filter = new \Data\Filter('a eq b');
        $clauses = $filter->getChildren();
        $this->assertCount(1, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '=');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, 'b');

        $filter = new \Data\Filter("a eq 'b'");
        $clauses = $filter->getChildren();
        $this->assertCount(1, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '=');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, "'b'");

        $filter = new \Data\Filter('a eq 1');
        $clauses = $filter->getChildren();
        $this->assertCount(1, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '=');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, '1');

        $filter = new \Data\Filter('a ne b');
        $clauses = $filter->getChildren();
        $this->assertCount(1, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '!=');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, 'b');

        $filter = new \Data\Filter('a gt b');
        $clauses = $filter->getChildren();
        $this->assertCount(1, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '>');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, 'b');

        $filter = new \Data\Filter('a ge b');
        $clauses = $filter->getChildren();
        $this->assertCount(1, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '>=');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, 'b');

        $filter = new \Data\Filter('a lt b');
        $clauses = $filter->getChildren();
        $this->assertCount(1, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '<');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, 'b');

        $filter = new \Data\Filter('a le b');
        $clauses = $filter->getChildren();
        $this->assertCount(1, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '<=');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, 'b');
    }

    public function testParenseParsing()
    {
        $filter = new \Data\Filter('(a eq b)');
        $clauses = $filter->getChildren();
        $this->assertCount(1, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '=');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, 'b');
    }

    public function testAndParsing()
    {
        $filter = new \Data\Filter('a eq b and c eq d');
        $clauses = $filter->getChildren();
        $this->assertCount(3, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '=');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, 'b');
        $clause = $clauses[1];
        $this->assertEquals($clause, 'and');
        $clause = $clauses[2];
        $this->assertEquals($clause->op, '=');
        $this->assertEquals($clause->var1, 'c');
        $this->assertEquals($clause->var2, 'd');
    }

    public function testOrParsing()
    {
        $filter = new \Data\Filter('a eq b or c eq d');
        $clauses = $filter->getChildren();
        $this->assertCount(3, $clauses);
        $clause = $clauses[0];
        $this->assertEquals($clause->op, '=');
        $this->assertEquals($clause->var1, 'a');
        $this->assertEquals($clause->var2, 'b');
        $clause = $clauses[1];
        $this->assertEquals($clause, 'or');
        $clause = $clauses[2];
        $this->assertEquals($clause->op, '=');
        $this->assertEquals($clause->var1, 'c');
        $this->assertEquals($clause->var2, 'd');
    }

    public function testSQL()
    {
        $filter = new \Data\Filter('a eq b');
        $sql = $filter->to_sql_string();
        $this->assertEquals($sql, 'a=b');
        $filter = new \Data\Filter('a eq b and c eq d');
        $sql = $filter->to_sql_string();
        $this->assertEquals($sql, 'a=b AND c=d');
        $filter = new \Data\Filter('a eq b or c eq d');
        $sql = $filter->to_sql_string();
        $this->assertEquals($sql, 'a=b OR c=d');
    }

    public function testLDAP()
    {
        $filter = new \Data\Filter('a eq b and c eq d or e eq f');
        try
        {
            $filter->to_ldap_string();
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }
        $filter = new \Data\Filter('a eq b or c eq d and e eq f');
        try
        {
            $filter->to_ldap_string();
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }
        $filter = new \Data\Filter('a eq b and c eq d');
        $this->assertEquals('(&(a=b)(c=d))', $filter->to_ldap_string());
        $filter = new \Data\Filter('a eq b or c eq d');
        $this->assertEquals('(|(a=b)(c=d))', $filter->to_ldap_string());
    }

    public function testMongo()
    {
        $filter = new \Data\Filter('a eq b');
        $this->assertEquals(array('a'=>'b'), $filter->to_mongo_filter());

        $filter = new \Data\Filter('a eq b and c eq d');
        $comp = array('$and' => array(array('a'=>'b'), array('c'=>'d')));
        $this->assertEquals($comp, $filter->to_mongo_filter());

        $filter = new \Data\Filter('a eq b or c eq d');
        $comp = array('$or' => array(array('a'=>'b'), array('c'=>'d')));
        $this->assertEquals($comp, $filter->to_mongo_filter());

        $filter = new \Data\Filter('a eq b and c eq d or e eq f');
        $comp = array('$or' => array(array('$and' => array(array('a'=>'b'), array('c'=>'d'))), array('e'=>'f')));
        $this->assertEquals($comp, $filter->to_mongo_filter());
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
