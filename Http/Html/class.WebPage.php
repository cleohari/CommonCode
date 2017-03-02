<?php
namespace Http\Html;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

class WebPage
{
    /** The webpage title */
    public $title;
    /** An array of tags to be added to the HTML head section */
    protected $headTags;
    /** A string represnting the body of the page */
    public $body;
    /** A string to add to the body open tag */
    public $body_tags;

    protected $request = null;

    public function __construct($title = '')
    {
        $this->title = $title;
        $this->headTags = array();
    }

    public function handleRequest($request, $response, $args)
    {
        $this->request = $request;
        $response = $this->printPage($response);
        return $response;
    }

    /**
     * Add a tag to the head element
     *
     * @param string $tag The tag to add to the page header
     */
    public function addHeadTag($tag)
    {
        array_push($this->headTags, $tag);
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
    protected function createOpenTag($tagName, $attribs = array(), $selfClose = false)
    {
        $tag = '<'.$tagName;
        $attribNames = array_keys($attribs);
        foreach($attribNames as $attribName)
        {
            $tag .= ' '.$attribName;
            if($attribs[$attribName])
            {
                $tag .= '="'.$attribs[$attribName].'"';
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
    public function createLink($linkName, $linkTarget = '#')
    {
        $startTag = $this->createOpenTag('a', array('href'=>$linkTarget));
        $endTag = $this->createCloseTag('a');
        return $startTag.$linkName.$endTag;
    }

    /**
     * Print the HTML doctype header
     */
    protected function printDoctype($response)
    {
        return $response->write('<!DOCTYPE html>');
    }

    /**
     * Print the opening HTML tag
     */
    protected function printOpenHtml($response)
    {
        return $response->write('<HTML lang="en">');
    }
    /**
     * Print the closing HTML tag
     */
    protected function printCloseHtml($response)
    {
        return $response->write('</HTML>');
    }

    /**
     * Print the HTML HEAD section
     */
    protected function printHead($response)
    {
        $head = '<HEAD><TITLE>'.$this->title.'</TITLE>';
        $head.='<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        foreach($this->headTags as $tag)
        {
            $head.=$tag."\n";
        }
        $head.='</HEAD>';
        return $response->write($head);
    }

    /**
     * Print the HTML BODY section
     */
    protected function printBody($response)
    {
        return $response->write('<BODY '.$this->body_tags.'>'.$this->body.'</BODY>');
    }

    protected function printPage($response)
    {
        $response = $this->printDoctype($response);
        $response = $this->printOpenHtml($response);
        $response = $this->printHead($response);
        $response = $this->printBody($response);
        $response = $this->printCloseHtml($response);
        return $response;
    }

    public function getCurrentUrl()
    {
        if($this->request === null)
        {
            return '';
        }
        return $this->request->getUri();
    }
}
