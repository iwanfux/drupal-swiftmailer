<?php

/**
 * @file
 * Contains \Drupal\swiftmailer\Form\MessagesForm.
 */

namespace Drupal\swiftmailer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class MessagesForm extends ConfigFormBase {


  public function getFormId() {
    return 'swiftmailer_messages_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'swiftmailer.message',
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('swiftmailer.message');

    $form['#tree'] = TRUE;

    $form['description'] = array(
      '#markup' => '<p>' . t('This page allows you to configure settings which determines how e-mail messages are created.') . '</p>',
    );

    if (swiftmailer_validate_library($config->get('path', SWIFTMAILER_VARIABLE_PATH_DEFAULT))) {

      $form['format'] = array(
        '#type' => 'fieldset',
        '#title' => t('Message format'),
        '#description' => t('You can set the default message format which should be applied to e-mail
          messages.'),
      );

      $form['format']['type'] = array(
        '#type' => 'radios',
        '#options' => array(SWIFTMAILER_FORMAT_PLAIN => t('Plain Text'), SWIFTMAILER_FORMAT_HTML => t('HTML')),
        '#default_value' => $config->get('format', SWIFTMAILER_VARIABLE_FORMAT_DEFAULT),
      );

      $form['format']['respect'] = array(
        '#type' => 'checkbox',
        '#title' => t('Respect provided e-mail format.'),
        '#default_value' => $config->get('respect_format', SWIFTMAILER_VARIABLE_RESPECT_FORMAT_DEFAULT),
        '#description' => t('The header "Content-Type", if available, will be respected if you enable this setting.
          Settings such as e-mail format ("text/plain" or "text/html") and character set may be provided through this
          header. Unless your site somehow alters e-mails, enabling this setting will result in all e-mails to be sent
          as plain text as this is the content type Drupal by default will apply to all e-mails.'),
      );

      $form['convert'] = array(
        '#type' => 'fieldset',
        '#title' => t('Plain Text Version'),
        '#description' => t('An alternative plain text version can be generated based on the HTML version if no plain text version
          has been explicitly set. The plain text version will be used by e-mail clients not capable of displaying HTML content.'),
        '#states' => array(
          'visible' => array(
            'input[type=radio][name=format[type]]' => array('value' => SWIFTMAILER_FORMAT_HTML),
          ),
        ),
      );

      $form['convert']['mode'] = array(
        '#type' => 'checkbox',
        '#title' => t('Generate alternative plain text version.'),
        '#default_value' => $config->get('convert_mode', SWIFTMAILER_VARIABLE_CONVERT_MODE_DEFAULT),
        '#description' => t('Please refer to !link for more details about how the alternative plain text version will be generated.', array('!link' => _l('html2text', 'http://www.chuggnutt.com/html2text'))),
      );

      $form['character_set'] = array(
        '#type' => 'fieldset',
        '#title' => t('Character Set'),
        '#description' => '<p>' . t('E-mails need to carry details about the character set which the
          receiving client should use to understand the content of the e-mail.
          The default character set is UTF-8.') . '</p>',
      );

      $form['character_set']['type'] = array(
        '#type' => 'select',
        '#options' => swiftmailer_get_character_set_options(),
        '#default_value' => $config->get('character_set', SWIFTMAILER_VARIABLE_CHARACTER_SET_DEFAULT),
      );
    }
    else {

      $form['message'] = array(
        '#markup' => '<p>' . t('You need to configure the location of the Swift Mailer library. Please visit the !page
          and configure the library to enable the configuration options on this page.',
          array('!page' => _l(t('library configuration page'), 'admin/config/people/swiftmailer'))) . '</p>',
      );

    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('swiftmailer.message');
    $config->set('format', $form_state->getValue(['format', 'type']));
    $config->set('respect_format', $form_state->getValue(['format', 'respect']));
    $config->set('convert_mode', $form_state->getValue(['convert', 'mode']));
    $config->set('character_set', $form_state->getValue(['character_set', 'type']));

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
