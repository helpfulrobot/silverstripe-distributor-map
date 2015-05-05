<?php

/**
 * Class DistributorPage
 * @author Patrick Chitovoro
 */
class DistributorPage extends Page
{
    private static $can_be_root = true;
    public static $db = array(
        "AllowAddingDistributors" => "Boolean",
        "AddButtonText" => "Varchar(255)",
    );
    public static $has_one = array();
    public static $has_many = array(
        "Distributors" => "Distributor",
    );

    public function getCMSFields()
    {
        $f = parent::getCMSFields();
        $gridFieldConfig = GridFieldConfig_RecordEditor::create();
        $gridField = new GridField('Distributors', 'Testimonials', $this->Distributors(), $gridFieldConfig);
        $f->addFieldToTab('Root.Distributors', $gridField);
        $f->addFieldToTab("Root.Distributors", CheckboxField::create('AllowAddingDistributors'));
        $f->addFieldToTab("Root.Distributors", TextField::create('AddButtonText'));
        return $f;
    }
}

class DistributorPage_Controller extends Page_Controller
{
    private static $allowed_actions = array(
        "add",
        "DistributorForm"
    );

    public function init()
    {
        parent:: init();
        Requirements::css(DISTRIBUTOR_MAP_DIR . '/css/distributor-map.css');
        Requirements::javascript(DISTRIBUTOR_MAP_DIR . "/js/Base64Handler.js");

        $aVars = array(
            'Address' => $this->Address,
            'Project' => PROJECT,
            'Module' => DISTRIBUTOR_MAP_DIR,
            'Distributors' => $this->DistributorList()
        );
        Requirements::javascriptTemplate(DISTRIBUTOR_MAP_DIR . '/js/DistributorGoogleMapCode.js', $aVars);

    }


    function DistributorList()
    {
        $aPlaces = array();
        $Distributors = $this->Distributors()->filter(array("Status" => "Active"));
        if (count($Distributors)) {
            foreach ($Distributors as $record) {
                $aPlaces [] = sprintf(" ['%s', '%s', %s, %s, '%s']",
                    Convert::raw2sql($record->Name),
                    Convert::raw2sql($record->Town),
                    Convert::raw2sql($record->Latitude),
                    Convert::raw2sql($record->Longitude),
                    base64_encode($this->getInfoWindow($record))
                );
            }
        }
        return implode(',', $aPlaces);
    }

    /**
     * @param Distributor $record
     * @return mixed
     */
    function getInfoWindow(Distributor $record)
    {
        $html = $this->customise($record->Details())->renderWith(array("DistributorInfoWindow"));
        return $html->Value;


    }


    function add()
    {
        $data = array(
            "SlideShow" => false,
            "Form" => $this->DistributorForm()
        );
        return $this->customise($data)->renderWith(array("DistributorPage_add", "Page"));
    }

    function DistributorForm()
    {
        return DistributorForm::create($this, __FUNCTION__);
    }

}
