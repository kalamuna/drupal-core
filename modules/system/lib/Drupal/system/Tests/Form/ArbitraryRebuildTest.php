<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Form\ArbitraryRebuildTest.
 */

namespace Drupal\system\Tests\Form;

use Drupal\simpletest\WebTestBase;

/**
 * Tests rebuilding of arbitrary forms by altering them.
 */
class ArbitraryRebuildTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('form_test');

  public static function getInfo() {
    return array(
      'name' => 'Rebuild arbitrary forms',
      'description' => 'Tests altering forms to be rebuilt so there are multiple steps.',
      'group' => 'Form API',
    );
  }

  function setUp() {
    parent::setUp();

    // Auto-create a field for testing.
    entity_create('field_entity', array(
      'field_name' => 'test_multiple',
      'type' => 'text',
      'cardinality' => -1,
      'translatable' => FALSE,
    ))->save();
    entity_create('field_instance', array(
      'entity_type' => 'user',
      'field_name' => 'test_multiple',
      'bundle' => 'user',
      'label' => 'Test a multiple valued field',
      'settings' => array(
        'user_register_form' => TRUE,
      ),
    ))->save();
    entity_get_form_display('user', 'user', 'default')
      ->setComponent('test_multiple', array(
        'type' => 'text_textfield',
        'weight' => 0,
      ))
      ->save();
  }

  /**
   * Tests a basic rebuild with the user registration form.
   */
  function testUserRegistrationRebuild() {
    $edit = array(
      'name' => 'foo',
      'mail' => 'bar@example.com',
    );
    $this->drupalPost('user/register', $edit, 'Rebuild');
    $this->assertText('Form rebuilt.');
    $this->assertFieldByName('name', 'foo', 'Entered user name has been kept.');
    $this->assertFieldByName('mail', 'bar@example.com', 'Entered mail address has been kept.');
  }

  /**
   * Tests a rebuild caused by a multiple value field.
   */
  function testUserRegistrationMultipleField() {
    $edit = array(
      'name' => 'foo',
      'mail' => 'bar@example.com',
    );
    $this->drupalPost('user/register', $edit, t('Add another item'), array('query' => array('field' => TRUE)));
    $this->assertText('Test a multiple valued field', 'Form has been rebuilt.');
    $this->assertFieldByName('name', 'foo', 'Entered user name has been kept.');
    $this->assertFieldByName('mail', 'bar@example.com', 'Entered mail address has been kept.');
  }
}
