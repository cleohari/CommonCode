<?php
namespace PDF;

require_once('libs/mpdf/mpdf.php');

class PDF
{
    private $mpdf;
    /** An instance of the Settings class */
    protected $settings;
    /** The sites URLs */
    protected $wwwUrl;
    protected $wikiUrl;
    protected $profilesUrl;
    protected $secureUrl;

    function __construct()
    {
        $this->mpdf = new \mPDF('', 'Letter', '', '', 5, 5);
        $this->settings = \Settings::getInstance();
        $this->wwwUrl = $this->settings->getGlobalSetting('www_url', 'https://www.burningflipside.com/');
        $this->wikiUrl = $this->settings->getGlobalSetting('wiki_url', 'https://wiki.burningflipside.com/');
        $this->profilesUrl = $this->settings->getGlobalSetting('profiles_url', 'https://profiles.burningflipside.com/');
        $this->secureUrl = $this->settings->getGlobalSetting('secure_url', 'https://secure.burningflipside.com/');
    }

    public function setPDFFromHTML($html)
    {
        $this->mpdf->WriteHTML($html);
    }

    public function toPDFBuffer()
    {
        return $this->mpdf->Output('', 'S');
    }

    public function toPDFFile($filename)
    {
        return $this->mpdf->Output($filename);
    }
}
