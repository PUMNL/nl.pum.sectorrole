<?php

/**
 * Class to process hooks for Sector Role
 * (originally created for issue http://redmine.pum.nl/issues/3296)
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 26 Apr 2016
 * @license AGPL-3.0
 */
class CRM_Sectorrole_SectorRole {

  /**
   * Method to implement hook civicrm_buildForm for Sector Role
   *
   * @param $formName
   * @param $form
   */
  public static function buildForm($formName, &$form) {
    // set default role based on contact type for add action
    if ($formName == "CRM_Contactsegment_Form_ContactSegment") {
        $contactId = $form->getVar('_contactId');
        try {
          $subType = "Other";
          $contactSubTypes = civicrm_api3('Contact', 'Getvalue', array('id' => $contactId, 'return' => 'contact_sub_type'));
          foreach ($contactSubTypes as $contactSubType) {
            if ($contactSubType == 'Expert' || $contactSubType == 'Country' || $contactSubType == 'Customer') {
              $subType = $contactSubType;
              break;
            }
          }
          self::removeInvalidOptionsFromList($form, $subType);
        } catch (CiviCRM_API3_Exception $ex) {
        }
      $action = $form->getVar('_action');
      if ($action == CRM_Core_Action::ADD) {
        $defaults['contact_segment_role'] = $subType;
        $form->setDefaults($defaults);
      }
    }
  }

  /**
   * Method to remove unwanted list options for role
   *
   * @param $form
   * @param $role
   * @access protected
   * @static
   */
  protected static function removeInvalidOptionsFromList(&$form, $role) {
    $index = $form->_elementIndex['contact_segment_role'];
    switch($role) {
      case "Expert":
        $remove = array("Customer", "Country");
        break;
      case "Country":
        $remove = array("Customer", "Expert", "Sector Coordinator", "Recruitment Team Member");
        break;
      case "Customer":
        $remove = array("Country", "Expert", "Sector Coordinator", "Recruitment Team Member");
        break;
      default:
        $remove = array("Country", "Expert", "Sector Coordinator", "Recruitment Team Member", "Customer");
        break;
    }
    foreach ($form->_elements[$index]->_options as $optionId => $option) {
      if (in_array($option['text'], $remove)) {
        unset($form->_elements[$index]->_options[$optionId]);
      }
    }
  }
}