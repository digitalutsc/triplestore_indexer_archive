<?php

namespace Drupal\Tests\triplestore_indexer\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Simple test to ensure that main page loads with module enabled.
 *
 * @group triplestore_indexer
 */
class ConfigTest extends BrowserTestBase {
  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['jsonld', 'advancedqueue', 'rest', 'restui', 'triplestore_indexer'];

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  protected function setUp() {
    parent::setUp();
    $this->user = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($this->user);
  }

  public function testConfigForm() {
      // Login
      $this->drupalLogin($this->user);

      // Access config page
      $this->drupalGet('admin/config/triplestore_indexer/configuration');
      $this->assertResponse(200);




      // Test the form elements exist and have defaults
      $config = $this->config('triplestore_indexer.triplestoreindexerconfig');
      $this->assertFieldByName(
          'server-url',
          $config->get('server-url'),
          'Page title field has the default value'
      );
      $this->assertFieldByName(
          'namespace',
          $config->get('namespace'),
          'Source text field has the default value'
      );

      $this->assertFieldByName(
          'select-auth-method',
          $config->get('method-of-auth'),
          'Source text field has the default value'
      );

      if ($config->get("method-of-auth") === "digest") {
          $this->assertFieldByName(
              'client-id',
              $config->get('client-id'),
              'Source text field has the default value'
          );

          $this->assertFieldByName(
              'client-secret',
              $config->get('client-secret'),
              'Source text field has the default value'
          );
      }
      else {
          $this->assertFieldByName(
              'admin-username',
              $config->get('admin-password'),
              'Source text field has the default value'
          );

          $this->assertFieldByName(
              'admin-password',
              $config->get('admin-password'),
              'Source text field has the default value'
          );
      }

      $this->assertFieldByName(
          'advancedqueue-id',
          $config->get('advancedqueue-id'),
          'Source text field has the default value'
      );

      $this->assertFieldByName(
          'number-of-retries',
          $config->get('aqj-max-retries'),
          'Source text field has the default value'
      );

      $this->assertFieldByName(
          'retries-delay',
          $config->get('aqj-retry_delay'),
          'Source text field has the default value'
      );






      // Test form submission
      $this->drupalPostForm(NULL, array(
          'page_title' => 'Test lorem ipsum',
          'source_text' => 'Test phrase 1 \nTest phrase 2 \nTest phrase 3 \n',
      ), t('Save configuration'));
      $this->assertText(
          'The configuration options have been saved.',
          'The form was saved correctly.'
      );
      // Test the new values are there.
      $this->drupalGet('admin/config/development/loremipsum');
      $this->assertResponse(200);
      $this->assertFieldByName(
          'page_title',
          'Test lorem ipsum',
          'Page title is OK.'
      );
      $this->assertFieldByName(
          'source_text',
          'Test phrase 1 \nTest phrase 2 \nTest phrase 3 \n',
          'Source text is OK.'
      );


  }

}
