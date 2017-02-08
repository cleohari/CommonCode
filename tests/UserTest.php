<?php
require_once('Autoload.php');
class UserTest extends PHPUnit_Framework_TestCase
{
    public function testUser()
    {
        $user = new \Auth\User();
        $this->assertFalse($user->isInGroupNamed('AAR'));
        $this->assertFalse($user->displayName);
        $this->assertFalse($user->givenName);
        $this->assertFalse($user->mail);
        $this->assertFalse($user->uid);
        $this->assertFalse($user->jpegPhoto);
        $this->assertFalse($user->mobile);
        $this->assertFalse($user->o);
        $this->assertFalse($user->title);
        $this->assertFalse($user->st);
        $this->assertFalse($user->l);
        $this->assertFalse($user->sn);
        $this->assertFalse($user->cn);
        $this->assertFalse($user->postalAddress);
        $this->assertFalse($user->postalCode);
        $this->assertFalse($user->c);
        $this->assertFalse($user->ou);
        $this->assertFalse($user->host);
        $this->assertFalse($user->getGroups());
        try{
            $user->addLoginProvider('google.com');
            $this->assertFalse(true);
        }
        catch(\Exception $e)
        {
            $this->assertFalse(false);
        }
        $this->assertFalse($user->canLoginWith('google.com'));
        $this->assertFalse($user->isProfileComplete());
        $this->assertFalse($user->validate_password('test'));
        $this->assertFalse($user->validate_reset_hash('test'));
        $this->assertFalse($user->getPasswordResetHash());

        try
        {
            $res = $user->change_pass('test', 'test');
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }

        try
        {
            $res = $user->change_pass('test', 'test', true);
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }

        try
        {
            $res = $user->change_pass(false, 'test');
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }

        $vcard = $user->getVcard();
        $this->assertEquals("BEGIN:VCARD\nVERSION:2.1\nN:;\nFN:\nORG: Austin Artistic Reconstruction\nTEL;TYPE=MOBILE,VOICE:\nEMAIL;TYPE=PREF,INTERNET:\nEND:VCARD\n", $vcard);

        $json = json_encode($user);
        $this->assertEquals('{"displayName":false,"givenName":false,"jpegPhoto":"","mail":false,"mobile":false,"uid":false,"o":false,"title":false,"titlenames":false,"st":false,"l":false,"sn":false,"cn":false,"postalAddress":false,"postalCode":false,"c":false,"ou":false,"host":false,"class":"Auth\\\\User"}', $json);

        $data = new \stdClass();
        $user->editUser($data);
        $this->assertFalse(false);

        try
        {
            $data = new \stdClass();
            $data->password = 'test';
            $data->oldpass = 'test';
            $user->editUser($data);
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }

        try
        {
            $data = new \stdClass();
            $data->password = 'test';
            $data->hash = 'test';
            $user->editUser($data);
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }

        $data = new \stdClass();
        $data->displayName = 'test';
        $data->givenName = 'test';
        $data->sn = 'test';
        $data->cn = 'test';
        $data->postalAddress = 'test';
        $data->l = 'test';
        $data->st = 'test';
        $data->postalCode = 'test';
        $data->c = 'test';
        $data->o = 'test';
        $data->title = 'test';
        $data->ou = 'test';
        $data->jpegPhoto = base64_encode('test');
        $data->mobile = 'test';
        $user->editUser($data);
        $this->assertFalse(false);

        try
        {
            $data = new \stdClass();
            $data->mail = 'test';
            $user->editUser($data);
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }

        try
        {
            $data = new \stdClass();
            $data->uid = 'test';
            $user->editUser($data);
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }
    }

    public function testLDAPUser()
    {
        $user = new \Auth\LDAPUser();
        try{
            $this->assertFalse($user->isInGroupNamed('AAR'));
        } catch(\Exception $e)
        {
            $this->assertFalse(false);
        }
        $this->assertFalse($user->displayName);
        $this->assertFalse($user->givenName);
        $this->assertFalse($user->mail);
        $this->assertFalse($user->uid);
        $this->assertFalse($user->jpegPhoto);
        $this->assertFalse($user->mobile);
        $this->assertEquals('Volunteer', $user->o);
        $this->assertFalse($user->title);
        $this->assertFalse($user->st);
        $this->assertFalse($user->l);
        $this->assertFalse($user->sn);
        $this->assertFalse($user->cn);
        $this->assertFalse($user->postalAddress);
        $this->assertFalse($user->postalCode);
        $this->assertFalse($user->c);
        $this->assertFalse($user->ou);
        $this->assertFalse($user->host);
        try{
            $this->assertFalse($user->getGroups());
        } catch(\Exception $e)
        {
            $this->assertFalse(false);
        }

        $this->assertTrue($user->setPass('test'));
        $user->displayName = 'test';
        $user->givenName = 'test';
        $user->sn = 'test';
        $user->mail = 'test@example.com';
        $user->jpegPhoto = base64_encode('test');
        $user->postalAddress = 'test';
        $user->postalCode = '123456';
        $user->c = 'US';
        $user->st = 'TX';
        $user->l = 'test';
        $user->phoneNumber = '1234567890';
        $user->title = 'test';
        $this->assertEquals($user->title, array('test'));
        $user->title = array('test', 'test2');
        $this->assertEquals($user->title, array('test', 'test2'));

        $user->ou = 'test';
        $this->assertEquals($user->ou, array('test'));
        $user->ou = array('test', 'test2');
        $this->assertEquals($user->ou, array('test', 'test2'));

        $this->assertEquals($user->displayName, 'test');
        $this->assertEquals($user->givenName, 'test');
        $this->assertEquals($user->mail, 'test@example.com');
        $this->assertEquals($user->jpegPhoto, base64_encode('test'));
        $this->assertEquals($user->phoneNumber, '1234567890');
        $this->assertEquals($user->st, 'TX');
        $this->assertEquals($user->l, 'test');
        $this->assertEquals($user->sn, 'test');
        $this->assertEquals($user->postalAddress, 'test');
        $this->assertEquals($user->postalCode, '123456');
        $this->assertEquals($user->c, 'US');
    }

