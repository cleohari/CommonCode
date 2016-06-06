<?php
require_once('Autoload.php');
class UserTest extends PHPUnit_Framework_TestCase
{
    public function testUser()
    {
        $user = new \Auth\User();
        $this->assertFalse($user->isInGroupNamed('AAR'));
        $this->assertFalse($user->getDisplayName());
        $this->assertFalse($user->getGivenName());
        $this->assertFalse($user->getEmail());
        $this->assertFalse($user->getUid());
        $this->assertFalse($user->getPhoto());
        $this->assertFalse($user->getPhoneNumber());
        $this->assertFalse($user->getOrganization());
        $this->assertFalse($user->getTitles());
        $this->assertFalse($user->getState());
        $this->assertFalse($user->getCity());
        $this->assertFalse($user->getLastName());
        $this->assertFalse($user->getNickName());
        $this->assertFalse($user->getAddress());
        $this->assertFalse($user->getPostalCode());
        $this->assertFalse($user->getCountry());
        $this->assertFalse($user->getOrganizationUnits());
        $this->assertFalse($user->getLoginProviders());
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
        $this->assertFalse($user->setDisplayName('test'));
        $this->assertFalse($user->setGivenName('test'));
        $this->assertFalse($user->setEmail('test@test.com'));
        $this->assertFalse($user->setUid('test'));
        $this->assertFalse($user->setPhoto('test'));
        $this->assertFalse($user->setPhoneNumber('test'));
        $this->assertFalse($user->setOrganization('test'));
        $this->assertFalse($user->setTitles('test'));
        $this->assertFalse($user->setTitles(array('test', 'test2')));
        $this->assertFalse($user->setState('TX'));
        $this->assertFalse($user->setCity('test'));
        $this->assertFalse($user->setLastName('test'));
        $this->assertFalse($user->setNickName('test'));
        $this->assertFalse($user->setAddress('test'));
        $this->assertFalse($user->setPostalCode('test'));
        $this->assertFalse($user->setCountry('test'));
        $this->assertFalse($user->setOrganizationUnits('test'));
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
        $this->assertFalse($user->getDisplayName());
        $this->assertFalse($user->getGivenName());
        $this->assertFalse($user->getEmail());
        $this->assertFalse($user->getUid());
        $this->assertFalse($user->getPhoto());
        $this->assertFalse($user->getPhoneNumber());
        $this->assertEquals('Volunteer', $user->getOrganization());
        $this->assertFalse($user->getTitles());
        $this->assertFalse($user->getState());
        $this->assertFalse($user->getCity());
        $this->assertFalse($user->getLastName());
        $this->assertFalse($user->getNickName());
        $this->assertFalse($user->getAddress());
        $this->assertFalse($user->getPostalCode());
        $this->assertFalse($user->getCountry());
        $this->assertFalse($user->getOrganizationUnits());
        $this->assertFalse($user->getLoginProviders());
        try{
            $this->assertFalse($user->getGroups());
        } catch(\Exception $e)
        {
            $this->assertFalse(false);
        }

        $this->assertTrue($user->setPass('test'));
        $this->assertTrue($user->setDisplayName('test'));
        $this->assertTrue($user->setGivenName('test'));
        $this->assertTrue($user->setLastName('test'));
        $this->assertTrue($user->setEmail('test@example.com'));
        $this->assertTrue($user->setUid('test'));
        $this->assertTrue($user->setPhoto(base64_encode('test')));
        $this->assertTrue($user->setAddress('test'));
        $this->assertTrue($user->setPostalCode('123456'));
        $this->assertTrue($user->setCountry('US'));
        $this->assertTrue($user->setState('TX'));
        $this->assertTrue($user->setCity('test'));
        $this->assertTrue($user->setPhoneNumber('1234567890'));
        $this->assertTrue($user->setTitles('test'));
        $this->assertTrue($user->setTitles(array('test', 'test2')));
        $this->assertTrue($user->setOrganizationUnits('test'));
        $this->assertTrue($user->setOrganizationUnits(array('test', 'test2')));
        
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
        $this->assertFalse($user->getDisplayName());
        $this->assertFalse($user->getGivenName());
        $this->assertFalse($user->getEmail());
        $this->assertFalse($user->getUid());
        $this->assertFalse($user->getPhoto());
        $this->assertFalse($user->getPhoneNumber());
        $this->assertFalse($user->getOrganization());
        $this->assertFalse($user->getTitles());
        $this->assertFalse($user->getState());
        $this->assertFalse($user->getCity());
        $this->assertFalse($user->getLastName());
        $this->assertFalse($user->getNickName());
        $this->assertFalse($user->getAddress());
        $this->assertFalse($user->getPostalCode());
        $this->assertFalse($user->getCountry());
        $this->assertFalse($user->getOrganizationUnits());
        $this->assertFalse($user->getLoginProviders());
        $this->assertFalse($user->getGroups());

        $user = new \Auth\SQLUser(array('mail'=>'test@example.com'));
        $this->assertEquals('test@example.com', $user->getEmail());
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
        $this->assertFalse($user->getDisplayName());
        $this->assertFalse($user->getGivenName());
        $this->assertFalse($user->getEmail());
        $this->assertFalse($user->getUid());
        $this->assertFalse($user->getPhoto());
        $this->assertFalse($user->getPhoneNumber());
        $this->assertFalse($user->getOrganization());
        $this->assertFalse($user->getTitles());
        $this->assertFalse($user->getState());
        $this->assertFalse($user->getCity());
        $this->assertFalse($user->getLastName());
        $this->assertFalse($user->getNickName());
        $this->assertFalse($user->getAddress());
        $this->assertFalse($user->getPostalCode());
        $this->assertFalse($user->getCountry());
        $this->assertFalse($user->getOrganizationUnits());
        $this->assertFalse($user->getLoginProviders());
        $this->assertFalse($user->getGroups());
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
