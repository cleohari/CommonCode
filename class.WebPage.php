<?php
/**
 * WebPage class
 *
 * This file describes an abstraction for creating a webpage
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

/**
 * We use the Browscap abstraction to determine browser versions 
 */
require('libs/browscap-php/src/phpbrowscap/Browscap.php');
use phpbrowscap\Browscap;

/**
 * A generic abstraction layer for creating a webpage.
 *
 * This class abstracts out some basic webpage creation
 */
class WebPage
{
    /** The webpage title */
    public $title;
    /** An array of tags to be added to the HTML head section */
    public $head_tags;
    /** A string represnting the body of the page */
    public $body;
    /** The browsecap object */
    private $bc;
    /** Data about the browser used to load the page */
    public $browser;
    /** A string to add to the body open tag */
    public $body_tags;
    /** Does the browser support import of CSS or HTML? */
    public $import_support;

    /**
     * Create a new WebPage
     *
     * Create a new webpage abstraction object
     *
     * @param string $title The webpage title
     */
    function __construct($title)
    {
        $this->title = $title;
        $this->head_tags = array();
        $this->body = '';
        $this->bc = $this->getBrowscap();
        $this->bc->doAutoUpdate = false;
        $this->bc->lowercase = true;
        $this->browser = $this->getBrowser();
        $this->import_support = false;
        
        $browserName = $this->getBrowserName();
        if($browserName === 'IE' && $this->getBrowserMajorVer() <= 7)
        {
            header( 'Location: /badbrowser.php' ) ;
        }
        else if($browserName === 'Chrome' && $this->getBrowserMajorVer() >= 36)
        {
            $this->import_support = true;
        }
    }

    /**
     * Get the Browscap instance for the system
     *
     * @return Browscap The Browscap instance for the system
     *
     * @SuppressWarnings("Superglobals")
     */
    protected function getBrowscap()
    {
        if(isset($GLOBALS['BROWSCAP_CACHE']))
        {
            return new Browscap($GLOBALS['BROWSCAP_CACHE']);
        }
        return new Browscap('/var/php_cache/browser');
    }

    /**
     * Use the Browsecap library to determine what browser is being used to load this page
     */
    private function getBrowser()
    {
        static $browser;//No accident can arise from depending on an unset variable.
        if(!isset($browser))
        {
            $browser = $this->bc->getBrowser();
        }
        return $browser;
    }

    /**
     * Get the name of the browser used to load this page
     */
    private function getBrowserName()
    {
        if(isset($this->browser->Browser))
        {
            return $this->browser->Browser;
        }
        return $this->browser->browser;
    }

    /**
     * Get the first part of the browser version number
     *
     * Determine what version of the browser is being used to load the page. This
     * is used to determine if the version of IE is too old to be used
     */
    private function getBrowserMajorVer()
    {
        if(isset($this->browser->MajorVer))
        {
            return $this->browser->MajorVer;
        }
        return $this->browser->majorver;
    }

    /**
     * Print the HTML doctype header
     */
    protected function printDoctype()
    {
        echo '<!DOCTYPE html>';
        echo "\n";
    }

    /**
     * Print the opening HTML tag
     */
    protected function printOpenHtml()
    {
        echo '<HTML>';
    }

    /**
     * Print the closing HTML tag
     */
    protected function printCloseHtml()
    {
        echo '</HTML>';
    }

    /**
     * Print the page
     *
     * @deprecated 1.0.0 This funciton is deprectated and will be remoted. Please use printPage() instead
     */
    function print_page()
    {
        $this->printPage();
    }

    /**
     * Print the webpage to standard out
     */
    public function printPage()
    {
        $this->printDoctype();
        $this->printOpenHtml();
        $this->printHead('    ');
        $this->printBody('    ');
        $this->printCloseHtml();
    }

    /**
     * Add a tag to the head element
     *
     * @param string $tag The tag to add to the page header
     */
    protected function addHeadTag($tag)
    {
        array_push($this->head_tags, $tag);
    }

