<?php
namespace Auth;
if(!class_exists('Httpful\Request'))
{
    require(realpath(dirname(__FILE__)).'/../libs/httpful/bootstrap.php');
}

class FlipsideAPIUser extends User
{
    private $userData;
    private $groupData = null;

    public function __construct($data = false)
    {
        if($data !== false && !isset($data['extended']))
        {
            //Generic user object
            //TODO get from API
        }
        else
        {
            if(isset($data['extended']))
            {
                $this->userData = $data['extended'];
            }
        }
    }

    public function isInGroupNamed($name)
    {
        if($this->groupData === null)
        {
            $resp = \Httpful\Request::get('https://profiles.test.burningflipside.com/api/v1/users/me/groups')->authenticateWith($this->userData->uid, $this->userData->userPassword)->send();
            if($resp->hasErrors())
            {
                return false;
            }
            $this->groupData = $resp->body;
        }
        $count = count($this->groupData);
        for($i = 0; $i < $count; $i++)
        {
            if($this->groupData[$i]->cn === $name)
            {
                return true;
            }
        }
        return false;
    }

    public function getDisplayName()
    {
        if($this->userData === null)
        {
            return parent::getDisplayName();
        }
        return $this->userData->displayname;
    }

    public function getGivenName()
    {
        if($this->userData === null)
        {
            return parent::getGivenName();
        }
        return $this->userData->givenname;
    }

    public function getEmail()
    {
        if($this->userData === null)
        {
            return parent::getEmail();
        }
        return $this->userData->mail;
    }

    public function getUid()
    {
        if($this->userData === null)
        {
            return parent::getUid();
        }
        return $this->userData->uid;
    }

    public function getPhoneNumber()
    {
        if($this->userData === null)
        {
            return parent::getPhoneNumber();
        }
        return $this->userData->mobile;
    }

    public function getOrganization()
    {
        if($this->userData === null)
        {
            return parent::getOrganization();
        }
        return $this->userData->o;
    }

    public function getTitles()
    {
        if($this->userData === null)
        {
            return parent::getTitles();
        }
        return $this->userData->title;
    }

    public function getState()
    {
        if($this->userData === null)
        {
            return parent::getState();
        }
        return $this->userData->st;
    }

    public function getCity()
    {
        if($this->userData === null)
        {
            return parent::getCity();
        }
        return $this->userData->l;
    }

    public function getLastName()
    {
        if($this->userData === null)
        {
            return parent::getLastName();
        }
        return $this->userData->sn;
    }

    public function getNickName()
    {
        if($this->userData === null)
        {
            return parent::getNickName();
        }
        return $this->userData->displayname;
    }

    public function getAddress()
    {
        if($this->userData === null)
        {
            return parent::getAddress();
        }
        return $this->userData->postaladdress;
    }

    public function getPostalCode()
    {
        if($this->userData === null)
        {
            return parent::getPostalCode();
        }
        return $this->userData->postalcode;
    }

    public function getCountry()
    {
        if($this->userData === null)
        {
            return parent::getCountry();
        }
        return $this->userData->c;
    }

    public function getOrganizationUnits()
    {
        if($this->userData === null)
        {
            return parent::getOrganizationUnits();
        }
        return $this->userData->ou;
    }

    public function getLoginProviders()
    {
        if($this->userData === null)
        {
            return parent::getLoginProviders();
        }
        return $this->userData->host;
    }
}