    public function testSQLUser()
    {
        $user = new \Auth\SQLUser();
        try{
            $this->assertFalse($user->isInGroupNamed('AAR'));
        } catch(\Exception $e)
        {
            $this->assertFalse(false);
        }
        $this->assertFalse($user->displayName);
        $this->assertFalse($user->givenName);
        $this->assertFalse($user->mail);
        $this->assertFalse($user->uid);
        $this->assertFalse($user->jpegPhoto);
        $this->assertFalse($user->mobile);
        $this->assertFalse($user->o);
        $this->assertFalse($user->title);
        $this->assertFalse($user->st);
        $this->assertFalse($user->l);
        $this->assertFalse($user->sn);
        $this->assertFalse($user->cn);
        $this->assertFalse($user->postalAddress);
        $this->assertFalse($user->postalCode);
        $this->assertFalse($user->c);
        $this->assertFalse($user->ou);
        $this->assertFalse($user->host);
        $this->assertFalse($user->getGroups());

        $user = new \Auth\SQLUser(array('mail'=>'test@example.com'));
        $this->assertEquals('test@example.com', $user->mail);
    }

    public function testFlipsideAPIUser()
    {
        $user = new \Auth\FlipsideAPIUser();
        try{
            $this->assertFalse($user->isInGroupNamed('AAR'));
        } catch(\Exception $e)
        {
            $this->assertFalse(false);
        }
        $this->assertFalse($user->displayName);
        $this->assertFalse($user->givenName);
        $this->assertFalse($user->mail);
        $this->assertFalse($user->uid);
        $this->assertFalse($user->jpegPhoto);
        $this->assertFalse($user->mobile);
        $this->assertFalse($user->o);
        $this->assertFalse($user->title);
        $this->assertFalse($user->st);
        $this->assertFalse($user->l);
        $this->assertFalse($user->sn);
        $this->assertFalse($user->cn);
        $this->assertFalse($user->postaltAddress);
        $this->assertFalse($user->postalCode);
        $this->assertFalse($user->c);
        $this->assertFalse($user->ou);
        $this->assertFalse($user->host);
        $this->assertFalse($user->getGroups());
    }

    public function testPendingUser()
    {
        $user = new \Auth\PendingUser();
        $this->assertFalse($user->getHash());
        $this->assertFalse($user->getRegistrationTime());
        $this->assertFalse($user->isInGroupNamed('AAR'));
        $this->assertFalse($user->mail);
        $this->assertFalse($user->givenName);
        $this->assertFalse($user->sn);
        $this->assertFalse($user->getPassword());
        $this->assertFalse($user->host);

        $user->addLoginProvider('example.com');
        $this->assertEquals(array('example.com'), $user->host);

        $user->addLoginProvider('example2.com');
        $this->assertEquals(array('example.com', 'example2.com'), $user->host);

        $user->uid = 'test';
        $this->assertEquals('test', $user->uid);

        $user->mail = 'test@example.com';
        $this->assertEquals('test@example.com', $user->mail);

        $user->givenName = 'test';
        $this->assertEquals('test', $user->givenName);

        $user->sn = 'test';
        $this->assertEquals('test', $user->sn);

        $this->assertEquals('{"hash":false,"mail":"test@example.com","uid":"test","class":"Auth\\\\PendingUser"}', json_encode($user));
    }

    public function testSQLPendingUser()
    {
        $user = new \Auth\SQLPendingUser(array('hash'=>false, 'time'=>'now', 'data'=>'{"mail":"test@example.com", "uid":"test", "password":"test"}'));
        $this->assertFalse($user->getHash());
        $this->assertNotFalse($user->getRegistrationTime());
        $this->assertFalse($user->isInGroupNamed('AAR'));
        $this->assertEquals('test@example.com', $user->mail);
        $this->assertEquals('test', $user->uid);
        $this->assertEquals('test', $user->getPassword());
        $time = $user->getRegistrationTime()->format(\DateTime::RFC822);
        $this->assertEquals('{"hash":false,"mail":"test@example.com","uid":"test","time":"'.$time.'","class":"Auth\\\\SQLPendingUser"}', json_encode($user));
        $this->assertEquals('test', $user['uid']);

        $user = new \Auth\SQLPendingUser(array('hash'=>'1234', 'time'=>'now', 'data'=>'{"mail":["test@example.com"], "uid":["test"], "password":["test"]}'));
        $this->assertEquals('1234', $user->getHash());
        $this->assertNotFalse($user->getRegistrationTime());
        $this->assertFalse($user->isInGroupNamed('AAR'));
        $this->assertEquals('test@example.com', $user->mail);
        $this->assertEquals('test', $user->uid);
        $this->assertEquals('test', $user->getPassword());
        $time = $user->getRegistrationTime()->format(\DateTime::RFC822);
        $this->assertEquals('{"hash":"1234","mail":"test@example.com","uid":"test","time":"'.$time.'","class":"Auth\\\\SQLPendingUser"}', json_encode($user));
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
