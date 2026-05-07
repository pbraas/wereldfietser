<?php
/**
 *
 * Wereldfietser Text Adjustments. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace wf\textadjust\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Wereldfietser Text Adjustments Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup_after'	=> 'load_modified_language_vars',
      'core.twig_environment_render_template_before' => 'load_modified_boardrules_intro',
		);
	}

	/**
	 * Modifies Core UI text variables by loading the language file
   *   at wf/textadjust/language/nl/common.php
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_modified_language_vars($event)
	{
    global $phpbb_container;
		/** @var \phpbb\language\language $language Language object */
		$language = $phpbb_container->get('language');
    $language->add_lang('common', 'wf/textadjust');
    $language->add_lang('ucp', 'wf/textadjust');
	}

	/**
	 * Modifies Board Rules introduction
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_modified_boardrules_intro($event)
	{
    if ($event['name'] == 'boardrules_controller.html') {
      $context = $event['context'];
      $context['BOARDRULES_EXPLAIN'] = 'Versie 28 september 2018 - Dit forum wordt je aangeboden door vereniging De Wereldfietser. Je treft hier mensen die net als jij een passie hebben voor het reizen per fiets. Naast die passie zijn we allemaal verschillend. Om de discussies tussen al deze verschillende mensen op dit forum in goede banen te leiden, hanteert de vereniging regels. Dit document beschrijft wat we van je verwachten, wat er wel en niet mag, en hoe we ingrijpen als je de regels overtreedt.';
      $event['context'] = $context;
    }
	}

}
