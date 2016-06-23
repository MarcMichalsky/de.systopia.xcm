<?php
/*-------------------------------------------------------+
| Extended Contact Matcher XCM                           |
| Copyright (C) 2016 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

require_once 'CRM/Core/Form.php';


define('XCM_MAX_RULE_COUNT', 3);

/**
 * XCM Settings form controller
 */
class CRM_Xcm_Form_Settings extends CRM_Core_Form {

  /**
   * build form
   */
  public function buildQuickForm() {

    // add the rule selectors
    for ($i=1; $i <= XCM_MAX_RULE_COUNT; $i++) {
      $this->addElement('select', 
                        "rule_$i",
                        ts('Matching Rule #%1', array(1 => $i, 'domain' => 'de.systopia.xcm')),
                        $this->getRuleOptions($i),
                        array('class' => 'crm-select2'));
    }


    // add stuff for matched/created postprocessing
    foreach (array('matched', 'created') as $mode) {
      $this->addElement('select', 
                        "{$mode}_add_group",
                        ts('Add to group', array('domain' => 'de.systopia.xcm')),
                        $this->getGroups(),
                        array('class' => 'crm-select2'));
      
      $this->addElement('select', 
                        "{$mode}_add_tag",
                        ts('Add to tag', array('domain' => 'de.systopia.xcm')),
                        $this->getTags(),
                        array('class' => 'crm-select2'));

      $this->addElement('select', 
                        "{$mode}_add_activity",
                        ts('Add activity', array('domain' => 'de.systopia.xcm')),
                        $this->getActivities(),
                        array('class' => 'crm-select2'));
    }

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Save', array('domain' => 'de.systopia.xcm')),
        'isDefault' => TRUE,
      ),
    ));
    
    // pass params to smarty
    $this->assign('rule_idxs', range(1, XCM_MAX_RULE_COUNT));

    parent::buildQuickForm();
  }

  /**
   * Getter for $_defaultValues.
   *
   * @return array
   */
  public function setDefaultValues() {
    $rules          = CRM_Core_BAO_Setting::getItem('de.systopia.xcm', 'rules');
    $postprocessing = CRM_Core_BAO_Setting::getItem('de.systopia.xcm', 'postprocessing');

    return $rules + $postprocessing;
  }




  public function postProcess() {
    $values = $this->exportValues();
      
    // store the rules
    $rules = array();
    for ($i=1; isset($values["rule_$i"]); $i++) { 
      $rules["rule_$i"] = $values["rule_$i"];
    }
    CRM_Core_BAO_Setting::setItem($rules, 'de.systopia.xcm', 'rules');

    // store the postprocessing
    $postprocessing = array();
    foreach (array('matched', 'created') as $mode) {
      foreach (array('group', 'tag', 'activity') as $type) {
        $key = "{$mode}_add_{$type}";
        $postprocessing[$key] = CRM_Utils_Array::value($key, $values);
      }
    }
    CRM_Core_BAO_Setting::setItem($postprocessing, 'de.systopia.xcm', 'postprocessing');

    parent::postProcess();
  }







  protected function getRuleOptions($i) {
    // TODO:
    return array(
      0 => ts('None, thank you', array('domain' => 'de.systopia.xcm')),
    );    
  }

  protected function getActivities() {
    $activity_list = array(0 => ts('None, thank you', array('domain' => 'de.systopia.xcm')));

    $activities = civicrm_api3('OptionValue', 'get', array('is_active' => 1, 'option_group_id' => 'activity_type', 'option.limit' => 9999));
    foreach ($activities['values'] as $activity) {
      $activity_list[$activity['value']] = $activity['label'];
    }

    return $activity_list;
  }

  protected function getTags() {
    $tag_list = array(0 => ts('None, thank you', array('domain' => 'de.systopia.xcm')));

    $tags = civicrm_api3('Tag', 'get', array('is_active' => 1, 'option.limit' => 9999));
    foreach ($tags['values'] as $tag) {
      $tag_list[$tag['id']] = $tag['name'];
    }
    return $tag_list;
  }

  protected function getGroups() {
    $group_list = array(0 => ts('None, thank you', array('domain' => 'de.systopia.xcm')));

    $groups = civicrm_api3('Group', 'get', array('is_active' => 1, 'option.limit' => 9999));
    foreach ($groups['values'] as $group) {
      $group_list[$group['id']] = $group['title'];
    }

    return $group_list;
  }

}