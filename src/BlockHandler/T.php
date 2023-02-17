<?php

namespace Smarty\BlockHandler;

use Smarty\Smarty;
use Smarty\Template;

/**
 * smarty-gettext.php - Gettext support for smarty
 *
 * To register as a smarty block function named 't', use:
 *   $smarty->register_block('t', 'smarty_translate');
 *
 * NOTE: native php support for conext sensitive does not exist
 * Those jumps are disabled
 *
 * @package	smarty-gettext
 * @version	$Id: block.t.php 4738 2022-05-06 01:28:48Z clemens $
 * @link	http://smarty-gettext.sf.net/
 * @author	Sagi Bashari <sagi@boom.org.il>
 * @copyright 2004 Sagi Bashari
 * @copyright Elan Ruusamäe
 * @copyright Clemens Schwaighofer
 */

class T implements BlockHandlerInterface
{
	/**
	 * Replaces arguments in a string with their values.
	 * Arguments are represented by % followed by their number.
	 *
	 * @param string $str Source string
	 * @param mixed mixed Arguments, can be passed in an array or through single variables.
	 * @return string Modified string
	 */
	private function smartyGettextStrArg($str/*, $varargs... */)
	{
		$tr = [];
		$p = 0;

		$nargs = func_num_args();
		for ($i = 1; $i < $nargs; $i++) {
			$arg = func_get_arg($i);

			if (is_array($arg)) {
				foreach ($arg as $aarg) {
					$tr['%' . ++$p] = $aarg;
				}
			} else {
				$tr['%' . ++$p] = $arg;
			}
		}

		return strtr($str, $tr);
	}

	/**
	 * Smarty block function, provides gettext support for smarty.
	 *
	 * The block content is the text that should be translated.
	 *
	 * Any parameter that is sent to the function will be represented as %n in the translation text,
	 * where n is 1 for the first parameter. The following parameters are reserved:
	 *   - escape - sets escape mode:
	 *       - 'html' for HTML escaping, this is the default.
	 *       - 'js' for javascript escaping.
	 *       - 'url' for url escaping.
	 *       - 'no'/'off'/0 - turns off escaping
	 *   - plural - The plural version of the text (2nd parameter of ngettext())
	 *   - count - The item count for plural mode (3rd parameter of ngettext())
	 *   - domain - Textdomain to be used, default if skipped (dgettext() instead of gettext())
	 *   - context - gettext context. reserved for future use.
	 *
	 */

	// cs modified: __ calls instead of direct gettext calls

	public function handle($params, $text, Template $template, &$repeat)
	{
		if (!isset($text)) {
			return $text;
		}
		$assign = null;

		// set escape mode, default html escape
		if (isset($params['escape'])) {
			$escape = $params['escape'];
			unset($params['escape']);
		} else {
			$escape = 'html';
		}

		// set plural parameters 'plural' and 'count'.
		if (isset($params['plural'])) {
			$plural = $params['plural'];
			unset($params['plural']);

			// set count
			if (isset($params['count'])) {
				$count = $params['count'];
				unset($params['count']);
			}
		}

		// get domain param
		if (isset($params['domain'])) {
			$domain = $params['domain'];
			unset($params['domain']);
		} else {
			$domain = null;
		}

		// get context param
		if (isset($params['context'])) {
			$context = $params['context'];
			unset($params['context']);
		} else {
			$context = null;
		}

		// use plural if required parameters are set
		if (isset($count) && isset($plural)) {
			if (isset($domain) && isset($context)) {
				if (is_callable('_dnpgettext')) {
					$text = _dnpgettext($domain, $context, $text, $plural, $count);
				}/*  elseif (is_callable('dnpgettext')) {
					$text = dnpgettext($domain, $context, $text, $plural, $count);
				} */
			} elseif (isset($domain)) {
				if (is_callable('_dngettext')) {
					$text = _dngettext($domain, $text, $plural, $count);
				} elseif (is_callable('dngettext')) {
					$text = dngettext($domain, $text, $plural, $count);
				}
			} elseif (isset($context)) {
				if (is_callable('_npgettext')) {
					$text = _npgettext($context, $text, $plural, $count);
				}/*  elseif (is_callable('npgettext')) {
					$text = npgettext($context, $text, $plural, $count);
				} */
			} else {
				if (is_callable('_ngettext')) {
					$text = _ngettext($text, $plural, $count);
				} elseif (is_callable('ngettext')) {
					$text = ngettext($text, $plural, $count);
				}
			}
		} else { // use normal
			if (isset($domain) && isset($context)) {
				if (is_callable('_dpgettext')) {
					$text = _dpgettext($domain, $context, $text);
				}/*  elseif (is_callable('dpgettext')) {
					$text = dpgettext($domain, $context, $text);
				} */
			} elseif (isset($domain)) {
				if (is_callable('_dgettext')) {
					$text = _dgettext($domain, $text);
				} elseif (is_callable('dpgettext')) {
					$text = dgettext($domain, $text);
				}
			} elseif (isset($context)) {
				if (is_callable('_pgettext')) {
					$text = _pgettext($context, $text);
				}/*  elseif (is_callable('pgettext')) {
					$text = pgettext($context, $text);
				} */
			} else {
				if (is_callable('_gettext')) {
					$text = _gettext($text);
				} elseif (is_callable('gettext')) {
					$text = gettext($text);
				}
			}
		}

		// run strarg if there are parameters
		if (count($params)) {
			$text = $this->smartyGettextStrArg($text, $params);
		}

		switch ($escape) {
			case 'html':
				// default
				$text = nl2br(htmlspecialchars($text));
				break;
			case 'javascript':
			case 'js':
				// javascript escape
				$text = strtr(
					$text,
					[
						'\\' => '\\\\',
						"'" => "\\'",
						'"' => '\\"',
						"\r" => '\\r',
						"\n" => '\\n',
						'</' => '<\/'
					]
				);
				break;
			case 'url':
				// url escape
				$text = urlencode($text);
				break;
			// below is a list for explicit OFF
			case 'no':
			case 'off':
			case 'false':
			case '0':
			case 0:
				// explicit OFF
			default:
				break;
		}

		if ($assign) {
			$template->assign($assign, $text);
		} else {
			return $text;
		}
	}

	public function isCacheable(): bool
	{
		return true;
	}
}

// __END__