    /**
     * Create a tag to be added to the document
     *
     * @param string $tagName The tag's name (i.e. the string right after the open sign
     * @param array $attribs Attributes to be added to the tag in the form key=value
     * @param boolean $selfClose Does this tag end with a close (/>)?
     *
     * @return string The tag as a string
     */
    protected function createOpenTag($tagName, $attribs=array(), $selfClose=false)
    {
        $tag = '<'.$tagName;
        $attribNames = array_keys($attribs);
        foreach($attribNames as $attribName)
        {
            $tag.=' '.$attribName;
            if($attribs[$attribName])
            {
                $tag.='="'.$attribs[$attribName].'"';
            }
        }
        if($selfClose)
        {
            return $tag.'/>';
        }
        return $tag.'>';
    }
   
    /**
     * Create a close tag to be added to the document
     *
     * @param string $tagName The tag's name (i.e. the string right after the open sign
     *
     * @return string The close tag as a string
     */
    protected function createCloseTag($tagName)
    {
        return '</'.$tagName.'>';
    }

    /**
     * Create a link to be added to the document
     *
     * @param string $linkName The text inside the link
     * @param string $linkTarget The location the link goes to
     *
     * @return string The link
     */
    public function createLink($linkName, $linkTarget='#')
    {
        $startTag = $this->createOpenTag('a', array('href'=>$linkTarget));
        $endTag = $this->createCloseTag('a');
        return $startTag.$linkName.$endTag;
    }

    /**
     * Add tags to the header to make the IE family of browsers behave better
     *
     * The IE family of browsers lower than version 9 do not support HTML 5 so we need
     * to add a polyfill for those feaures. Additionally, IE versions greater than 8
     * have a compatibility mode. We need to tell them to act as the latest greatest version
     *
     * @param string $prefix The prefix to append to each line
     */
    protected function printIeCompatability($prefix='')
    {
       //IE 8 doesn't support HTML 5. Install the shim...
       if($this->getBrowserMajorVer() < 9)
       {
           echo $prefix.'<script src="js/html5.js"></script>';
           echo "\n";
       }
       //Tell the browser not to use compatability mode...
       echo $prefix.'<meta http-equiv="X-UA-Compatible" content="IE=edge"/>';
       echo "\n";
    }

    /**
     * Print the HTML HEAD section
     *
     * @param string $prefix The prefix to append to each line
     */
    protected function printHead($prefix='')
    {
        echo $prefix.'<HEAD>';
        if($this->getBrowserName() === 'IE')
        {
            $this->printIeCompatability($prefix.$prefix);
        }
        echo $prefix.$prefix.'<TITLE>'.$this->title.'</TITLE>';
        echo $prefix.$prefix.'<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        foreach($this->head_tags as $tag)
        {
            echo $prefix.$prefix.$tag."\n";
        }
        echo $prefix.'</HEAD>';
    }

    /**
     * Print the HTML BODY section
     *
     * @param string $prefix The prefix to append to each line
     */
    protected function printBody($prefix='')
    {
        echo $prefix.'<BODY '.$this->body_tags.'>';
        echo $prefix.$prefix.$this->body."\n";
        echo $prefix.'</BODY>';
    }

    /**
     * Get the currently requested URL
     *
     * @return string The full URL of the requested page
     *
     * @deprecated 1.0.0 This funciton is deprectated and will be remoted. Please use currentURL() instead
     */
    function current_url()
    {
        return $this->currentURL();
    }

    /**
     * Get the currently requested URL
     *
     * @return string The full URL of the requested page
     *
     * @SuppressWarnings("Superglobals")
     */
    public function currentURL()
    {
        if(!isset($_SERVER['REQUEST_URI']))
        {
            return '';
        }
        $requestURI = $_SERVER['REQUEST_URI'];
        if($requestURI[0] === '/')
        {
            $requestURI = substr($requestURI, 1);
        }
        return 'http'.(isset($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['HTTP_HOST'].'/'.$requestURI;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
